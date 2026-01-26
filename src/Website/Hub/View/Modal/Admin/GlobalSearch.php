<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Admin\Search\SearchProviderRegistry;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Global search component with Command+K keyboard shortcut.
 *
 * Searches across all registered SearchProvider implementations.
 * Accessible from any page via keyboard shortcut or search button.
 *
 * Features:
 * - Command+K / Ctrl+K to open
 * - Arrow key navigation
 * - Enter to select
 * - Escape to close
 * - Recent searches (stored in session)
 * - Debounced search input
 * - Grouped results by provider type
 */
class GlobalSearch extends Component
{
    /**
     * Whether the search modal is open.
     */
    public bool $open = false;

    /**
     * The current search query.
     */
    public string $query = '';

    /**
     * Currently selected result index for keyboard navigation.
     */
    public int $selectedIndex = 0;

    /**
     * Recent searches stored in session.
     */
    public array $recentSearches = [];

    /**
     * Maximum number of recent searches to store.
     */
    protected int $maxRecentSearches = 5;

    /**
     * The search provider registry.
     */
    protected SearchProviderRegistry $registry;

    /**
     * Boot the component with dependencies.
     */
    public function boot(SearchProviderRegistry $registry): void
    {
        $this->registry = $registry;
    }

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->recentSearches = session('global_search.recent', []);
    }

    /**
     * Open the search modal.
     */
    #[On('open-global-search')]
    public function openSearch(): void
    {
        $this->open = true;
        $this->query = '';
        $this->selectedIndex = 0;
    }

    /**
     * Close the search modal.
     */
    public function closeSearch(): void
    {
        $this->open = false;
        $this->query = '';
        $this->selectedIndex = 0;
    }

    /**
     * Handle query changes - reset selection index.
     */
    public function updatedQuery(): void
    {
        $this->selectedIndex = 0;
    }

    /**
     * Navigate up in results.
     */
    public function navigateUp(): void
    {
        if ($this->selectedIndex > 0) {
            $this->selectedIndex--;
        }
    }

    /**
     * Navigate down in results.
     */
    public function navigateDown(): void
    {
        $allResults = $this->flatResults;
        if ($this->selectedIndex < count($allResults) - 1) {
            $this->selectedIndex++;
        }
    }

    /**
     * Select the current result.
     */
    public function selectCurrent(): void
    {
        $allResults = $this->flatResults;
        if (isset($allResults[$this->selectedIndex])) {
            $result = $allResults[$this->selectedIndex];
            $this->navigateTo($result);
        }
    }

    /**
     * Navigate to a specific result.
     */
    public function navigateTo(array $result): void
    {
        // Add to recent searches
        $this->addToRecentSearches($result);

        $this->closeSearch();

        $this->dispatch('navigate-to-url', url: $result['url']);
    }

    /**
     * Navigate to a recent search item.
     */
    public function navigateToRecent(int $index): void
    {
        if (isset($this->recentSearches[$index])) {
            $result = $this->recentSearches[$index];
            $this->closeSearch();
            $this->dispatch('navigate-to-url', url: $result['url']);
        }
    }

    /**
     * Clear all recent searches.
     */
    public function clearRecentSearches(): void
    {
        $this->recentSearches = [];
        session()->forget('global_search.recent');
    }

    /**
     * Remove a single recent search.
     */
    public function removeRecentSearch(int $index): void
    {
        if (isset($this->recentSearches[$index])) {
            array_splice($this->recentSearches, $index, 1);
            session(['global_search.recent' => $this->recentSearches]);
        }
    }

    /**
     * Add a result to recent searches.
     */
    protected function addToRecentSearches(array $result): void
    {
        // Remove if already exists (to move to top)
        $this->recentSearches = array_values(array_filter(
            $this->recentSearches,
            fn ($item) => $item['id'] !== $result['id'] || $item['type'] !== $result['type']
        ));

        // Add to the beginning
        array_unshift($this->recentSearches, [
            'id' => $result['id'],
            'title' => $result['title'],
            'subtitle' => $result['subtitle'] ?? null,
            'url' => $result['url'],
            'type' => $result['type'],
            'icon' => $result['icon'],
        ]);

        // Limit the number of recent searches
        $this->recentSearches = array_slice($this->recentSearches, 0, $this->maxRecentSearches);

        // Save to session
        session(['global_search.recent' => $this->recentSearches]);
    }

    /**
     * Get search results grouped by type.
     */
    #[Computed]
    public function results(): array
    {
        if (strlen($this->query) < 2) {
            return [];
        }

        $user = auth()->user();
        $workspace = $user?->defaultHostWorkspace();

        return $this->registry->search($this->query, $user, $workspace);
    }

    /**
     * Get flattened results for keyboard navigation.
     */
    #[Computed]
    public function flatResults(): array
    {
        return $this->registry->flattenResults($this->results);
    }

    /**
     * Check if there are any results.
     */
    #[Computed]
    public function hasResults(): bool
    {
        return ! empty($this->flatResults);
    }

    /**
     * Check if we should show recent searches.
     */
    #[Computed]
    public function showRecentSearches(): bool
    {
        return strlen($this->query) < 2 && ! empty($this->recentSearches);
    }

    public function render()
    {
        return view('hub::admin.global-search');
    }
}
