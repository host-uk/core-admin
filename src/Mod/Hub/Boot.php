<?php

declare(strict_types=1);

namespace Core\Mod\Hub;

use Core\Events\AdminPanelBooting;
use Core\Front\Admin\AdminMenuRegistry;
use Core\Front\Admin\Concerns\HasMenuPermissions;
use Core\Front\Admin\Contracts\AdminMenuProvider;
use Illuminate\Support\ServiceProvider;
use Core\Mod\Tenant\Services\WorkspaceService;

class Boot extends ServiceProvider implements AdminMenuProvider
{
    use HasMenuPermissions;

    protected string $moduleName = 'hub';

    /**
     * Events this module listens to for lazy loading.
     *
     * @var array<class-string, string>
     */
    public static array $listens = [
        AdminPanelBooting::class => 'onAdminPanel',
    ];

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/Migrations');
        $this->loadTranslationsFrom(__DIR__.'/Lang', 'hub');

        app(AdminMenuRegistry::class)->register($this);
    }

    /**
     * Admin menu items for Hub (platform base items).
     */
    public function adminMenuItems(): array
    {
        return [
            // Dashboard
            [
                'group' => 'dashboard',
                'priority' => 0,
                'item' => fn () => [
                    'label' => 'Dashboard',
                    'href' => route('hub.dashboard'),
                    'icon' => 'gauge',
                    'color' => 'indigo',
                    'active' => request()->routeIs('hub.dashboard'),
                ],
            ],
            // Workspaces - Overview
            [
                'group' => 'workspaces',
                'priority' => 10,
                'item' => fn () => [
                    'label' => 'Overview',
                    'href' => route('hub.sites'),
                    'icon' => 'layer-group',
                    'color' => 'blue',
                    'active' => request()->routeIs('hub.sites') || request()->routeIs('hub.sites.settings'),
                ],
            ],
            // Workspaces - Content
            [
                'group' => 'workspaces',
                'priority' => 20,
                'item' => fn () => [
                    'label' => 'Content',
                    'href' => route('hub.content-manager', ['workspace' => app(WorkspaceService::class)->currentSlug()]),
                    'icon' => 'file-lines',
                    'color' => 'emerald',
                    'active' => request()->routeIs('hub.content-manager') || request()->routeIs('hub.content-editor*'),
                ],
            ],
            // Workspaces - Configuration
            [
                'group' => 'workspaces',
                'priority' => 30,
                'item' => fn () => [
                    'label' => 'Configuration',
                    'href' => '/hub/config',
                    'icon' => 'sliders',
                    'color' => 'slate',
                    'active' => request()->is('hub/config*'),
                ],
            ],
            // Account - Profile
            [
                'group' => 'settings',
                'priority' => 10,
                'item' => fn () => [
                    'label' => 'Profile',
                    'href' => route('hub.account'),
                    'icon' => 'user',
                    'color' => 'sky',
                    'active' => request()->routeIs('hub.account') && ! request()->routeIs('hub.account.*'),
                ],
            ],
            // Account - Settings
            [
                'group' => 'settings',
                'priority' => 20,
                'item' => fn () => [
                    'label' => 'Settings',
                    'href' => route('hub.account.settings'),
                    'icon' => 'gear',
                    'color' => 'zinc',
                    'active' => request()->routeIs('hub.account.settings*'),
                ],
            ],
            // Account - Usage (consolidated: usage overview, boosts, AI services)
            [
                'group' => 'settings',
                'priority' => 30,
                'item' => fn () => [
                    'label' => 'Usage',
                    'href' => route('hub.account.usage'),
                    'icon' => 'chart-pie',
                    'color' => 'amber',
                    'active' => request()->routeIs('hub.account.usage'),
                ],
            ],
            // Admin - Platform
            [
                'group' => 'admin',
                'priority' => 10,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Platform',
                    'href' => route('hub.platform'),
                    'icon' => 'crown',
                    'color' => 'amber',
                    'active' => request()->routeIs('hub.platform*'),
                ],
            ],
            // Admin - Entitlements
            [
                'group' => 'admin',
                'priority' => 11,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Entitlements',
                    'href' => route('hub.entitlements'),
                    'icon' => 'key',
                    'color' => 'violet',
                    'active' => request()->routeIs('hub.entitlements*'),
                ],
            ],
            // Admin - Services
            [
                'group' => 'admin',
                'priority' => 13,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Services',
                    'href' => route('hub.admin.services'),
                    'icon' => 'cubes',
                    'color' => 'indigo',
                    'active' => request()->routeIs('hub.admin.services'),
                ],
            ],
            // Admin - Infrastructure
            [
                'group' => 'admin',
                'priority' => 60,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Infrastructure',
                    'icon' => 'server',
                    'color' => 'slate',
                    'active' => request()->routeIs('hub.console*') || request()->routeIs('hub.databases*') || request()->routeIs('hub.deployments*') || request()->routeIs('hub.honeypot'),
                    'children' => [
                        ['label' => 'Console', 'icon' => 'terminal', 'href' => route('hub.console'), 'active' => request()->routeIs('hub.console*')],
                        ['label' => 'Databases', 'icon' => 'database', 'href' => route('hub.databases'), 'active' => request()->routeIs('hub.databases*')],
                        ['label' => 'Deployments', 'icon' => 'rocket', 'href' => route('hub.deployments'), 'active' => request()->routeIs('hub.deployments*')],
                        ['label' => 'Honeypot', 'icon' => 'bug', 'href' => route('hub.honeypot'), 'active' => request()->routeIs('hub.honeypot')],
                    ],
                ],
            ],
            // Admin - Config
            [
                'group' => 'admin',
                'priority' => 85,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Config',
                    'href' => route('admin.config'),
                    'icon' => 'sliders',
                    'color' => 'zinc',
                    'active' => request()->routeIs('admin.config'),
                ],
            ],
            // Admin - Workspaces
            [
                'group' => 'admin',
                'priority' => 15,
                'admin' => true,
                'item' => fn () => [
                    'label' => 'Workspaces',
                    'href' => route('hub.admin.workspaces'),
                    'icon' => 'layer-group',
                    'color' => 'blue',
                    'active' => request()->routeIs('hub.admin.workspaces'),
                ],
            ],
        ];
    }

    public function register(): void
    {
        //
    }

    // -------------------------------------------------------------------------
    // Event-driven handlers
    // -------------------------------------------------------------------------

    public function onAdminPanel(AdminPanelBooting $event): void
    {
        $event->views($this->moduleName, __DIR__.'/View/Blade');

        if (file_exists(__DIR__.'/Routes/admin.php')) {
            $event->routes(fn () => require __DIR__.'/Routes/admin.php');
        }

        // Core admin components
        $event->livewire('hub.admin.dashboard', View\Modal\Admin\Dashboard::class);
        $event->livewire('hub.admin.content', View\Modal\Admin\Content::class);
        $event->livewire('hub.admin.content-manager', View\Modal\Admin\ContentManager::class);
        $event->livewire('hub.admin.content-editor', View\Modal\Admin\ContentEditor::class);
        $event->livewire('hub.admin.sites', View\Modal\Admin\Sites::class);
        $event->livewire('hub.admin.console', View\Modal\Admin\Console::class);
        $event->livewire('hub.admin.databases', View\Modal\Admin\Databases::class);
        $event->livewire('hub.admin.profile', View\Modal\Admin\Profile::class);
        $event->livewire('hub.admin.settings', View\Modal\Admin\Settings::class);
        $event->livewire('hub.admin.account-usage', View\Modal\Admin\AccountUsage::class);
        $event->livewire('hub.admin.site-settings', View\Modal\Admin\SiteSettings::class);
        $event->livewire('hub.admin.deployments', View\Modal\Admin\Deployments::class);
        $event->livewire('hub.admin.platform', View\Modal\Admin\Platform::class);
        $event->livewire('hub.admin.platform-user', View\Modal\Admin\PlatformUser::class);
        $event->livewire('hub.admin.prompt-manager', View\Modal\Admin\PromptManager::class);
        $event->livewire('hub.admin.waitlist-manager', View\Modal\Admin\WaitlistManager::class);
        $event->livewire('hub.admin.workspace-switcher', View\Modal\Admin\WorkspaceSwitcher::class);
        $event->livewire('hub.admin.wp-connector-settings', View\Modal\Admin\WpConnectorSettings::class);
        $event->livewire('hub.admin.services-admin', View\Modal\Admin\ServicesAdmin::class);
        $event->livewire('hub.admin.service-manager', View\Modal\Admin\ServiceManager::class);

        // Entitlement
        $event->livewire('hub.admin.entitlement.dashboard', View\Modal\Admin\Entitlement\Dashboard::class);
        $event->livewire('hub.admin.entitlement.feature-manager', View\Modal\Admin\Entitlement\FeatureManager::class);
        $event->livewire('hub.admin.entitlement.package-manager', View\Modal\Admin\Entitlement\PackageManager::class);

        // Global UI components
        $event->livewire('hub.admin.global-search', View\Modal\Admin\GlobalSearch::class);
        $event->livewire('hub.admin.activity-log', View\Modal\Admin\ActivityLog::class);

        // Security
        $event->livewire('hub.admin.honeypot', View\Modal\Admin\Honeypot::class);

        // Workspace management (Tenant module)
        $event->livewire('tenant.admin.workspace-manager', \Core\Mod\Tenant\View\Modal\Admin\WorkspaceManager::class);
        $event->livewire('tenant.admin.workspace-details', \Core\Mod\Tenant\View\Modal\Admin\WorkspaceDetails::class);
    }
}
