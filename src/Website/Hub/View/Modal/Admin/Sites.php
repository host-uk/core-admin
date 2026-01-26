<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;
use Core\Mod\Tenant\Models\Feature;
use Core\Mod\Tenant\Models\Package;
use Core\Mod\Tenant\Models\Workspace;
use Core\Mod\Tenant\Services\EntitlementService;
use Core\Mod\Tenant\Services\WorkspaceService;

/**
 * Workspace settings page at /hub/workspaces.
 *
 * Shows settings for the currently selected workspace (from switcher).
 * The workspace switcher in the header handles workspace selection.
 */
#[Title('Workspace Settings')]
#[Layout('hub::admin.layouts.app')]
class Sites extends Component
{
    public string $tab = 'services';

    protected WorkspaceService $workspaceService;

    protected EntitlementService $entitlements;

    public function boot(WorkspaceService $workspaceService, EntitlementService $entitlements): void
    {
        $this->workspaceService = $workspaceService;
        $this->entitlements = $entitlements;
    }

    #[Computed]
    public function workspace(): ?Workspace
    {
        return $this->workspaceService->currentModel();
    }

    #[Computed]
    public function workspaceSlug(): string
    {
        return $this->workspace?->slug ?? '';
    }

    #[On('workspace-changed')]
    public function refreshWorkspace(): void
    {
        unset($this->workspace);
        unset($this->workspaceSlug);
        unset($this->serviceCards);
        unset($this->tabs);
    }

    #[Computed]
    public function tabs(): array
    {
        return [
            'services' => [
                'label' => 'Services',
                'icon' => 'puzzle-piece',
                'href' => route('hub.sites').'?tab=services',
            ],
            'general' => [
                'label' => 'General',
                'icon' => 'gear',
                'href' => route('hub.sites').'?tab=general',
            ],
            'deployment' => [
                'label' => 'Deployment',
                'icon' => 'rocket',
                'href' => route('hub.sites').'?tab=deployment',
            ],
            'environment' => [
                'label' => 'Environment',
                'icon' => 'key',
                'href' => route('hub.sites').'?tab=environment',
            ],
            'ssl' => [
                'label' => 'SSL & Security',
                'icon' => 'shield-check',
                'href' => route('hub.sites').'?tab=ssl',
            ],
            'backups' => [
                'label' => 'Backups',
                'icon' => 'cloud-arrow-up',
                'href' => route('hub.sites').'?tab=backups',
            ],
            'danger' => [
                'label' => 'Danger Zone',
                'icon' => 'triangle-exclamation',
                'href' => route('hub.sites').'?tab=danger',
            ],
        ];
    }

    #[Computed]
    public function serviceCards(): array
    {
        $workspace = $this->workspace;

        $services = [
            [
                'name' => 'Bio',
                'description' => 'Bio pages, short links & QR codes',
                'icon' => 'link',
                'color' => 'violet',
                'slug' => 'bio',
                'feature' => 'core.srv.bio',
                'adminRoute' => route('hub.services', ['service' => 'bio']),
                'features' => [
                    'Unlimited bio pages',
                    'Custom domains',
                    'Link analytics',
                    'QR code generation',
                ],
            ],
            [
                'name' => 'Social',
                'description' => 'Social media scheduling & management',
                'icon' => 'share-nodes',
                'color' => 'blue',
                'slug' => 'social',
                'feature' => 'core.srv.social',
                'adminRoute' => route('hub.services', ['service' => 'social']),
                'features' => [
                    'Multi-platform posting',
                    'Content calendar',
                    'Team approvals',
                    'Analytics & insights',
                ],
            ],
            [
                'name' => 'Analytics',
                'description' => 'Privacy-focused website analytics',
                'icon' => 'chart-line',
                'color' => 'cyan',
                'slug' => 'analytics',
                'feature' => 'core.srv.analytics',
                'adminRoute' => route('hub.services', ['service' => 'analytics']),
                'features' => [
                    'Real-time visitors',
                    'Goal tracking',
                    'Heatmaps',
                    'Session replays',
                ],
            ],
            [
                'name' => 'Trust',
                'description' => 'Social proof & conversion widgets',
                'icon' => 'shield-check',
                'color' => 'orange',
                'slug' => 'trust',
                'feature' => 'core.srv.trust',
                'adminRoute' => route('hub.services', ['service' => 'trust']),
                'features' => [
                    'Purchase notifications',
                    'Review widgets',
                    'Visitor counts',
                    'Custom campaigns',
                ],
            ],
            [
                'name' => 'Notify',
                'description' => 'Push notifications & campaigns',
                'icon' => 'bell',
                'color' => 'yellow',
                'slug' => 'notify',
                'feature' => 'core.srv.notify',
                'adminRoute' => route('hub.services', ['service' => 'notify']),
                'features' => [
                    'Browser push notifications',
                    'Subscriber management',
                    'Campaign scheduling',
                    'Delivery analytics',
                ],
            ],
            [
                'name' => 'Support',
                'description' => 'Help desk & live chat',
                'icon' => 'headset',
                'color' => 'teal',
                'slug' => 'support',
                'feature' => 'core.srv.support',
                'adminRoute' => route('hub.support.inbox'),
                'features' => [
                    'Email ticketing',
                    'Live chat widget',
                    'Knowledge base',
                    'Team collaboration',
                ],
            ],
        ];

        return collect($services)->map(function ($service) use ($workspace) {
            $service['entitled'] = $workspace
                ? $this->entitlements->can($workspace, $service['feature'])->isAllowed()
                : false;

            return $service;
        })->all();
    }

    public function addService(string $featureCode): void
    {
        $workspace = $this->workspace;

        if (! $workspace) {
            session()->flash('error', 'No workspace found.');

            return;
        }

        $serviceCard = collect($this->serviceCards)->firstWhere('feature', $featureCode);

        if (! $serviceCard) {
            session()->flash('error', 'Service not found.');

            return;
        }

        $feature = Feature::firstOrCreate(
            ['code' => $featureCode],
            [
                'name' => $serviceCard['name'].' Access',
                'description' => "Access to {$serviceCard['name']}",
                'category' => 'service',
                'type' => Feature::TYPE_BOOLEAN,
                'reset_type' => Feature::RESET_NONE,
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        $packageCode = str_replace('.', '-', $featureCode).'-access';
        $package = Package::firstOrCreate(
            ['code' => $packageCode],
            [
                'name' => $feature->name,
                'description' => "Access to {$feature->name}",
                'is_stackable' => true,
                'is_base_package' => false,
                'is_active' => true,
                'is_public' => false,
                'sort_order' => 99,
            ]
        );

        if (! $package->features()->where('feature_id', $feature->id)->exists()) {
            $package->features()->attach($feature->id, ['limit_value' => null]);
        }

        $this->entitlements->provisionPackage($workspace, $packageCode, [
            'source' => 'user',
            'metadata' => ['added_via' => 'site_settings_page'],
        ]);

        Cache::flush();

        session()->flash('success', "{$feature->name} has been added to your site.");
    }

    public function switchTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs)) {
            $this->tab = $tab;
        }
    }

    public function render(): View
    {
        return view('hub::admin.site-settings');
    }
}
