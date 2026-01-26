<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Contracts;

use Illuminate\Support\Collection;

/**
 * Interface for search providers.
 *
 * Modules implement this interface to contribute searchable content to the
 * global search (Command+K). Each provider is responsible for:
 *
 * - Defining a search type (e.g., 'pages', 'users', 'posts')
 * - Providing an icon for visual identification
 * - Executing searches against their data source
 * - Generating URLs for navigation to results
 *
 * ## Search Result Format
 *
 * The `search()` method should return a Collection of SearchResult objects
 * or arrays with the following structure:
 *
 * ```php
 * [
 *     'id' => 'unique-identifier',
 *     'title' => 'Result Title',
 *     'subtitle' => 'Optional description',
 *     'url' => '/path/to/resource',
 *     'icon' => 'optional-override-icon',
 *     'meta' => ['optional' => 'metadata'],
 * ]
 * ```
 *
 * ## Registration
 *
 * Providers are typically registered via `SearchProviderRegistry::register()`
 * during the AdminPanelBooting event or in a service provider's boot method.
 *
 *
 * @see SearchProviderRegistry For provider registration and discovery
 * @see SearchResult For the result data structure
 */
interface SearchProvider
{
    /**
     * Get the search type identifier.
     *
     * This is used for grouping results in the UI and for filtering.
     * Examples: 'pages', 'users', 'posts', 'products', 'settings'.
     */
    public function searchType(): string;

    /**
     * Get the display label for this search type.
     *
     * This is shown as the group header in the search results.
     * Should be a human-readable, translatable string.
     */
    public function searchLabel(): string;

    /**
     * Get the icon name for this search type.
     *
     * Used to display an icon next to search results from this provider.
     * Should be a valid Heroicon or FontAwesome icon name.
     */
    public function searchIcon(): string;

    /**
     * Execute a search query.
     *
     * Searches the provider's data source for matches against the query.
     * Should implement fuzzy matching where appropriate for better UX.
     *
     * @param  string  $query  The search query string
     * @param  int  $limit  Maximum number of results to return (default: 5)
     * @return Collection<int, SearchResult|array> Collection of search results
     */
    public function search(string $query, int $limit = 5): Collection;

    /**
     * Get the URL for a search result.
     *
     * Generates the navigation URL for a given search result.
     * This allows providers to implement custom URL generation logic.
     *
     * @param  mixed  $result  The search result (model or array)
     * @return string The URL to navigate to
     */
    public function getUrl(mixed $result): string;

    /**
     * Get the priority for ordering in search results.
     *
     * Lower numbers appear first. Default should be 50.
     * Use lower numbers (10-40) for important/frequently accessed resources.
     * Use higher numbers (60-100) for less important resources.
     */
    public function searchPriority(): int;

    /**
     * Check if this provider should be active for the current context.
     *
     * Override this to implement permission checks or context-based filtering.
     * For example, only show certain searches to admin users.
     *
     * @param  object|null  $user  The authenticated user
     * @param  object|null  $workspace  The current workspace context
     */
    public function isAvailable(?object $user, ?object $workspace): bool;
}
