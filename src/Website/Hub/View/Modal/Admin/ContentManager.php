<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Cdn\Services\BunnyCdnService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Core\Mod\Content\Models\ContentItem;
use Core\Mod\Content\Models\ContentTaxonomy;
use Core\Mod\Content\Models\ContentWebhookLog;
use Core\Tenant\Models\Workspace;
use Core\Tenant\Services\WorkspaceService;

/**
 * Content Manager component.
 *
 * Native content system - WordPress sync removed.
 */
class ContentManager extends Component
{
    use WithPagination;

    // View mode: dashboard, kanban, calendar, list, webhooks
    public string $view = 'dashboard';

    // Filters
    #[Url]
    public string $search = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $syncStatus = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $contentType = ''; // hostuk, satellite

    // Sort
    #[Url]
    public string $sort = 'created_at';

    #[Url]
    public string $dir = 'desc';

    public int $perPage = 20;

    // Workspace (named currentWorkspace to avoid conflict with route parameter)
    public ?Workspace $currentWorkspace = null;

    public string $workspaceSlug = 'main';

    // Selected item for preview/edit
    public ?int $selectedItemId = null;

    public bool $showPreview = false;

    // Sync state
    public bool $syncing = false;

    public ?string $syncMessage = null;

    protected WorkspaceService $workspaceService;

    protected BunnyCdnService $cdn;

    public function boot(
        WorkspaceService $workspaceService,
        BunnyCdnService $cdn
    ): void {
        $this->workspaceService = $workspaceService;
        $this->cdn = $cdn;
    }

    public function mount(string $workspace = 'main', string $view = 'dashboard'): void
    {
        $this->workspaceSlug = $workspace;
        $this->view = $view;

        $this->currentWorkspace = Workspace::where('slug', $workspace)->first();

        if (! $this->currentWorkspace) {
            session()->flash('error', 'Workspace not found');
        }

        // Update session so sidebar links stay on this workspace
        $this->workspaceService->setCurrent($workspace);
    }

    #[On('workspace-changed')]
    public function handleWorkspaceChange(string $workspace): void
    {
        $this->workspaceSlug = $workspace;
        $this->currentWorkspace = Workspace::where('slug', $workspace)->first();
        $this->resetPage();
    }

    /**
     * Available tabs for navigation.
     */
    #[Computed]
    public function tabs(): array
    {
        return [
            'dashboard' => [
                'label' => __('hub::hub.content_manager.tabs.dashboard'),
                'icon' => 'chart-pie',
                'href' => route('hub.content-manager', ['workspace' => $this->workspaceSlug, 'view' => 'dashboard']),
            ],
            'kanban' => [
                'label' => __('hub::hub.content_manager.tabs.kanban'),
                'icon' => 'view-columns',
                'href' => route('hub.content-manager', ['workspace' => $this->workspaceSlug, 'view' => 'kanban']),
            ],
            'calendar' => [
                'label' => __('hub::hub.content_manager.tabs.calendar'),
                'icon' => 'calendar',
                'href' => route('hub.content-manager', ['workspace' => $this->workspaceSlug, 'view' => 'calendar']),
            ],
            'list' => [
                'label' => __('hub::hub.content_manager.tabs.list'),
                'icon' => 'list-bullet',
                'href' => route('hub.content-manager', ['workspace' => $this->workspaceSlug, 'view' => 'list']),
            ],
            'webhooks' => [
                'label' => __('hub::hub.content_manager.tabs.webhooks'),
                'icon' => 'bolt',
                'href' => route('hub.content-manager', ['workspace' => $this->workspaceSlug, 'view' => 'webhooks']),
            ],
        ];
    }

    /**
     * Get content statistics for dashboard.
     *
     * Uses conditional aggregation to consolidate 16+ queries into 3.
     */
    #[Computed]
    public function stats(): array
    {
        if (! $this->currentWorkspace) {
            return $this->emptyStats();
        }

        $id = $this->currentWorkspace->id;

        // Single query for all ContentItem stats using conditional counts
        $contentStats = ContentItem::forWorkspace($id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN type = 'post' THEN 1 ELSE 0 END) as posts")
            ->selectRaw("SUM(CASE WHEN type = 'page' THEN 1 ELSE 0 END) as pages")
            ->selectRaw("SUM(CASE WHEN status = 'publish' THEN 1 ELSE 0 END) as published")
            ->selectRaw("SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as drafts")
            ->selectRaw("SUM(CASE WHEN sync_status = 'synced' THEN 1 ELSE 0 END) as synced")
            ->selectRaw("SUM(CASE WHEN sync_status = 'pending' THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN sync_status = 'failed' THEN 1 ELSE 0 END) as failed")
            ->selectRaw("SUM(CASE WHEN sync_status = 'stale' THEN 1 ELSE 0 END) as stale")
            ->selectRaw("SUM(CASE WHEN content_type = 'wordpress' THEN 1 ELSE 0 END) as wordpress")
            ->selectRaw("SUM(CASE WHEN content_type = 'hostuk' THEN 1 ELSE 0 END) as hostuk")
            ->selectRaw("SUM(CASE WHEN content_type = 'satellite' THEN 1 ELSE 0 END) as satellite")
            ->first();

        // Single query for taxonomy stats
        $taxonomyStats = ContentTaxonomy::forWorkspace($id)
            ->selectRaw("SUM(CASE WHEN type = 'category' THEN 1 ELSE 0 END) as categories")
            ->selectRaw("SUM(CASE WHEN type = 'tag' THEN 1 ELSE 0 END) as tags")
            ->first();

        // Single query for webhook stats
        $webhookStats = ContentWebhookLog::forWorkspace($id)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw("SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today", [today()->toDateString()])
            ->selectRaw("SUM(CASE WHEN status = 'failed' THEN 1 ELSE 0 END) as failed")
            ->first();

        return [
            'total' => (int) ($contentStats->total ?? 0),
            'posts' => (int) ($contentStats->posts ?? 0),
            'pages' => (int) ($contentStats->pages ?? 0),
            'published' => (int) ($contentStats->published ?? 0),
            'drafts' => (int) ($contentStats->drafts ?? 0),
            'synced' => (int) ($contentStats->synced ?? 0),
            'pending' => (int) ($contentStats->pending ?? 0),
            'failed' => (int) ($contentStats->failed ?? 0),
            'stale' => (int) ($contentStats->stale ?? 0),
            'categories' => (int) ($taxonomyStats->categories ?? 0),
            'tags' => (int) ($taxonomyStats->tags ?? 0),
            'webhooks_today' => (int) ($webhookStats->today ?? 0),
            'webhooks_failed' => (int) ($webhookStats->failed ?? 0),
            'wordpress' => (int) ($contentStats->wordpress ?? 0),
            'hostuk' => (int) ($contentStats->hostuk ?? 0),
            'satellite' => (int) ($contentStats->satellite ?? 0),
        ];
    }

    /**
     * Get chart data for content over time (Flux chart format).
     *
     * Uses single query with GROUP BY instead of 30 separate queries.
     */
    #[Computed]
    public function chartData(): array
    {
        if (! $this->currentWorkspace) {
            return [];
        }

        $days = 30;
        $startDate = now()->subDays($days - 1)->startOfDay();

        // Single query with date grouping
        $counts = ContentItem::forWorkspace($this->currentWorkspace->id)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Build complete date range with zeros for missing dates
        $data = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $data[] = [
                'date' => $date,
                'count' => $counts[$date] ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Get content by type for donut chart.
     */
    #[Computed]
    public function contentByType(): array
    {
        if (! $this->currentWorkspace) {
            return [];
        }

        return [
            ['label' => 'Posts', 'value' => ContentItem::forWorkspace($this->currentWorkspace->id)->posts()->count()],
            ['label' => 'Pages', 'value' => ContentItem::forWorkspace($this->currentWorkspace->id)->pages()->count()],
        ];
    }

    /**
     * Get content grouped by status for Kanban board.
     *
     * Eager loads author and categories to prevent N+1 queries in view.
     */
    #[Computed]
    public function kanbanColumns(): array
    {
        if (! $this->currentWorkspace) {
            return [];
        }

        $id = $this->currentWorkspace->id;

        return [
            [
                'name' => 'Draft',
                'status' => 'draft',
                'color' => 'gray',
                'items' => ContentItem::forWorkspace($id)
                    ->with(['author', 'categories'])
                    ->where('status', 'draft')
                    ->orderBy('wp_modified_at', 'desc')
                    ->take(20)
                    ->get(),
            ],
            [
                'name' => 'Pending Review',
                'status' => 'pending',
                'color' => 'yellow',
                'items' => ContentItem::forWorkspace($id)
                    ->with(['author', 'categories'])
                    ->where('status', 'pending')
                    ->orderBy('wp_modified_at', 'desc')
                    ->take(20)
                    ->get(),
            ],
            [
                'name' => 'Scheduled',
                'status' => 'future',
                'color' => 'blue',
                'items' => ContentItem::forWorkspace($id)
                    ->with(['author', 'categories'])
                    ->where('status', 'future')
                    ->orderBy('wp_created_at', 'asc')
                    ->take(20)
                    ->get(),
            ],
            [
                'name' => 'Published',
                'status' => 'publish',
                'color' => 'green',
                'items' => ContentItem::forWorkspace($id)
                    ->with(['author', 'categories'])
                    ->published()
                    ->orderBy('wp_created_at', 'desc')
                    ->take(20)
                    ->get(),
            ],
        ];
    }

    /**
     * Get scheduled content for calendar view.
     */
    #[Computed]
    public function calendarEvents(): array
    {
        if (! $this->currentWorkspace) {
            return [];
        }

        return ContentItem::forWorkspace($this->currentWorkspace->id)
            ->whereNotNull('wp_created_at')
            ->orderBy('wp_created_at', 'desc')
            ->take(100)
            ->get()
            ->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'date' => $item->wp_created_at?->format('Y-m-d'),
                'type' => $item->type,
                'status' => $item->status,
                'color' => $item->status_color,
            ])
            ->toArray();
    }

    /**
     * Get paginated content for list view.
     */
    #[Computed]
    public function content()
    {
        if (! $this->currentWorkspace) {
            // Return empty paginator instead of collection for Flux table compatibility
            return ContentItem::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        $query = ContentItem::forWorkspace($this->currentWorkspace->id)
            ->with(['author', 'categories', 'tags']);

        // Apply filters
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', "%{$this->search}%")
                    ->orWhere('slug', 'like', "%{$this->search}%")
                    ->orWhere('excerpt', 'like', "%{$this->search}%");
            });
        }

        if ($this->type) {
            $query->where('type', $this->type);
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        if ($this->syncStatus) {
            $query->where('sync_status', $this->syncStatus);
        }

        if ($this->category) {
            $query->whereHas('categories', function ($q) {
                $q->where('slug', $this->category);
            });
        }

        if ($this->contentType) {
            $query->where('content_type', $this->contentType);
        }

        // Apply sorting
        $query->orderBy($this->sort, $this->dir);

        return $query->paginate($this->perPage);
    }

    /**
     * Get categories for filter dropdown.
     */
    #[Computed]
    public function categories(): array
    {
        if (! $this->currentWorkspace) {
            return [];
        }

        return ContentTaxonomy::forWorkspace($this->currentWorkspace->id)
            ->categories()
            ->orderBy('name')
            ->pluck('name', 'slug')
            ->toArray();
    }

    /**
     * Get recent webhook logs.
     */
    #[Computed]
    public function webhookLogs()
    {
        if (! $this->currentWorkspace) {
            // Return empty paginator instead of collection for Flux table compatibility
            return ContentWebhookLog::query()->whereRaw('1=0')->paginate($this->perPage);
        }

        return ContentWebhookLog::forWorkspace($this->currentWorkspace->id)
            ->orderBy('created_at', 'desc')
            ->paginate($this->perPage);
    }

    /**
     * Get the selected item for preview.
     */
    #[Computed]
    public function selectedItem(): ?ContentItem
    {
        if (! $this->selectedItemId) {
            return null;
        }

        return ContentItem::with(['author', 'categories', 'tags', 'featuredMedia'])
            ->find($this->selectedItemId);
    }

    /**
     * Trigger full sync for workspace.
     *
     * Note: WordPress sync removed - native content system.
     */
    public function syncAll(): void
    {
        if (! $this->currentWorkspace) {
            return;
        }

        $this->syncMessage = 'Native content system - external sync not required';
    }

    /**
     * Purge CDN cache for workspace.
     */
    public function purgeCache(): void
    {
        if (! $this->currentWorkspace) {
            return;
        }

        $success = $this->cdn->purgeWorkspace($this->currentWorkspace->slug);

        if ($success) {
            $this->syncMessage = 'CDN cache purged successfully';
        } else {
            $this->syncMessage = 'Failed to purge CDN cache';
        }
    }

    /**
     * Select an item for preview.
     */
    public function selectItem(int $id): void
    {
        $this->selectedItemId = $id;
        $this->dispatch('modal-show', name: 'content-preview');
    }

    /**
     * Close the preview panel.
     */
    public function closePreview(): void
    {
        $this->selectedItemId = null;
        $this->dispatch('modal-close', name: 'content-preview');
    }

    /**
     * Set the sort column.
     */
    public function setSort(string $column): void
    {
        if ($this->sort === $column) {
            $this->dir = $this->dir === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sort = $column;
            $this->dir = 'desc';
        }
    }

    /**
     * Clear all filters.
     */
    public function clearFilters(): void
    {
        $this->search = '';
        $this->type = '';
        $this->status = '';
        $this->syncStatus = '';
        $this->category = '';
        $this->contentType = '';
        $this->resetPage();
    }

    /**
     * Retry a failed webhook.
     *
     * Note: WordPress webhooks removed - native content system.
     */
    public function retryWebhook(int $logId): void
    {
        $log = ContentWebhookLog::find($logId);
        if ($log && $log->status === 'failed') {
            $log->update(['status' => 'pending', 'error_message' => null]);
            $this->syncMessage = 'Webhook marked for retry';
        }
    }

    protected function emptyStats(): array
    {
        return [
            'total' => 0,
            'posts' => 0,
            'pages' => 0,
            'published' => 0,
            'drafts' => 0,
            'synced' => 0,
            'pending' => 0,
            'failed' => 0,
            'stale' => 0,
            'categories' => 0,
            'tags' => 0,
            'webhooks_today' => 0,
            'webhooks_failed' => 0,
            'wordpress' => 0,
            'hostuk' => 0,
            'satellite' => 0,
        ];
    }

    public function render()
    {
        return view('hub::admin.content-manager')
            ->layout('hub::admin.layouts.app', [
                'title' => 'Content Manager',
                'workspace' => $this->currentWorkspace,
            ]);
    }
}
