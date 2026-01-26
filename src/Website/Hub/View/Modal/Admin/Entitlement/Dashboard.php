<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin\Entitlement;

use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;
use Core\Mod\Tenant\Models\Boost;
use Core\Mod\Tenant\Models\Feature;
use Core\Mod\Tenant\Models\Package;
use Core\Mod\Tenant\Models\WorkspacePackage;

#[Title('Entitlements')]
#[Layout('hub::admin.layouts.app')]
class Dashboard extends Component
{
    use WithPagination;

    #[Url]
    public string $tab = 'overview';

    // Package form
    public bool $showPackageModal = false;

    public ?int $editingPackageId = null;

    public string $packageCode = '';

    public string $packageName = '';

    public string $packageDescription = '';

    public string $packageIcon = 'box';

    public string $packageColor = 'blue';

    public int $packageSortOrder = 0;

    public bool $packageIsStackable = true;

    public bool $packageIsBasePackage = false;

    public bool $packageIsActive = true;

    public bool $packageIsPublic = true;

    // Feature form
    public bool $showFeatureModal = false;

    public ?int $editingFeatureId = null;

    public string $featureCode = '';

    public string $featureName = '';

    public string $featureDescription = '';

    public string $featureCategory = '';

    public string $featureType = 'boolean';

    public string $featureResetType = 'none';

    public ?int $featureRollingDays = null;

    public ?int $featureParentId = null;

    public int $featureSortOrder = 0;

    public bool $featureIsActive = true;

    // Features assignment
    public bool $showFeaturesModal = false;

    public array $selectedFeatures = [];

    public function mount(?string $tab = null): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required for entitlement management.');
        }

        if ($tab && in_array($tab, ['overview', 'packages', 'features'])) {
            $this->tab = $tab;
        }
    }

    public function setTab(string $tab): void
    {
        if (in_array($tab, ['overview', 'packages', 'features'])) {
            $this->tab = $tab;
            $this->resetPage();
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Overview Stats
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function stats(): array
    {
        return [
            'packages' => [
                'total' => Package::count(),
                'active' => Package::where('is_active', true)->count(),
                'public' => Package::where('is_public', true)->count(),
                'base' => Package::where('is_base_package', true)->count(),
            ],
            'features' => [
                'total' => Feature::count(),
                'active' => Feature::where('is_active', true)->count(),
                'boolean' => Feature::where('type', 'boolean')->count(),
                'limit' => Feature::where('type', 'limit')->count(),
            ],
            'assignments' => [
                'workspace_packages' => WorkspacePackage::where('status', 'active')->count(),
                'active_boosts' => Boost::where('status', 'active')->count(),
            ],
            'categories' => Feature::whereNotNull('category')
                ->distinct()
                ->pluck('category')
                ->toArray(),
        ];
    }

    // ─────────────────────────────────────────────────────────────
    // Packages
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function packages()
    {
        return Package::withCount('features')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(15);
    }

    public function openCreatePackage(): void
    {
        $this->resetPackageForm();
        $this->showPackageModal = true;
    }

    public function openEditPackage(int $id): void
    {
        $package = Package::findOrFail($id);

        $this->editingPackageId = $id;
        $this->packageCode = $package->code;
        $this->packageName = $package->name;
        $this->packageDescription = $package->description ?? '';
        $this->packageIcon = $package->icon ?? 'box';
        $this->packageColor = $package->color ?? 'blue';
        $this->packageSortOrder = $package->sort_order;
        $this->packageIsStackable = $package->is_stackable;
        $this->packageIsBasePackage = $package->is_base_package;
        $this->packageIsActive = $package->is_active;
        $this->packageIsPublic = $package->is_public;

        $this->showPackageModal = true;
    }

    public function savePackage(): void
    {
        $this->validate([
            'packageCode' => ['required', 'string', 'max:50', $this->editingPackageId
                ? 'unique:entitlement_packages,code,'.$this->editingPackageId
                : 'unique:entitlement_packages,code'],
            'packageName' => ['required', 'string', 'max:100'],
            'packageDescription' => ['nullable', 'string', 'max:500'],
        ]);

        $data = [
            'code' => $this->packageCode,
            'name' => $this->packageName,
            'description' => $this->packageDescription ?: null,
            'icon' => $this->packageIcon ?: null,
            'color' => $this->packageColor ?: null,
            'sort_order' => $this->packageSortOrder,
            'is_stackable' => $this->packageIsStackable,
            'is_base_package' => $this->packageIsBasePackage,
            'is_active' => $this->packageIsActive,
            'is_public' => $this->packageIsPublic,
        ];

        if ($this->editingPackageId) {
            Package::findOrFail($this->editingPackageId)->update($data);
            session()->flash('success', 'Package updated.');
        } else {
            Package::create($data);
            session()->flash('success', 'Package created.');
        }

        $this->closePackageModal();
        unset($this->packages);
        unset($this->stats);
    }

    public function deletePackage(int $id): void
    {
        $package = Package::findOrFail($id);

        if ($package->workspacePackages()->exists()) {
            session()->flash('error', 'Cannot delete package with active assignments.');

            return;
        }

        $package->delete();
        session()->flash('success', 'Package deleted.');
        unset($this->packages);
        unset($this->stats);
    }

    public function openAssignFeatures(int $id): void
    {
        $this->editingPackageId = $id;
        $package = Package::with('features')->findOrFail($id);

        $this->selectedFeatures = [];
        foreach ($package->features as $feature) {
            $this->selectedFeatures[$feature->id] = [
                'enabled' => true,
                'limit' => $feature->pivot->limit_value,
            ];
        }

        $this->showFeaturesModal = true;
    }

    public function toggleFeature(int $featureId): void
    {
        if (isset($this->selectedFeatures[$featureId])) {
            $this->selectedFeatures[$featureId]['enabled'] = ! $this->selectedFeatures[$featureId]['enabled'];
        } else {
            $this->selectedFeatures[$featureId] = [
                'enabled' => true,
                'limit' => null,
            ];
        }
    }

    public function saveFeatures(): void
    {
        $package = Package::findOrFail($this->editingPackageId);

        $syncData = [];
        foreach ($this->selectedFeatures as $featureId => $config) {
            if (! empty($config['enabled'])) {
                $syncData[$featureId] = [
                    'limit_value' => isset($config['limit']) && $config['limit'] !== ''
                        ? (int) $config['limit']
                        : null,
                ];
            }
        }

        $package->features()->sync($syncData);

        session()->flash('success', 'Package features updated.');
        $this->showFeaturesModal = false;
        unset($this->packages);
    }

    public function closePackageModal(): void
    {
        $this->showPackageModal = false;
        $this->resetPackageForm();
    }

    protected function resetPackageForm(): void
    {
        $this->editingPackageId = null;
        $this->packageCode = '';
        $this->packageName = '';
        $this->packageDescription = '';
        $this->packageIcon = 'box';
        $this->packageColor = 'blue';
        $this->packageSortOrder = 0;
        $this->packageIsStackable = true;
        $this->packageIsBasePackage = false;
        $this->packageIsActive = true;
        $this->packageIsPublic = true;
    }

    // ─────────────────────────────────────────────────────────────
    // Features
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function features()
    {
        return Feature::with('parent')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->paginate(20);
    }

    #[Computed]
    public function allFeatures()
    {
        return Feature::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
    }

    #[Computed]
    public function parentFeatures()
    {
        return Feature::root()
            ->where('type', 'limit')
            ->get();
    }

    #[Computed]
    public function featureCategories()
    {
        return Feature::whereNotNull('category')
            ->distinct()
            ->pluck('category');
    }

    public function openCreateFeature(): void
    {
        $this->resetFeatureForm();
        $this->showFeatureModal = true;
    }

    public function openEditFeature(int $id): void
    {
        $feature = Feature::findOrFail($id);

        $this->editingFeatureId = $id;
        $this->featureCode = $feature->code;
        $this->featureName = $feature->name;
        $this->featureDescription = $feature->description ?? '';
        $this->featureCategory = $feature->category ?? '';
        $this->featureType = $feature->type;
        $this->featureResetType = $feature->reset_type;
        $this->featureRollingDays = $feature->rolling_window_days;
        $this->featureParentId = $feature->parent_feature_id;
        $this->featureSortOrder = $feature->sort_order;
        $this->featureIsActive = $feature->is_active;

        $this->showFeatureModal = true;
    }

    public function saveFeature(): void
    {
        $this->validate([
            'featureCode' => ['required', 'string', 'max:100', $this->editingFeatureId
                ? 'unique:entitlement_features,code,'.$this->editingFeatureId
                : 'unique:entitlement_features,code'],
            'featureName' => ['required', 'string', 'max:100'],
            'featureDescription' => ['nullable', 'string', 'max:500'],
            'featureCategory' => ['nullable', 'string', 'max:50'],
            'featureType' => ['required', 'in:boolean,limit,unlimited'],
            'featureResetType' => ['required', 'in:none,monthly,rolling'],
        ]);

        $data = [
            'code' => $this->featureCode,
            'name' => $this->featureName,
            'description' => $this->featureDescription ?: null,
            'category' => $this->featureCategory ?: null,
            'type' => $this->featureType,
            'reset_type' => $this->featureResetType,
            'rolling_window_days' => $this->featureResetType === 'rolling' ? $this->featureRollingDays : null,
            'parent_feature_id' => $this->featureParentId ?: null,
            'sort_order' => $this->featureSortOrder,
            'is_active' => $this->featureIsActive,
        ];

        if ($this->editingFeatureId) {
            Feature::findOrFail($this->editingFeatureId)->update($data);
            session()->flash('success', 'Feature updated.');
        } else {
            Feature::create($data);
            session()->flash('success', 'Feature created.');
        }

        $this->closeFeatureModal();
        unset($this->features);
        unset($this->allFeatures);
        unset($this->stats);
    }

    public function deleteFeature(int $id): void
    {
        $feature = Feature::findOrFail($id);

        if ($feature->packages()->exists()) {
            session()->flash('error', 'Cannot delete feature assigned to packages.');

            return;
        }

        if ($feature->children()->exists()) {
            session()->flash('error', 'Cannot delete feature with children.');

            return;
        }

        $feature->delete();
        session()->flash('success', 'Feature deleted.');
        unset($this->features);
        unset($this->allFeatures);
        unset($this->stats);
    }

    public function closeFeatureModal(): void
    {
        $this->showFeatureModal = false;
        $this->resetFeatureForm();
    }

    protected function resetFeatureForm(): void
    {
        $this->editingFeatureId = null;
        $this->featureCode = '';
        $this->featureName = '';
        $this->featureDescription = '';
        $this->featureCategory = '';
        $this->featureType = 'boolean';
        $this->featureResetType = 'none';
        $this->featureRollingDays = null;
        $this->featureParentId = null;
        $this->featureSortOrder = 0;
        $this->featureIsActive = true;
    }

    // ─────────────────────────────────────────────────────────────
    // Table Helpers
    // ─────────────────────────────────────────────────────────────

    #[Computed]
    public function packageTableRows(): array
    {
        return $this->packages->map(function ($p) {
            $lines = [['bold' => $p->name]];
            if ($p->description) {
                $lines[] = ['muted' => Str::limit($p->description, 50)];
            }

            $typeBadge = match (true) {
                $p->is_base_package => ['badge' => 'Base', 'color' => 'purple'],
                $p->is_stackable => ['badge' => 'Addon', 'color' => 'blue'],
                default => ['badge' => 'Standard', 'color' => 'gray'],
            };

            $statusLines = [];
            $statusLines[] = ['badge' => $p->is_active ? 'Active' : 'Inactive', 'color' => $p->is_active ? 'green' : 'gray'];
            if ($p->is_public) {
                $statusLines[] = ['badge' => 'Public', 'color' => 'sky'];
            }

            return [
                [
                    'icon' => $p->icon ?? 'box',
                    'iconColor' => $p->color ?? 'gray',
                    'lines' => $lines,
                ],
                ['mono' => $p->code],
                ['badge' => $p->features_count.' features', 'color' => 'gray'],
                $typeBadge,
                ['lines' => $statusLines],
                [
                    'actions' => [
                        ['icon' => 'puzzle-piece', 'click' => "openAssignFeatures({$p->id})", 'title' => 'Assign features'],
                        ['icon' => 'pencil', 'click' => "openEditPackage({$p->id})", 'title' => 'Edit'],
                        ['icon' => 'trash', 'click' => "deletePackage({$p->id})", 'confirm' => 'Delete this package?', 'title' => 'Delete', 'class' => 'text-red-600'],
                    ],
                ],
            ];
        })->all();
    }

    #[Computed]
    public function featureTableRows(): array
    {
        $typeColors = [
            'boolean' => 'gray',
            'limit' => 'blue',
            'unlimited' => 'purple',
        ];

        return $this->features->map(function ($f) use ($typeColors) {
            $lines = [['bold' => $f->name]];
            if ($f->description) {
                $lines[] = ['muted' => Str::limit($f->description, 40)];
            }
            if ($f->parent) {
                $lines[] = ['muted' => 'Pool: '.$f->parent->name];
            }

            $resetCell = match ($f->reset_type) {
                'none' => ['muted' => 'Never'],
                'monthly' => ['badge' => 'Monthly', 'color' => 'green'],
                'rolling' => ['badge' => $f->rolling_window_days.'d', 'color' => 'amber'],
                default => ['muted' => '-'],
            };

            return [
                ['lines' => $lines],
                ['mono' => $f->code],
                $f->category ? ['badge' => $f->category, 'color' => 'gray'] : ['muted' => '-'],
                ['badge' => ucfirst($f->type), 'color' => $typeColors[$f->type] ?? 'gray'],
                $resetCell,
                ['badge' => $f->is_active ? 'Active' : 'Inactive', 'color' => $f->is_active ? 'green' : 'gray'],
                [
                    'actions' => [
                        ['icon' => 'pencil', 'click' => "openEditFeature({$f->id})", 'title' => 'Edit'],
                        ['icon' => 'trash', 'click' => "deleteFeature({$f->id})", 'confirm' => 'Delete this feature?', 'title' => 'Delete', 'class' => 'text-red-600'],
                    ],
                ],
            ];
        })->all();
    }

    public function render()
    {
        return view('hub::admin.entitlement.dashboard');
    }
}
