<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search;

use Core\Admin\Search\Contracts\SearchProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Registry for search providers.
 *
 * Manages registration and discovery of SearchProvider implementations.
 * Coordinates searching across all registered providers and aggregates
 * results into a unified structure for the GlobalSearch component.
 *
 * ## Fuzzy Matching
 *
 * The registry provides built-in fuzzy matching support via the `fuzzyMatch()`
 * method. Providers can use this for consistent search behavior:
 *
 * ```php
 * public function search(string $query, int $limit = 5): Collection
 * {
 *     $results = $this->getAllItems();
 *     return $results->filter(function ($item) use ($query) {
 *         return app(SearchProviderRegistry::class)
 *             ->fuzzyMatch($query, $item->title);
 *     })->take($limit);
 * }
 * ```
 */
class SearchProviderRegistry
{
    /**
     * Registered search providers.
     *
     * @var array<SearchProvider>
     */
    protected array $providers = [];

    /**
     * Register a search provider.
     */
    public function register(SearchProvider $provider): void
    {
        $this->providers[] = $provider;
    }

    /**
     * Register multiple search providers.
     *
     * @param  array<SearchProvider>  $providers
     */
    public function registerMany(array $providers): void
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    /**
     * Get all registered providers.
     *
     * @return array<SearchProvider>
     */
    public function providers(): array
    {
        return $this->providers;
    }

    /**
     * Get available providers for a given context.
     *
     * @param  object|null  $user  The authenticated user
     * @param  object|null  $workspace  The current workspace context
     * @return Collection<int, SearchProvider>
     */
    public function availableProviders(?object $user, ?object $workspace): Collection
    {
        return collect($this->providers)
            ->filter(fn (SearchProvider $provider) => $provider->isAvailable($user, $workspace))
            ->sortBy(fn (SearchProvider $provider) => $provider->searchPriority());
    }

    /**
     * Search across all available providers.
     *
     * Returns results grouped by search type, sorted by provider priority.
     *
     * @param  string  $query  The search query
     * @param  object|null  $user  The authenticated user
     * @param  object|null  $workspace  The current workspace context
     * @param  int  $limitPerProvider  Maximum results per provider
     * @return array<string, array{label: string, icon: string, results: array}>
     */
    public function search(
        string $query,
        ?object $user,
        ?object $workspace,
        int $limitPerProvider = 5
    ): array {
        $grouped = [];

        foreach ($this->availableProviders($user, $workspace) as $provider) {
            $type = $provider->searchType();
            $results = $provider->search($query, $limitPerProvider);

            // Convert results to array format with type/icon
            $formattedResults = $results->map(function ($result) use ($provider) {
                if ($result instanceof SearchResult) {
                    return $result->withTypeAndIcon(
                        $provider->searchType(),
                        $provider->searchIcon()
                    )->toArray();
                }

                // Handle array results
                if (is_array($result)) {
                    $searchResult = SearchResult::fromArray($result);

                    return $searchResult->withTypeAndIcon(
                        $provider->searchType(),
                        $provider->searchIcon()
                    )->toArray();
                }

                // Handle model objects with getUrl
                return [
                    'id' => (string) ($result->id ?? uniqid()),
                    'title' => (string) ($result->title ?? $result->name ?? ''),
                    'subtitle' => (string) ($result->subtitle ?? $result->description ?? ''),
                    'url' => $provider->getUrl($result),
                    'type' => $provider->searchType(),
                    'icon' => $provider->searchIcon(),
                    'meta' => [],
                ];
            })->toArray();

            if (! empty($formattedResults)) {
                $grouped[$type] = [
                    'label' => $provider->searchLabel(),
                    'icon' => $provider->searchIcon(),
                    'results' => $formattedResults,
                ];
            }
        }

        return $grouped;
    }

    /**
     * Flatten search results into a single array for keyboard navigation.
     *
     * @param  array  $grouped  Grouped search results
     */
    public function flattenResults(array $grouped): array
    {
        $flat = [];

        foreach ($grouped as $type => $group) {
            foreach ($group['results'] as $result) {
                $flat[] = $result;
            }
        }

        return $flat;
    }

    /**
     * Check if a query fuzzy-matches a target string.
     *
     * Supports:
     * - Case-insensitive partial matching
     * - Word-start matching (e.g., "ps" matches "Post Settings")
     * - Abbreviation matching (e.g., "gs" matches "Global Search")
     *
     * @param  string  $query  The search query
     * @param  string  $target  The target string to match against
     */
    public function fuzzyMatch(string $query, string $target): bool
    {
        $query = Str::lower(trim($query));
        $target = Str::lower(trim($target));

        // Empty query matches nothing
        if ($query === '') {
            return false;
        }

        // Direct substring match (most common case)
        if (Str::contains($target, $query)) {
            return true;
        }

        // Word-start matching: each character matches start of consecutive words
        // e.g., "ps" matches "Post Settings", "gs" matches "Global Search"
        $words = preg_split('/\s+/', $target);
        $queryChars = str_split($query);
        $wordIndex = 0;
        $charIndex = 0;

        while ($charIndex < count($queryChars) && $wordIndex < count($words)) {
            $char = $queryChars[$charIndex];
            $word = $words[$wordIndex];

            if (Str::startsWith($word, $char)) {
                $charIndex++;
            }
            $wordIndex++;
        }

        if ($charIndex === count($queryChars)) {
            return true;
        }

        // Abbreviation matching: all query chars appear in order
        // e.g., "gsr" matches "Global Search Results"
        $targetIndex = 0;
        foreach ($queryChars as $char) {
            $foundAt = strpos($target, $char, $targetIndex);
            if ($foundAt === false) {
                return false;
            }
            $targetIndex = $foundAt + 1;
        }

        return true;
    }

    /**
     * Calculate a relevance score for sorting results.
     *
     * Higher scores indicate better matches.
     *
     * @param  string  $query  The search query
     * @param  string  $target  The target string
     * @return int Score from 0-100
     */
    public function relevanceScore(string $query, string $target): int
    {
        $query = Str::lower(trim($query));
        $target = Str::lower(trim($target));

        if ($query === '' || $target === '') {
            return 0;
        }

        // Exact match
        if ($target === $query) {
            return 100;
        }

        // Starts with query
        if (Str::startsWith($target, $query)) {
            return 90;
        }

        // Contains query as whole word
        if (preg_match('/\b'.preg_quote($query, '/').'\b/', $target)) {
            return 80;
        }

        // Contains query
        if (Str::contains($target, $query)) {
            return 70;
        }

        // Word-start matching
        $words = preg_split('/\s+/', $target);
        $queryChars = str_split($query);
        $matched = 0;
        $wordIndex = 0;

        foreach ($queryChars as $char) {
            while ($wordIndex < count($words)) {
                if (Str::startsWith($words[$wordIndex], $char)) {
                    $matched++;
                    $wordIndex++;
                    break;
                }
                $wordIndex++;
            }
        }

        if ($matched === count($queryChars)) {
            return 60;
        }

        // Fuzzy match
        if ($this->fuzzyMatch($query, $target)) {
            return 40;
        }

        return 0;
    }
}
