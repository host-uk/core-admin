<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin\Entitlement;

use Core\Mod\Tenant\Models\Feature;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Features')]
#[Layout('hub::admin.layouts.app')]
class FeatureManager extends Component
{
    use WithPagination;

    public bool $showModal = false;

    /**
     * Authorize access - Hades tier only.
     */
    public function mount(): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required for feature management.');
        }
    }

    public ?int $editingId = null;

    // Form fields
    public string $code = '';

    public string $name = '';

    public string $description = '';

    public string $category = '';

    public string $type = 'boolean';

    public string $reset_type = 'none';

    public ?int $rolling_window_days = null;

    public ?int $parent_feature_id = null;

    public int $sort_order = 0;

    public bool $is_active = true;

    protected function rules(): array
    {
        $uniqueRule = $this->editingId
            ? 'unique:entitlement_features,code,'.$this->editingId
            : 'unique:entitlement_features,code';

        return [
            'code' => ['required', 'string', 'max:100', $uniqueRule],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'category' => ['nullable', 'string', 'max:50'],
            'type' => ['required', 'in:boolean,limit,unlimited'],
            'reset_type' => ['required', 'in:none,monthly,rolling'],
            'rolling_window_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'parent_feature_id' => ['nullable', 'exists:entitlement_features,id'],
            'sort_order' => ['integer'],
            'is_active' => ['boolean'],
        ];
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $feature = Feature::findOrFail($id);

        $this->editingId = $id;
        $this->code = $feature->code;
        $this->name = $feature->name;
        $this->description = $feature->description ?? '';
        $this->category = $feature->category ?? '';
        $this->type = $feature->type;
        $this->reset_type = $feature->reset_type;
        $this->rolling_window_days = $feature->rolling_window_days;
        $this->parent_feature_id = $feature->parent_feature_id;
        $this->sort_order = $feature->sort_order;
        $this->is_active = $feature->is_active;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description ?: null,
            'category' => $this->category ?: null,
            'type' => $this->type,
            'reset_type' => $this->reset_type,
            'rolling_window_days' => $this->reset_type === 'rolling' ? $this->rolling_window_days : null,
            'parent_feature_id' => $this->parent_feature_id ?: null,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];

        if ($this->editingId) {
            Feature::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Feature updated successfully.');
        } else {
            Feature::create($data);
            session()->flash('message', 'Feature created successfully.');
        }

        $this->closeModal();
    }

    public function delete(int $id): void
    {
        $feature = Feature::findOrFail($id);

        // Check if feature is used in any packages
        if ($feature->packages()->exists()) {
            session()->flash('error', 'Cannot delete feature that is assigned to packages.');

            return;
        }

        // Check if feature has children
        if ($feature->children()->exists()) {
            session()->flash('error', 'Cannot delete feature that has child features.');

            return;
        }

        $feature->delete();
        session()->flash('message', 'Feature deleted successfully.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->code = '';
        $this->name = '';
        $this->description = '';
        $this->category = '';
        $this->type = 'boolean';
        $this->reset_type = 'none';
        $this->rolling_window_days = null;
        $this->parent_feature_id = null;
        $this->sort_order = 0;
        $this->is_active = true;
    }

    #[Computed]
    public function features()
    {
        return Feature::with('parent')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->paginate(30);
    }

    #[Computed]
    public function categories()
    {
        return Feature::whereNotNull('category')
            ->distinct()
            ->pluck('category');
    }

    #[Computed]
    public function parentFeatures()
    {
        return Feature::root()
            ->where('type', 'limit')
            ->get();
    }

    #[Computed]
    public function tableColumns(): array
    {
        return [
            'Feature',
            'Code',
            'Category',
            ['label' => 'Type', 'align' => 'center'],
            ['label' => 'Reset', 'align' => 'center'],
            ['label' => 'Status', 'align' => 'center'],
            ['label' => 'Actions', 'align' => 'center'],
        ];
    }

    #[Computed]
    public function tableRows(): array
    {
        $typeColors = [
            'boolean' => 'gray',
            'limit' => 'blue',
            'unlimited' => 'purple',
        ];

        return $this->features->map(function ($f) use ($typeColors) {
            // Feature name with description and parent
            $featureLines = [['bold' => $f->name]];
            if ($f->description) {
                $featureLines[] = ['muted' => \Illuminate\Support\Str::limit($f->description, 40)];
            }
            if ($f->parent) {
                $featureLines[] = ['muted' => 'Parent: '.$f->parent->name];
            }

            // Reset type display
            $resetCell = match ($f->reset_type) {
                'none' => ['muted' => 'Never'],
                'monthly' => ['badge' => 'Monthly', 'color' => 'green'],
                'rolling' => ['badge' => $f->rolling_window_days.'d Rolling', 'color' => 'amber'],
                default => ['muted' => '-'],
            };

            return [
                ['lines' => $featureLines],
                ['mono' => $f->code],
                $f->category ? ['badge' => $f->category, 'color' => 'gray'] : ['muted' => '-'],
                ['badge' => ucfirst($f->type), 'color' => $typeColors[$f->type] ?? 'gray'],
                $resetCell,
                ['badge' => $f->is_active ? 'Active' : 'Inactive', 'color' => $f->is_active ? 'green' : 'gray'],
                [
                    'actions' => [
                        ['icon' => 'pencil', 'click' => "openEdit({$f->id})", 'title' => 'Edit'],
                        ['icon' => 'trash', 'click' => "delete({$f->id})", 'confirm' => 'Are you sure you want to delete this feature?', 'title' => 'Delete', 'class' => 'text-red-600'],
                    ],
                ],
            ];
        })->all();
    }

    public function render()
    {
        return view('hub::admin.entitlement.feature-manager')
            ->layout('hub::admin.layouts.app', ['title' => 'Features']);
    }
}
