<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin\Entitlement;

use Core\Mod\Tenant\Models\Feature;
use Core\Mod\Tenant\Models\Package;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Packages')]
#[Layout('hub::admin.layouts.app')]
class PackageManager extends Component
{
    use WithPagination;

    public bool $showModal = false;

    /**
     * Authorize access - Hades tier only.
     */
    public function mount(): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required for package management.');
        }
    }

    public bool $showFeaturesModal = false;

    public ?int $editingId = null;

    // Form fields
    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $icon = 'package';

    public string $color = 'blue';

    public int $sort_order = 0;

    public bool $is_stackable = true;

    public bool $is_base_package = false;

    public bool $is_active = true;

    public bool $is_public = true;

    public string $blesta_package_id = '';

    // Features assignment
    public array $selectedFeatures = [];

    protected function rules(): array
    {
        $uniqueRule = $this->editingId
            ? 'unique:entitlement_packages,code,'.$this->editingId
            : 'unique:entitlement_packages,code';

        return [
            'code' => ['required', 'string', 'max:50', $uniqueRule],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'sort_order' => ['integer'],
            'is_stackable' => ['boolean'],
            'is_base_package' => ['boolean'],
            'is_active' => ['boolean'],
            'is_public' => ['boolean'],
            'blesta_package_id' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $package = Package::findOrFail($id);

        $this->editingId = $id;
        $this->code = $package->code;
        $this->name = $package->name;
        $this->description = $package->description ?? '';
        $this->icon = $package->icon ?? 'package';
        $this->color = $package->color ?? 'blue';
        $this->sort_order = $package->sort_order;
        $this->is_stackable = $package->is_stackable;
        $this->is_base_package = $package->is_base_package;
        $this->is_active = $package->is_active;
        $this->is_public = $package->is_public;
        $this->blesta_package_id = $package->blesta_package_id ?? '';

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'icon' => $this->icon ?: null,
            'color' => $this->color ?: null,
            'sort_order' => $this->sort_order,
            'is_stackable' => $this->is_stackable,
            'is_base_package' => $this->is_base_package,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'blesta_package_id' => $this->blesta_package_id ?: null,
        ];

        if ($this->editingId) {
            Package::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Package updated successfully.');
        } else {
            Package::create($data);
            session()->flash('message', 'Package created successfully.');
        }

        $this->closeModal();
    }

    public function openFeatures(int $id): void
    {
        $this->editingId = $id;
        $package = Package::with('features')->findOrFail($id);

        // Build selectedFeatures array with limit values
        $this->selectedFeatures = [];
        foreach ($package->features as $feature) {
            $this->selectedFeatures[$feature->id] = [
                'enabled' => true,
                'limit' => $feature->pivot->limit_value,
            ];
        }

        $this->showFeaturesModal = true;
    }

    public function saveFeatures(): void
    {
        $package = Package::findOrFail($this->editingId);

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

        session()->flash('message', 'Package features updated successfully.');
        $this->showFeaturesModal = false;
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

    public function delete(int $id): void
    {
        $package = Package::findOrFail($id);

        // Check if any workspaces use this package
        if ($package->workspacePackages()->exists()) {
            session()->flash('error', 'Cannot delete package with active assignments.');

            return;
        }

        $package->delete();
        session()->flash('message', 'Package deleted successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->showFeaturesModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->icon = 'package';
        $this->color = 'blue';
        $this->sort_order = 0;
        $this->is_stackable = true;
        $this->is_base_package = false;
        $this->is_active = true;
        $this->is_public = true;
        $this->blesta_package_id = '';
        $this->selectedFeatures = [];
    }

    #[Computed]
    public function packages()
    {
        return Package::withCount('features')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);
    }

    #[Computed]
    public function features()
    {
        return Feature::active()
            ->orderBy('category')
            ->orderBy('sort_order')
            ->get()
            ->groupBy('category');
    }

    #[Computed]
    public function tableColumns(): array
    {
        return [
            'Package',
            'Code',
            'Features',
            ['label' => 'Type', 'align' => 'center'],
            ['label' => 'Status', 'align' => 'center'],
            ['label' => 'Actions', 'align' => 'center'],
        ];
    }

    #[Computed]
    public function tableRows(): array
    {
        return $this->packages->map(function ($p) {
            // Package name with icon and description
            $packageLines = [['bold' => $p->name]];
            if ($p->description) {
                $packageLines[] = ['muted' => \Illuminate\Support\Str::limit($p->description, 50)];
            }

            // Type badge
            $typeBadge = match (true) {
                $p->is_base_package => ['badge' => 'Base', 'color' => 'purple'],
                $p->is_stackable => ['badge' => 'Addon', 'color' => 'blue'],
                default => ['badge' => 'Standard', 'color' => 'gray'],
            };

            // Status badges (multiple)
            $statusLines = [];
            $statusLines[] = ['badge' => $p->is_active ? 'Active' : 'Inactive', 'color' => $p->is_active ? 'green' : 'gray'];
            if ($p->is_public) {
                $statusLines[] = ['badge' => 'Public', 'color' => 'sky'];
            }

            return [
                ['lines' => $packageLines],
                ['mono' => $p->code],
                ['badge' => $p->features_count.' features', 'color' => 'gray'],
                $typeBadge,
                ['lines' => $statusLines],
                [
                    'actions' => [
                        ['icon' => 'puzzle-piece', 'click' => "openFeatures({$p->id})", 'title' => 'Assign features'],
                        ['icon' => 'pencil', 'click' => "openEdit({$p->id})", 'title' => 'Edit'],
                        ['icon' => 'trash', 'click' => "delete({$p->id})", 'confirm' => 'Are you sure you want to delete this package?', 'title' => 'Delete', 'class' => 'text-red-600'],
                    ],
                ],
            ];
        })->all();
    }

    public function render()
    {
        return view('hub::admin.entitlement.package-manager')
            ->layout('hub::admin.layouts.app', ['title' => 'Packages']);
    }
}
