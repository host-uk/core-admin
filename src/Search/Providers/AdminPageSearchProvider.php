<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Providers;

use Core\Admin\Search\Concerns\HasSearchProvider;
use Core\Admin\Search\Contracts\SearchProvider;
use Core\Admin\Search\SearchProviderRegistry;
use Core\Admin\Search\SearchResult;
use Illuminate\Support\Collection;

/**
 * Search provider for admin navigation pages.
 *
 * Provides quick access to admin pages via global search.
 * This is a built-in provider that indexes all admin navigation items.
 */
class AdminPageSearchProvider implements SearchProvider
{
    use HasSearchProvider;

    /**
     * Static list of admin pages.
     *
     * These are the core admin navigation items that are always available.
     * Modules can register additional search providers for their own pages.
     *
     * @var array<array{id: string, title: string, subtitle: string, url: string, icon: string}>
     */
    protected array $pages = [
        [
            'id' => 'dashboard',
            'title' => 'Dashboard',
            'subtitle' => 'Overview and quick actions',
            'url' => '/hub',
            'icon' => 'house',
        ],
        [
            'id' => 'workspaces',
            'title' => 'Workspaces',
            'subtitle' => 'Manage your workspaces',
            'url' => '/hub/sites',
            'icon' => 'folders',
        ],
        [
            'id' => 'profile',
            'title' => 'Profile',
            'subtitle' => 'Your account profile',
            'url' => '/hub/account',
            'icon' => 'user',
        ],
        [
            'id' => 'settings',
            'title' => 'Settings',
            'subtitle' => 'Account settings and preferences',
            'url' => '/hub/account/settings',
            'icon' => 'gear',
        ],
        [
            'id' => 'usage',
            'title' => 'Usage & Limits',
            'subtitle' => 'Monitor your usage and quotas',
            'url' => '/hub/account/usage',
            'icon' => 'chart-pie',
        ],
        [
            'id' => 'ai-services',
            'title' => 'AI Services',
            'subtitle' => 'Configure AI providers',
            'url' => '/hub/ai-services',
            'icon' => 'sparkles',
        ],
        [
            'id' => 'prompts',
            'title' => 'Prompt Manager',
            'subtitle' => 'Manage AI prompts',
            'url' => '/hub/prompts',
            'icon' => 'command',
        ],
        [
            'id' => 'content-manager',
            'title' => 'Content Manager',
            'subtitle' => 'Manage WordPress content',
            'url' => '/hub/content-manager',
            'icon' => 'newspaper',
        ],
        [
            'id' => 'deployments',
            'title' => 'Deployments',
            'subtitle' => 'View deployment history',
            'url' => '/hub/deployments',
            'icon' => 'rocket',
        ],
        [
            'id' => 'databases',
            'title' => 'Databases',
            'subtitle' => 'Database management',
            'url' => '/hub/databases',
            'icon' => 'database',
        ],
        [
            'id' => 'console',
            'title' => 'Server Console',
            'subtitle' => 'Terminal access',
            'url' => '/hub/console',
            'icon' => 'terminal',
        ],
        [
            'id' => 'analytics',
            'title' => 'Analytics',
            'subtitle' => 'Traffic and performance',
            'url' => '/hub/analytics',
            'icon' => 'chart-line',
        ],
        [
            'id' => 'activity',
            'title' => 'Activity Log',
            'subtitle' => 'Recent account activity',
            'url' => '/hub/activity',
            'icon' => 'clock-rotate-left',
        ],
    ];

    protected SearchProviderRegistry $registry;

    public function __construct(SearchProviderRegistry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Get the search type identifier.
     */
    public function searchType(): string
    {
        return 'pages';
    }

    /**
     * Get the display label for this search type.
     */
    public function searchLabel(): string
    {
        return __('Pages');
    }

    /**
     * Get the icon name for this search type.
     */
    public function searchIcon(): string
    {
        return 'rectangle-stack';
    }

    /**
     * Get the priority for ordering in search results.
     */
    public function searchPriority(): int
    {
        return 10; // Show pages first
    }

    /**
     * Execute a search query.
     *
     * @param  string  $query  The search query string
     * @param  int  $limit  Maximum number of results to return
     */
    public function search(string $query, int $limit = 5): Collection
    {
        return collect($this->pages)
            ->filter(function ($page) use ($query) {
                // Match against title and subtitle
                return $this->registry->fuzzyMatch($query, $page['title'])
                    || $this->registry->fuzzyMatch($query, $page['subtitle']);
            })
            ->sortByDesc(function ($page) use ($query) {
                // Sort by relevance to title
                return $this->registry->relevanceScore($query, $page['title']);
            })
            ->take($limit)
            ->map(function ($page) {
                return new SearchResult(
                    id: $page['id'],
                    title: $page['title'],
                    url: $page['url'],
                    type: $this->searchType(),
                    icon: $page['icon'],
                    subtitle: $page['subtitle'],
                );
            })
            ->values();
    }

    /**
     * Get the URL for a search result.
     *
     * @param  mixed  $result  The search result
     */
    public function getUrl(mixed $result): string
    {
        if ($result instanceof SearchResult) {
            return $result->url;
        }

        return $result['url'] ?? '#';
    }
}
