<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Front\Admin\AdminMenuRegistry;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Core\Mod\Analytics\Enums\ChannelType;
use Core\Mod\Analytics\Models\AnalyticsEvent;
use Core\Mod\Analytics\Models\AnalyticsGoal;
use Core\Mod\Analytics\Models\AnalyticsSession;
use Core\Mod\Analytics\Models\AnalyticsVisitor;
use Core\Mod\Analytics\Models\AnalyticsWebsite;
use Core\Mod\Hub\Models\Service;
use Core\Mod\Notify\Models\PushCampaign;
use Core\Mod\Notify\Models\PushCampaignLog;
use Core\Mod\Notify\Models\PushSubscriber;
use Core\Mod\Notify\Models\PushWebsite;
use Core\Mod\Social\Enums\PostStatus;
use Core\Mod\Social\Models\Account as SocialAccount;
use Core\Mod\Social\Models\Post as SocialPost;
use Core\Mod\Support\Models\Conversation;
use Core\Mod\Support\Models\Mailbox;
use Core\Mod\Support\Models\Thread;
use Core\Mod\Tenant\Models\Workspace;
use Core\Mod\Tenant\Services\WorkspaceService;
use Core\Mod\Trust\Models\Campaign as TrustCampaign;
use Core\Mod\Trust\Models\Notification as TrustNotification;
// TODO: Bio service admin moved to Host UK app (Mod\Bio)
// These imports are commented out until the admin panel is refactored

#[Title('Services')]
class ServicesAdmin extends Component
{
    public string $service = 'bio';

    public string $tab = 'dashboard';

    public string $analyticsDateRange = '30d';

    // Page details view state
    public ?int $pageDetailsWebsiteId = null;

    public ?string $pageDetailsPath = null;

    // Analytics settings form
    public string $analyticsSettingsName = '';

    public string $analyticsSettingsHost = '';

    public string $analyticsSettingsTrackingType = 'lightweight';

    public bool $analyticsSettingsEnabled = true;

    public bool $analyticsSettingsPublicStats = false;

    public string $analyticsSettingsExcludedIps = '';

    // Mod dashboard view state (for Websites tab)
    public ?int $selectedWebsiteId = null;

    protected WorkspaceService $workspaceService;

    public function boot(WorkspaceService $workspaceService): void
    {
        $this->workspaceService = $workspaceService;
    }

    public function mount(?string $service = null, ?string $tab = null): void
    {
        if ($service && in_array($service, $this->availableServices())) {
            $this->service = $service;
        }

        if ($tab) {
            $this->tab = $tab;
        }

        if ($this->service === 'analytics') {
            // Load analytics settings if mounted directly on settings tab
            if ($this->tab === 'settings') {
                $this->loadAnalyticsSettings();
            }

            // Set selected channel for channels tab
            if ($this->tab === 'channels') {
                $this->selectedWebsiteId = $this->analyticsChannels->first()?->id;
            }
        }
    }

    /**
     * Get the current workspace from the workspace switcher.
     */
    #[Computed]
    public function workspace(): ?Workspace
    {
        return $this->workspaceService->currentModel();
    }

    #[On('workspace-changed')]
    public function refreshWorkspace(): void
    {
        unset($this->workspace);
        unset($this->services);
        unset($this->bioStats, $this->bioStatCards, $this->bioPages, $this->bioProjects);
        unset($this->socialStats, $this->socialStatCards, $this->socialAccounts, $this->socialPosts);
        unset($this->analyticsStats, $this->analyticsStatCards, $this->analyticsWebsites);
        unset($this->notifyStats, $this->notifyStatCards, $this->notifyWebsites);
        unset($this->trustStats, $this->trustStatCards, $this->trustCampaigns);
        unset($this->supportStats);
    }

    /**
     * Get all service items from the registry.
     * This is the single source of truth - services are defined in each module's Boot.php.
     */
    #[Computed]
    public function services(): array
    {
        $registry = app(AdminMenuRegistry::class);

        return $registry->getAllServiceItems(
            $this->workspace,
            auth()->user()?->isHades() ?? false
        );
    }

    /**
     * Get the current service's menu item.
     */
    #[Computed]
    public function currentServiceItem(): ?array
    {
        return $this->services[$this->service] ?? null;
    }

    /**
     * Get the current service's marketing URL from the database.
     */
    #[Computed]
    public function serviceMarketingUrl(): ?string
    {
        $service = Service::where('code', $this->service)->first();

        return $service?->marketing_url;
    }

    /**
     * Get children (tabs) for the current service.
     */
    #[Computed]
    public function serviceTabs(): array
    {
        return $this->currentServiceItem['children'] ?? [];
    }

    /**
     * Get available service keys for validation.
     */
    public function availableServices(): array
    {
        return array_keys($this->services);
    }

    public function switchService(string $service): void
    {
        if (in_array($service, $this->availableServices())) {
            $this->service = $service;
            $this->tab = 'dashboard';
        }
    }

    public function switchTab(string $tab): void
    {
        $this->tab = $tab;

        if ($this->service === 'analytics') {
            // Load analytics settings when entering settings tab
            if ($tab === 'settings') {
                $this->loadAnalyticsSettings();
            }

            // Set selected channel for channels tab
            if ($tab === 'channels') {
                $this->selectedWebsiteId = $this->analyticsChannels->first()?->id;
            }
        }
    }

    /**
     * Load analytics settings from the primary website.
     */
    public function loadAnalyticsSettings(): void
    {
        $website = $this->analyticsWebsites->first();

        if ($website) {
            $this->analyticsSettingsName = $website->name ?? '';
            $this->analyticsSettingsHost = $website->host ?? '';
            $this->analyticsSettingsTrackingType = $website->tracking_type ?? 'lightweight';
            $this->analyticsSettingsEnabled = (bool) $website->is_enabled;
            $this->analyticsSettingsPublicStats = (bool) $website->public_stats_enabled;
            $this->analyticsSettingsExcludedIps = $website->excluded_ips ?? '';
        }
    }

    /**
     * Save analytics settings for the primary website.
     */
    public function saveAnalyticsSettings(): void
    {
        $website = $this->analyticsWebsites->first();

        if (! $website) {
            return;
        }

        $website->update([
            'name' => $this->analyticsSettingsName,
            'host' => $this->analyticsSettingsHost,
            'tracking_type' => $this->analyticsSettingsTrackingType,
            'is_enabled' => $this->analyticsSettingsEnabled,
            'public_stats_enabled' => $this->analyticsSettingsPublicStats,
            'excluded_ips' => $this->analyticsSettingsExcludedIps,
        ]);

        // Clear computed cache
        unset($this->analyticsWebsites);

        $this->dispatch('notify', message: 'Settings saved successfully');
    }

    /**
     * Regenerate the analytics pixel key for the primary website.
     */
    public function regenerateAnalyticsPixelKey(): void
    {
        $website = $this->analyticsWebsites->first();

        if (! $website) {
            return;
        }

        $website->update([
            'pixel_key' => \Illuminate\Support\Str::random(32),
        ]);

        // Clear computed cache
        unset($this->analyticsWebsites);

        $this->dispatch('notify', message: 'Pixel key regenerated. Update your website tracking code.');
    }

    /**
     * Show page details within the services panel.
     */
    public function showPageDetails(int $websiteId, string $path): void
    {
        $this->pageDetailsWebsiteId = $websiteId;
        $this->pageDetailsPath = '/'.ltrim($path, '/');
        $this->tab = 'pages';
    }

    /**
     * Close page details and return to pages list.
     */
    public function closePageDetails(): void
    {
        $this->pageDetailsWebsiteId = null;
        $this->pageDetailsPath = null;
    }

    /**
     * Select a website to view its dashboard.
     */
    public function selectWebsite(int $websiteId): void
    {
        $this->selectedWebsiteId = $websiteId;
    }

    /**
     * Close website dashboard and return to list.
     */
    public function closeWebsiteDashboard(): void
    {
        $this->selectedWebsiteId = null;
    }

    /**
     * Check if we're viewing a website dashboard.
     */
    #[Computed]
    public function isViewingWebsiteDashboard(): bool
    {
        return $this->selectedWebsiteId !== null;
    }

    /**
     * Get the selected website.
     */
    #[Computed]
    public function selectedWebsite(): ?AnalyticsWebsite
    {
        if (! $this->selectedWebsiteId) {
            return null;
        }

        return $this->analyticsWebsites->firstWhere('id', $this->selectedWebsiteId);
    }

    /**
     * Get chart data for the selected website.
     */
    #[Computed]
    public function selectedWebsiteChartData(): array
    {
        if (! $this->selectedWebsiteId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days - 1)->startOfDay();

        $sessions = AnalyticsSession::where('website_id', $this->selectedWebsiteId)
            ->where('started_at', '>=', $startDate)
            ->selectRaw('DATE(started_at) as date, COUNT(DISTINCT visitor_id) as visitors, COUNT(*) as sessions')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i);
            $dateStr = $date->format('Y-m-d');
            $row = $sessions->get($dateStr);
            $data[] = [
                'date' => $date->format('M j'),
                'visitors' => $row?->visitors ?? 0,
                'sessions' => $row?->sessions ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Get top pages for the selected website.
     */
    #[Computed]
    public function selectedWebsiteTopPages(): array
    {
        if (! $this->selectedWebsiteId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days)->startOfDay();

        return AnalyticsEvent::where('website_id', $this->selectedWebsiteId)
            ->where('type', 'pageview')
            ->where('created_at', '>=', $startDate)
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get top referrers for the selected website.
     */
    #[Computed]
    public function selectedWebsiteReferrers(): array
    {
        if (! $this->selectedWebsiteId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days)->startOfDay();

        return AnalyticsSession::where('website_id', $this->selectedWebsiteId)
            ->whereNotNull('referrer_host')
            ->where('referrer_host', '!=', '')
            ->where('started_at', '>=', $startDate)
            ->selectRaw('referrer_host, COUNT(*) as sessions')
            ->groupBy('referrer_host')
            ->orderByDesc('sessions')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get device breakdown for the selected website.
     */
    #[Computed]
    public function selectedWebsiteDevices(): array
    {
        if (! $this->selectedWebsiteId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days)->startOfDay();

        return AnalyticsVisitor::where('website_id', $this->selectedWebsiteId)
            ->where('last_seen_at', '>=', $startDate)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();
    }

    /**
     * Get browser breakdown for the selected website.
     */
    #[Computed]
    public function selectedWebsiteBrowsers(): array
    {
        if (! $this->selectedWebsiteId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days)->startOfDay();

        return AnalyticsVisitor::where('website_id', $this->selectedWebsiteId)
            ->where('last_seen_at', '>=', $startDate)
            ->selectRaw('browser_name, COUNT(*) as count')
            ->groupBy('browser_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->pluck('count', 'browser_name')
            ->toArray();
    }

    /**
     * Get country breakdown for the selected website.
     */
    #[Computed]
    public function selectedWebsiteCountries(): array
    {
        if (! $this->selectedWebsiteId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days)->startOfDay();

        return AnalyticsVisitor::where('website_id', $this->selectedWebsiteId)
            ->whereNotNull('country_code')
            ->where('last_seen_at', '>=', $startDate)
            ->selectRaw('country_code, COUNT(*) as count')
            ->groupBy('country_code')
            ->orderByDesc('count')
            ->limit(10)
            ->get()
            ->pluck('count', 'country_code')
            ->toArray();
    }

    /**
     * Check if we're viewing page details.
     */
    #[Computed]
    public function isViewingPageDetails(): bool
    {
        return $this->pageDetailsWebsiteId !== null && $this->pageDetailsPath !== null;
    }

    /**
     * Get the website for page details.
     */
    #[Computed]
    public function pageDetailsWebsite(): ?AnalyticsWebsite
    {
        if (! $this->pageDetailsWebsiteId) {
            return null;
        }

        return AnalyticsWebsite::find($this->pageDetailsWebsiteId);
    }

    /**
     * Get stats for the page details view.
     */
    #[Computed]
    public function pageDetailsStats(): array
    {
        if (! $this->isViewingPageDetails) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $start = now()->subDays($days)->startOfDay();
        $end = now()->endOfDay();

        $views = AnalyticsEvent::where('website_id', $this->pageDetailsWebsiteId)
            ->where('type', 'pageview')
            ->where('path', $this->pageDetailsPath)
            ->whereBetween('created_at', [$start, $end])
            ->count();

        $visitors = AnalyticsEvent::where('website_id', $this->pageDetailsWebsiteId)
            ->where('type', 'pageview')
            ->where('path', $this->pageDetailsPath)
            ->whereBetween('created_at', [$start, $end])
            ->distinct('visitor_id')
            ->count('visitor_id');

        // Entry stats (sessions that started on this page)
        $entries = AnalyticsSession::where('website_id', $this->pageDetailsWebsiteId)
            ->where('landing_page', $this->pageDetailsPath)
            ->whereBetween('started_at', [$start, $end])
            ->count();

        $bounces = AnalyticsSession::where('website_id', $this->pageDetailsWebsiteId)
            ->where('landing_page', $this->pageDetailsPath)
            ->where('is_bounce', true)
            ->whereBetween('started_at', [$start, $end])
            ->count();

        $bounceRate = $entries > 0 ? round(($bounces / $entries) * 100, 1) : 0;

        // Exit stats
        $exits = AnalyticsSession::where('website_id', $this->pageDetailsWebsiteId)
            ->where('exit_page', $this->pageDetailsPath)
            ->whereBetween('started_at', [$start, $end])
            ->count();

        $exitRate = $views > 0 ? round(($exits / $views) * 100, 1) : 0;

        // Average time on page
        $avgDuration = AnalyticsSession::where('website_id', $this->pageDetailsWebsiteId)
            ->where('landing_page', $this->pageDetailsPath)
            ->where('is_bounce', false)
            ->whereBetween('started_at', [$start, $end])
            ->avg('duration') ?? 0;

        return [
            'views' => $views,
            'visitors' => $visitors,
            'entries' => $entries,
            'bounce_rate' => $bounceRate,
            'exits' => $exits,
            'exit_rate' => $exitRate,
            'avg_duration' => (int) $avgDuration,
            'views_per_visitor' => $visitors > 0 ? round($views / $visitors, 1) : 0,
        ];
    }

    /**
     * Get chart data for page details.
     */
    #[Computed]
    public function pageDetailsChartData(): array
    {
        if (! $this->isViewingPageDetails) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $startDate = now()->subDays($days - 1)->startOfDay();

        $events = AnalyticsEvent::where('website_id', $this->pageDetailsWebsiteId)
            ->where('type', 'pageview')
            ->where('path', $this->pageDetailsPath)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('views', 'date')
            ->toArray();

        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $startDate->copy()->addDays($i)->format('M j'),
                'views' => $events[$date] ?? 0,
            ];
        }

        return $data;
    }

    /**
     * Get referrers for page details.
     */
    #[Computed]
    public function pageDetailsReferrers(): array
    {
        if (! $this->isViewingPageDetails) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $start = now()->subDays($days)->startOfDay();

        return AnalyticsSession::where('website_id', $this->pageDetailsWebsiteId)
            ->where('landing_page', $this->pageDetailsPath)
            ->whereNotNull('referrer_host')
            ->where('referrer_host', '!=', '')
            ->where('started_at', '>=', $start)
            ->selectRaw('referrer_host, COUNT(*) as sessions')
            ->groupBy('referrer_host')
            ->orderByDesc('sessions')
            ->limit(10)
            ->get()
            ->toArray();
    }

    /**
     * Get device breakdown for page details.
     */
    #[Computed]
    public function pageDetailsDevices(): array
    {
        if (! $this->isViewingPageDetails) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $start = now()->subDays($days)->startOfDay();

        $visitorIds = AnalyticsEvent::where('website_id', $this->pageDetailsWebsiteId)
            ->where('type', 'pageview')
            ->where('path', $this->pageDetailsPath)
            ->where('created_at', '>=', $start)
            ->pluck('visitor_id')
            ->unique();

        return AnalyticsVisitor::whereIn('id', $visitorIds)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->orderByDesc('count')
            ->get()
            ->pluck('count', 'device_type')
            ->toArray();
    }

    /**
     * Get browser breakdown for page details.
     */
    #[Computed]
    public function pageDetailsBrowsers(): array
    {
        if (! $this->isViewingPageDetails) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $start = now()->subDays($days)->startOfDay();

        $visitorIds = AnalyticsEvent::where('website_id', $this->pageDetailsWebsiteId)
            ->where('type', 'pageview')
            ->where('path', $this->pageDetailsPath)
            ->where('created_at', '>=', $start)
            ->pluck('visitor_id')
            ->unique();

        return AnalyticsVisitor::whereIn('id', $visitorIds)
            ->selectRaw('browser_name, COUNT(*) as count')
            ->groupBy('browser_name')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->pluck('count', 'browser_name')
            ->toArray();
    }

    // ========================================
    // BIO STATS (workspace-scoped)
    // ========================================

    // TODO: Bio service admin moved to Host UK app (Mod\Bio)
    // These computed properties are stubbed until the admin panel is refactored

    #[Computed]
    public function bioStats(): array
    {
        return ['total_pages' => 0, 'active_pages' => 0, 'total_clicks' => 0, 'total_projects' => 0, 'biolinks' => 0, 'shortlinks' => 0];
    }

    #[Computed]
    public function bioStatCards(): array
    {
        return [];
    }

    #[Computed]
    public function bioPages(): \Illuminate\Support\Collection
    {
        return collect();
    }

    #[Computed]
    public function bioProjects(): \Illuminate\Support\Collection
    {
        return collect();
    }

    #[Computed]
    public function bioThemes(): array
    {
        return [];
    }

    // ========================================
    // SOCIAL STATS (workspace-scoped)
    // ========================================

    #[Computed]
    public function socialStats(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return ['total_accounts' => 0, 'active_accounts' => 0, 'total_posts' => 0, 'scheduled_posts' => 0, 'published_posts' => 0, 'failed_posts' => 0];
        }

        return [
            'total_accounts' => SocialAccount::where('workspace_id', $workspaceId)->count(),
            'active_accounts' => SocialAccount::where('workspace_id', $workspaceId)->where('status', 'active')->count(),
            'total_posts' => SocialPost::where('workspace_id', $workspaceId)->count(),
            'scheduled_posts' => SocialPost::where('workspace_id', $workspaceId)->where('status', PostStatus::SCHEDULED)->count(),
            'published_posts' => SocialPost::where('workspace_id', $workspaceId)->where('status', PostStatus::PUBLISHED)->count(),
            'failed_posts' => SocialPost::where('workspace_id', $workspaceId)->where('status', PostStatus::FAILED)->count(),
        ];
    }

    #[Computed]
    public function socialStatCards(): array
    {
        return [
            ['value' => number_format($this->socialStats['total_accounts']), 'label' => __('hub::hub.services.stats.social.total_accounts'), 'icon' => 'users', 'color' => 'violet'],
            ['value' => number_format($this->socialStats['active_accounts']), 'label' => __('hub::hub.services.stats.social.active_accounts'), 'icon' => 'check-circle', 'color' => 'green'],
            ['value' => number_format($this->socialStats['scheduled_posts']), 'label' => __('hub::hub.services.stats.social.scheduled_posts'), 'icon' => 'calendar', 'color' => 'blue'],
            ['value' => number_format($this->socialStats['published_posts']), 'label' => __('hub::hub.services.stats.social.published_posts'), 'icon' => 'paper-plane', 'color' => 'orange'],
        ];
    }

    #[Computed]
    public function socialAccounts(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        return SocialAccount::where('workspace_id', $workspaceId)
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function socialPosts(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        return SocialPost::with(['accounts', 'user'])
            ->where('workspace_id', $workspaceId)
            ->latest()
            ->take(50)
            ->get();
    }

    // ========================================
    // ANALYTICS STATS (workspace-scoped)
    // ========================================

    #[Computed]
    public function analyticsStats(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return ['total_websites' => 0, 'active_websites' => 0, 'pageviews_today' => 0, 'pageviews_week' => 0, 'pageviews_month' => 0, 'sessions_today' => 0];
        }

        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $websiteIds = AnalyticsWebsite::where('workspace_id', $workspaceId)->pluck('id');

        return [
            'total_websites' => AnalyticsWebsite::where('workspace_id', $workspaceId)->count(),
            'active_websites' => AnalyticsWebsite::where('workspace_id', $workspaceId)->enabled()->count(),
            'pageviews_today' => AnalyticsEvent::whereIn('website_id', $websiteIds)->pageviews()->where('created_at', '>=', $today)->count(),
            'pageviews_week' => AnalyticsEvent::whereIn('website_id', $websiteIds)->pageviews()->where('created_at', '>=', $weekStart)->count(),
            'pageviews_month' => AnalyticsEvent::whereIn('website_id', $websiteIds)->pageviews()->where('created_at', '>=', $monthStart)->count(),
            'sessions_today' => AnalyticsSession::whereIn('website_id', $websiteIds)->where('started_at', '>=', $today)->count(),
        ];
    }

    #[Computed]
    public function analyticsStatCards(): array
    {
        return [
            ['value' => number_format($this->analyticsStats['total_websites']), 'label' => __('hub::hub.services.stats.analytics.total_websites'), 'icon' => 'globe', 'color' => 'violet'],
            ['value' => number_format($this->analyticsStats['active_websites']), 'label' => __('hub::hub.services.stats.analytics.active_websites'), 'icon' => 'check-circle', 'color' => 'green'],
            ['value' => number_format($this->analyticsStats['pageviews_today']), 'label' => __('hub::hub.services.stats.analytics.pageviews_today'), 'icon' => 'eye', 'color' => 'blue'],
            ['value' => number_format($this->analyticsStats['sessions_today']), 'label' => __('hub::hub.services.stats.analytics.sessions_today'), 'icon' => 'users', 'color' => 'orange'],
        ];
    }

    #[Computed]
    public function analyticsWebsites(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $startDate = now()->subDays($days)->startOfDay();

        return AnalyticsWebsite::where('workspace_id', $workspaceId)
            ->withCount([
                'events as pageviews_count' => fn ($q) => $q->pageviews()->where('created_at', '>=', $startDate),
                'sessions as sessions_count' => fn ($q) => $q->where('started_at', '>=', $startDate),
                'sessions as bounced_sessions_count' => fn ($q) => $q->where('started_at', '>=', $startDate)->where('is_bounce', true),
            ])
            ->withSum(['sessions as total_duration' => fn ($q) => $q->where('started_at', '>=', $startDate)->whereNotNull('duration')], 'duration')
            ->orderByDesc('pageviews_count')
            ->get()
            ->map(function ($website) use ($startDate) {
                // Calculate derived metrics
                $website->visitors_count = AnalyticsSession::where('website_id', $website->id)
                    ->where('started_at', '>=', $startDate)
                    ->distinct('visitor_id')
                    ->count('visitor_id');

                $website->bounce_rate = $website->sessions_count > 0
                    ? round(($website->bounced_sessions_count / $website->sessions_count) * 100, 1)
                    : 0;

                $website->avg_duration = $website->sessions_count > 0
                    ? (int) round($website->total_duration / $website->sessions_count)
                    : 0;

                return $website;
            });
    }

    /**
     * Get all analytics channels for the workspace, grouped by type.
     */
    #[Computed]
    public function analyticsChannels(): \Illuminate\Support\Collection
    {
        return $this->analyticsWebsites;
    }

    /**
     * Get analytics channels grouped by channel type.
     */
    #[Computed]
    public function analyticsChannelsByType(): array
    {
        $channels = $this->analyticsChannels;

        $grouped = [];
        foreach (ChannelType::cases() as $type) {
            $typeChannels = $channels->filter(fn ($c) => ($c->channel_type?->value ?? 'website') === $type->value);
            if ($typeChannels->isNotEmpty()) {
                $grouped[$type->value] = [
                    'type' => $type,
                    'label' => $type->label(),
                    'icon' => $type->icon(),
                    'color' => $type->color(),
                    'channels' => $typeChannels,
                ];
            }
        }

        return $grouped;
    }

    #[Computed]
    public function analyticsChartData(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            default => 30,
        };

        $websiteIds = AnalyticsWebsite::where('workspace_id', $workspaceId)->pluck('id');

        if ($websiteIds->isEmpty()) {
            return [];
        }

        $startDate = now()->subDays($days - 1)->startOfDay();

        // Get daily pageview counts
        $pageviews = AnalyticsEvent::whereIn('website_id', $websiteIds)
            ->pageviews()
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('count', 'date')
            ->toArray();

        // Build chart data with all dates
        $data = [];
        for ($i = 0; $i < $days; $i++) {
            $date = $startDate->copy()->addDays($i)->format('Y-m-d');
            $data[] = [
                'date' => $startDate->copy()->addDays($i)->format('M j'),
                'pageviews' => $pageviews[$date] ?? 0,
            ];
        }

        return $data;
    }

    #[Computed]
    public function analyticsTopPages(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => null,
            default => 30,
        };

        $websiteIds = AnalyticsWebsite::where('workspace_id', $workspaceId)->pluck('id');

        if ($websiteIds->isEmpty()) {
            return collect();
        }

        // Get pageview stats
        $query = AnalyticsEvent::whereIn('website_id', $websiteIds)
            ->pageviews()
            ->selectRaw('path, COUNT(*) as views, COUNT(DISTINCT visitor_id) as visitors')
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(10);

        if ($days !== null) {
            $query->where('created_at', '>=', now()->subDays($days)->startOfDay());
        }

        $pages = $query->get();

        // Get bounce rates by landing page
        $bounceQuery = AnalyticsSession::whereIn('website_id', $websiteIds)
            ->whereNotNull('landing_page')
            ->selectRaw('landing_page, COUNT(*) as entries, SUM(CASE WHEN is_bounce = 1 THEN 1 ELSE 0 END) as bounces');

        if ($days !== null) {
            $bounceQuery->where('started_at', '>=', now()->subDays($days)->startOfDay());
        }

        $bounceRates = $bounceQuery->groupBy('landing_page')->get()->keyBy('landing_page');

        // Merge bounce rate into pages
        return $pages->map(function ($page) use ($bounceRates) {
            $bounceData = $bounceRates->get($page->path);
            $page->entries = $bounceData?->entries ?? 0;
            $page->bounces = $bounceData?->bounces ?? 0;
            $page->bounce_rate = $page->entries > 0
                ? round(($page->bounces / $page->entries) * 100, 1)
                : null;

            return $page;
        });
    }

    /**
     * Get analytics summary metrics for the inline summary bar.
     * Returns total pageviews, unique visitors, bounce rate, and avg session duration
     * based on the selected date range.
     */
    #[Computed]
    public function analyticsSummaryMetrics(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [
                'total_pageviews' => 0,
                'unique_visitors' => 0,
                'bounce_rate' => 0,
                'avg_session_duration' => 0,
            ];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => null,
            default => 30,
        };

        $websiteIds = AnalyticsWebsite::where('workspace_id', $workspaceId)->pluck('id');

        if ($websiteIds->isEmpty()) {
            return [
                'total_pageviews' => 0,
                'unique_visitors' => 0,
                'bounce_rate' => 0,
                'avg_session_duration' => 0,
            ];
        }

        $query = AnalyticsEvent::whereIn('website_id', $websiteIds)->pageviews();
        $sessionQuery = AnalyticsSession::whereIn('website_id', $websiteIds);

        if ($days !== null) {
            $startDate = now()->subDays($days)->startOfDay();
            $query->where('created_at', '>=', $startDate);
            $sessionQuery->where('started_at', '>=', $startDate);
        }

        $totalPageviews = $query->count();

        // Unique visitors (distinct visitor_ids from sessions)
        $uniqueVisitors = (clone $sessionQuery)->distinct('visitor_id')->count('visitor_id');

        // Bounce rate: sessions with only 1 pageview / total sessions
        $totalSessions = (clone $sessionQuery)->count();
        $bouncedSessions = (clone $sessionQuery)->where('pageviews', 1)->count();
        $bounceRate = $totalSessions > 0 ? round(($bouncedSessions / $totalSessions) * 100, 1) : 0;

        // Average session duration in seconds
        $avgDuration = (clone $sessionQuery)->whereNotNull('ended_at')->avg(\DB::raw('TIMESTAMPDIFF(SECOND, started_at, ended_at)')) ?? 0;

        return [
            'total_pageviews' => $totalPageviews,
            'unique_visitors' => $uniqueVisitors,
            'bounce_rate' => $bounceRate,
            'avg_session_duration' => (int) round($avgDuration),
        ];
    }

    /**
     * Format seconds into a human-readable duration (e.g., "2m 30s").
     */
    public function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.'s';
        }

        $minutes = floor($seconds / 60);
        $remainingSeconds = $seconds % 60;

        if ($minutes < 60) {
            return $remainingSeconds > 0 ? "{$minutes}m {$remainingSeconds}s" : "{$minutes}m";
        }

        $hours = floor($minutes / 60);
        $remainingMinutes = $minutes % 60;

        return $remainingMinutes > 0 ? "{$hours}h {$remainingMinutes}m" : "{$hours}h";
    }

    #[Computed]
    public function analyticsAcquisitionChannels(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $websiteIds = AnalyticsWebsite::where('workspace_id', $workspaceId)->pluck('id');

        if ($websiteIds->isEmpty()) {
            return [];
        }

        $startDate = now()->subDays($days)->startOfDay();

        // Get sessions grouped by referrer type
        $sessions = AnalyticsSession::whereIn('website_id', $websiteIds)
            ->where('started_at', '>=', $startDate)
            ->get(['referrer_host', 'utm_source', 'utm_medium']);

        $total = $sessions->count();

        if ($total === 0) {
            return [];
        }

        // Categorise traffic sources
        $channels = [
            'direct' => 0,
            'search' => 0,
            'social' => 0,
            'referral' => 0,
        ];

        $searchEngines = ['google', 'bing', 'yahoo', 'duckduckgo', 'baidu', 'yandex'];
        $socialNetworks = ['facebook', 'twitter', 'instagram', 'linkedin', 'youtube', 'tiktok', 'pinterest', 'reddit'];

        foreach ($sessions as $session) {
            $host = strtolower($session->referrer_host ?? '');
            $source = strtolower($session->utm_source ?? '');
            $medium = strtolower($session->utm_medium ?? '');

            // Direct traffic (no referrer)
            if (empty($host) && empty($source)) {
                $channels['direct']++;

                continue;
            }

            // Check UTM medium first
            if (in_array($medium, ['cpc', 'ppc', 'organic', 'search'])) {
                $channels['search']++;

                continue;
            }
            if (in_array($medium, ['social', 'social-media'])) {
                $channels['social']++;

                continue;
            }

            // Check referrer host for search engines
            foreach ($searchEngines as $engine) {
                if (str_contains($host, $engine) || str_contains($source, $engine)) {
                    $channels['search']++;

                    continue 2;
                }
            }

            // Check referrer host for social networks
            foreach ($socialNetworks as $network) {
                if (str_contains($host, $network) || str_contains($source, $network)) {
                    $channels['social']++;

                    continue 2;
                }
            }

            // Everything else is referral
            $channels['referral']++;
        }

        $colours = [
            'direct' => '#8b5cf6',
            'search' => '#06b6d4',
            'social' => '#f59e0b',
            'referral' => '#10b981',
        ];

        $labels = [
            'direct' => __('hub::hub.services.analytics.channels.direct'),
            'search' => __('hub::hub.services.analytics.channels.search'),
            'social' => __('hub::hub.services.analytics.channels.social'),
            'referral' => __('hub::hub.services.analytics.channels.referral'),
        ];

        return collect($channels)
            ->filter(fn ($count) => $count > 0)
            ->map(fn ($count, $key) => [
                'name' => $labels[$key] ?? ucfirst($key),
                'count' => $count,
                'percentage' => round(($count / $total) * 100, 1),
                'color' => $colours[$key] ?? '#6b7280',
            ])
            ->sortByDesc('count')
            ->values()
            ->toArray();
    }

    #[Computed]
    public function analyticsDeviceBreakdown(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [];
        }

        $days = match ($this->analyticsDateRange) {
            '7d' => 7,
            '30d' => 30,
            '90d' => 90,
            'all' => 365,
            default => 30,
        };

        $websiteIds = AnalyticsWebsite::where('workspace_id', $workspaceId)->pluck('id');

        if ($websiteIds->isEmpty()) {
            return [];
        }

        $startDate = now()->subDays($days)->startOfDay();

        // Get visitors by device type
        $devices = AnalyticsVisitor::whereIn('website_id', $websiteIds)
            ->where('last_seen_at', '>=', $startDate)
            ->selectRaw('device_type, COUNT(*) as count')
            ->groupBy('device_type')
            ->pluck('count', 'device_type')
            ->toArray();

        $total = array_sum($devices);

        if ($total === 0) {
            return [];
        }

        $icons = [
            'desktop' => 'computer-desktop',
            'mobile' => 'device-phone-mobile',
            'tablet' => 'device-tablet',
        ];

        $labels = [
            'desktop' => __('hub::hub.services.analytics.devices.desktop'),
            'mobile' => __('hub::hub.services.analytics.devices.mobile'),
            'tablet' => __('hub::hub.services.analytics.devices.tablet'),
        ];

        // Ensure all device types are represented
        $deviceTypes = ['desktop', 'mobile', 'tablet'];
        $result = [];

        foreach ($deviceTypes as $type) {
            $count = $devices[$type] ?? 0;
            if ($count > 0 || $total > 0) {
                $result[] = [
                    'name' => $labels[$type] ?? ucfirst($type),
                    'icon' => $icons[$type] ?? 'question-mark-circle',
                    'count' => $count,
                    'percentage' => $total > 0 ? round(($count / $total) * 100, 0) : 0,
                ];
            }
        }

        return $result;
    }

    #[Computed]
    public function analyticsGoals(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        $websiteIds = AnalyticsWebsite::where('workspace_id', $workspaceId)->pluck('id');

        if ($websiteIds->isEmpty()) {
            return collect();
        }

        return AnalyticsGoal::with('website')
            ->whereIn('website_id', $websiteIds)
            ->withCount([
                'conversions as conversions_count' => fn ($q) => $q->where('created_at', '>=', now()->startOfMonth()),
            ])
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function analyticsGoalTypes(): array
    {
        return [
            'pageview' => ['label' => 'Page Visit', 'color' => 'blue', 'icon' => 'document-text'],
            'event' => ['label' => 'Custom Event', 'color' => 'purple', 'icon' => 'bolt'],
            'duration' => ['label' => 'Time on Page', 'color' => 'orange', 'icon' => 'clock'],
            'pages_per_session' => ['label' => 'Pages Per Session', 'color' => 'green', 'icon' => 'document-duplicate'],
        ];
    }

    // ========================================
    // NOTIFY STATS (workspace-scoped)
    // ========================================

    #[Computed]
    public function notifyStats(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return ['total_websites' => 0, 'total_subscribers' => 0, 'active_subscribers' => 0, 'active_campaigns' => 0, 'messages_today' => 0];
        }

        $websiteIds = PushWebsite::where('workspace_id', $workspaceId)->pluck('id');

        return [
            'total_websites' => PushWebsite::where('workspace_id', $workspaceId)->count(),
            'total_subscribers' => PushSubscriber::whereIn('website_id', $websiteIds)->count(),
            'active_subscribers' => PushSubscriber::whereIn('website_id', $websiteIds)->where('is_subscribed', true)->count(),
            'active_campaigns' => PushCampaign::whereIn('website_id', $websiteIds)->whereIn('status', [PushCampaign::STATUS_SCHEDULED, PushCampaign::STATUS_SENDING])->count(),
            'messages_today' => PushCampaignLog::whereIn('campaign_id', PushCampaign::whereIn('website_id', $websiteIds)->pluck('id'))->whereDate('sent_at', today())->count(),
        ];
    }

    #[Computed]
    public function notifyStatCards(): array
    {
        return [
            ['value' => number_format($this->notifyStats['total_websites']), 'label' => __('hub::hub.services.stats.notify.websites'), 'icon' => 'globe', 'color' => 'purple'],
            ['value' => number_format($this->notifyStats['active_subscribers']), 'label' => __('hub::hub.services.stats.notify.active_subscribers'), 'icon' => 'users', 'color' => 'blue'],
            ['value' => number_format($this->notifyStats['active_campaigns']), 'label' => __('hub::hub.services.stats.notify.active_campaigns'), 'icon' => 'bullhorn', 'color' => 'orange'],
            ['value' => number_format($this->notifyStats['messages_today']), 'label' => __('hub::hub.services.stats.notify.messages_today'), 'icon' => 'paper-plane', 'color' => 'green'],
        ];
    }

    #[Computed]
    public function notifyWebsites(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        return PushWebsite::where('workspace_id', $workspaceId)
            ->withCount(['subscribers' => fn ($q) => $q->where('is_subscribed', true)])
            ->orderByDesc('subscribers_count')
            ->get();
    }

    #[Computed]
    public function notifySubscribers(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        $websiteIds = PushWebsite::where('workspace_id', $workspaceId)->pluck('id');

        return PushSubscriber::with('website')
            ->whereIn('website_id', $websiteIds)
            ->latest('subscribed_at')
            ->take(100)
            ->get();
    }

    #[Computed]
    public function notifyCampaigns(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        $websiteIds = PushWebsite::where('workspace_id', $workspaceId)->pluck('id');

        return PushCampaign::with(['website', 'user'])
            ->whereIn('website_id', $websiteIds)
            ->latest()
            ->get();
    }

    // ========================================
    // TRUST STATS (workspace-scoped)
    // ========================================

    #[Computed]
    public function trustStats(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return ['total_campaigns' => 0, 'active_campaigns' => 0, 'total_notifications' => 0, 'total_impressions' => 0, 'total_clicks' => 0, 'total_conversions' => 0];
        }

        $campaignIds = TrustCampaign::where('workspace_id', $workspaceId)->pluck('id');

        return [
            'total_campaigns' => TrustCampaign::where('workspace_id', $workspaceId)->count(),
            'active_campaigns' => TrustCampaign::where('workspace_id', $workspaceId)->where('is_enabled', true)->count(),
            'total_notifications' => TrustNotification::whereIn('campaign_id', $campaignIds)->count(),
            'total_impressions' => TrustNotification::whereIn('campaign_id', $campaignIds)->sum('impressions'),
            'total_clicks' => TrustNotification::whereIn('campaign_id', $campaignIds)->sum('clicks'),
            'total_conversions' => TrustNotification::whereIn('campaign_id', $campaignIds)->sum('conversions'),
        ];
    }

    #[Computed]
    public function trustStatCards(): array
    {
        return [
            ['value' => number_format($this->trustStats['total_campaigns']), 'label' => __('hub::hub.services.stats.trust.total_campaigns'), 'icon' => 'megaphone', 'color' => 'blue'],
            ['value' => number_format($this->trustStats['active_campaigns']), 'label' => __('hub::hub.services.stats.trust.active_campaigns'), 'icon' => 'check-circle', 'color' => 'green'],
            ['value' => number_format($this->trustStats['total_notifications']), 'label' => __('hub::hub.services.stats.trust.total_widgets'), 'icon' => 'bell', 'color' => 'purple'],
            ['value' => number_format($this->trustStats['total_impressions']), 'label' => __('hub::hub.services.stats.trust.total_impressions'), 'icon' => 'eye', 'color' => 'orange'],
        ];
    }

    /**
     * Get aggregated Trust metrics for summary display.
     */
    #[Computed]
    public function trustAggregatedMetrics(): array
    {
        $stats = $this->trustStats;

        $ctr = $stats['total_impressions'] > 0 ? round(($stats['total_clicks'] / $stats['total_impressions']) * 100, 2) : 0;
        $cvr = $stats['total_impressions'] > 0 ? round(($stats['total_conversions'] / $stats['total_impressions']) * 100, 2) : 0;

        return [
            'impressions' => $stats['total_impressions'],
            'clicks' => $stats['total_clicks'],
            'conversions' => $stats['total_conversions'],
            'ctr' => $ctr,
            'cvr' => $cvr,
        ];
    }

    #[Computed]
    public function trustCampaigns(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        return TrustCampaign::where('workspace_id', $workspaceId)
            ->withCount('notifications')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function trustNotifications(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        $campaignIds = TrustCampaign::where('workspace_id', $workspaceId)->pluck('id');

        return TrustNotification::with('campaign')
            ->whereIn('campaign_id', $campaignIds)
            ->orderByDesc('impressions')
            ->get();
    }

    // ========================================
    // SUPPORT STATS (workspace-scoped)
    // ========================================

    #[Computed]
    public function supportStats(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [
                'open_tickets' => 0,
                'new_today' => 0,
                'resolved_today' => 0,
                'total_mailboxes' => 0,
            ];
        }

        $today = now()->startOfDay();
        $mailboxIds = Mailbox::where('workspace_id', $workspaceId)->pluck('id');

        return [
            'open_tickets' => Conversation::whereIn('mailbox_id', $mailboxIds)
                ->whereIn('status', ['active', 'pending'])
                ->count(),
            'new_today' => Conversation::whereIn('mailbox_id', $mailboxIds)
                ->where('created_at', '>=', $today)
                ->count(),
            'resolved_today' => Conversation::whereIn('mailbox_id', $mailboxIds)
                ->where('status', 'closed')
                ->where('closed_at', '>=', $today)
                ->count(),
            'total_mailboxes' => Mailbox::where('workspace_id', $workspaceId)->count(),
        ];
    }

    /**
     * Inbox health for support dashboard - open tickets and oldest unresponded.
     */
    #[Computed]
    public function supportInboxHealth(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [
                'open_tickets' => 0,
                'oldest_unresponded' => null,
                'avg_response_time' => null,
            ];
        }

        $mailboxIds = Mailbox::where('workspace_id', $workspaceId)->pluck('id');

        $openTickets = Conversation::whereIn('mailbox_id', $mailboxIds)
            ->whereIn('status', ['active', 'pending'])
            ->count();

        // Find oldest unresponded conversation
        $oldestUnresponded = Conversation::query()
            ->whereIn('mailbox_id', $mailboxIds)
            ->whereIn('status', ['active', 'pending'])
            ->whereDoesntHave('threads', function ($query) {
                $query->where('type', 'message');
            })
            ->orderBy('created_at')
            ->first();

        // Calculate average response time
        $avgResponseTime = $this->calculateSupportAvgResponseTime($mailboxIds);

        return [
            'open_tickets' => $openTickets,
            'oldest_unresponded' => $oldestUnresponded,
            'avg_response_time' => $avgResponseTime,
        ];
    }

    /**
     * Today's activity for support dashboard.
     */
    #[Computed]
    public function supportTodaysActivity(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [
                'new_conversations' => 0,
                'resolved_today' => 0,
                'messages_sent' => 0,
            ];
        }

        $today = now()->startOfDay();
        $mailboxIds = Mailbox::where('workspace_id', $workspaceId)->pluck('id');
        $conversationIds = Conversation::whereIn('mailbox_id', $mailboxIds)->pluck('id');

        return [
            'new_conversations' => Conversation::whereIn('mailbox_id', $mailboxIds)
                ->where('created_at', '>=', $today)
                ->count(),
            'resolved_today' => Conversation::whereIn('mailbox_id', $mailboxIds)
                ->where('status', 'closed')
                ->where('closed_at', '>=', $today)
                ->count(),
            'messages_sent' => Thread::whereIn('conversation_id', $conversationIds)
                ->where('created_at', '>=', $today)
                ->where('type', 'message')
                ->count(),
        ];
    }

    /**
     * Performance metrics for support dashboard.
     */
    #[Computed]
    public function supportPerformance(): array
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return [
                'first_response_time' => null,
                'resolution_time' => null,
            ];
        }

        $mailboxIds = Mailbox::where('workspace_id', $workspaceId)->pluck('id');

        return [
            'first_response_time' => $this->calculateSupportFirstResponseTime($mailboxIds),
            'resolution_time' => $this->calculateSupportResolutionTime($mailboxIds),
        ];
    }

    /**
     * Inbox health cards for support service.
     */
    #[Computed]
    public function supportInboxHealthCards(): array
    {
        $health = $this->supportInboxHealth;

        return [
            [
                'value' => number_format($health['open_tickets']),
                'label' => __('hub::hub.services.support.open_tickets'),
                'icon' => 'inbox',
                'color' => 'blue',
                'oldest' => $health['oldest_unresponded'],
            ],
            [
                'value' => $health['avg_response_time'] ?? __('hub::hub.services.support.na'),
                'label' => __('hub::hub.services.support.avg_response_time'),
                'icon' => 'clock',
                'color' => 'green',
            ],
        ];
    }

    /**
     * Activity cards for support service.
     */
    #[Computed]
    public function supportActivityCards(): array
    {
        $activity = $this->supportTodaysActivity;

        return [
            [
                'value' => number_format($activity['new_conversations']),
                'label' => __('hub::hub.services.support.new_today'),
                'icon' => 'plus-circle',
                'color' => 'violet',
            ],
            [
                'value' => number_format($activity['resolved_today']),
                'label' => __('hub::hub.services.support.resolved_today'),
                'icon' => 'check-circle',
                'color' => 'green',
            ],
            [
                'value' => number_format($activity['messages_sent']),
                'label' => __('hub::hub.services.support.messages_sent'),
                'icon' => 'paper-airplane',
                'color' => 'blue',
            ],
        ];
    }

    /**
     * Performance cards for support service.
     */
    #[Computed]
    public function supportPerformanceCards(): array
    {
        $performance = $this->supportPerformance;

        return [
            [
                'value' => $performance['first_response_time'] ?? __('hub::hub.services.support.na'),
                'label' => __('hub::hub.services.support.first_response'),
                'icon' => 'bolt',
                'color' => 'amber',
            ],
            [
                'value' => $performance['resolution_time'] ?? __('hub::hub.services.support.na'),
                'label' => __('hub::hub.services.support.resolution_time'),
                'icon' => 'flag',
                'color' => 'teal',
            ],
        ];
    }

    /**
     * Recent conversations for support service.
     */
    #[Computed]
    public function supportRecentConversations(): \Illuminate\Support\Collection
    {
        $workspaceId = $this->workspace?->id;

        if (! $workspaceId) {
            return collect();
        }

        $mailboxIds = Mailbox::where('workspace_id', $workspaceId)->pluck('id');

        return Conversation::with(['mailbox', 'customer', 'latestThread'])
            ->whereIn('mailbox_id', $mailboxIds)
            ->latest()
            ->take(5)
            ->get();
    }

    /**
     * Calculate average response time for support conversations.
     */
    private function calculateSupportAvgResponseTime(\Illuminate\Support\Collection $mailboxIds): ?string
    {
        $monthStart = now()->startOfMonth();

        $conversations = Conversation::query()
            ->whereIn('mailbox_id', $mailboxIds)
            ->where('created_at', '>=', $monthStart)
            ->whereHas('threads', function ($query) {
                $query->where('type', 'message');
            })
            ->with(['threads' => function ($query) {
                $query->orderBy('created_at');
            }])
            ->get();

        if ($conversations->isEmpty()) {
            return null;
        }

        $totalSeconds = 0;
        $count = 0;

        foreach ($conversations as $conversation) {
            $customerThread = $conversation->threads->firstWhere('type', 'customer');
            $agentThread = $conversation->threads->firstWhere('type', 'message');

            if ($customerThread && $agentThread && $agentThread->created_at > $customerThread->created_at) {
                $totalSeconds += $agentThread->created_at->diffInSeconds($customerThread->created_at);
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        return $this->formatSupportDuration((int) ($totalSeconds / $count));
    }

    /**
     * Calculate first response time for support conversations.
     */
    private function calculateSupportFirstResponseTime(\Illuminate\Support\Collection $mailboxIds): ?string
    {
        $monthStart = now()->startOfMonth();

        $conversations = Conversation::query()
            ->whereIn('mailbox_id', $mailboxIds)
            ->where('created_at', '>=', $monthStart)
            ->whereHas('threads', function ($query) {
                $query->where('type', 'message');
            })
            ->get();

        if ($conversations->isEmpty()) {
            return null;
        }

        $totalSeconds = 0;
        $count = 0;

        foreach ($conversations as $conversation) {
            $firstAgentReply = Thread::where('conversation_id', $conversation->id)
                ->where('type', 'message')
                ->orderBy('created_at')
                ->first();

            if ($firstAgentReply) {
                $totalSeconds += $firstAgentReply->created_at->diffInSeconds($conversation->created_at);
                $count++;
            }
        }

        if ($count === 0) {
            return null;
        }

        return $this->formatSupportDuration((int) ($totalSeconds / $count));
    }

    /**
     * Calculate resolution time for support conversations.
     */
    private function calculateSupportResolutionTime(\Illuminate\Support\Collection $mailboxIds): ?string
    {
        $monthStart = now()->startOfMonth();

        $conversations = Conversation::query()
            ->whereIn('mailbox_id', $mailboxIds)
            ->where('status', 'closed')
            ->where('closed_at', '>=', $monthStart)
            ->whereNotNull('closed_at')
            ->get();

        if ($conversations->isEmpty()) {
            return null;
        }

        $totalSeconds = 0;
        $count = 0;

        foreach ($conversations as $conversation) {
            $totalSeconds += $conversation->closed_at->diffInSeconds($conversation->created_at);
            $count++;
        }

        if ($count === 0) {
            return null;
        }

        return $this->formatSupportDuration((int) ($totalSeconds / $count));
    }

    /**
     * Format seconds into human-readable duration for support metrics.
     */
    private function formatSupportDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.'s';
        }

        if ($seconds < 3600) {
            $minutes = (int) ($seconds / 60);

            return $minutes.'m';
        }

        if ($seconds < 86400) {
            $hours = (int) ($seconds / 3600);
            $minutes = (int) (($seconds % 3600) / 60);

            return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
        }

        $days = (int) ($seconds / 86400);
        $hours = (int) (($seconds % 86400) / 3600);

        return $hours > 0 ? "{$days}d {$hours}h" : "{$days}d";
    }

    /**
     * Get status color for support conversations.
     */
    public function supportStatusColor(string $status): string
    {
        return match ($status) {
            'active' => 'green',
            'pending' => 'yellow',
            'closed' => 'zinc',
            'spam' => 'red',
            default => 'zinc',
        };
    }

    public function render(): View
    {
        return view('hub::admin.services-admin')
            ->layout('hub::admin.layouts.app', ['title' => $this->currentServiceItem['label'] ?? 'Services']);
    }
}
