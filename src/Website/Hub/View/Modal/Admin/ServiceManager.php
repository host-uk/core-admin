<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Core\Mod\Hub\Database\Seeders\ServiceSeeder;
use Core\Mod\Hub\Models\Service;

#[Title('Services')]
#[Layout('hub::admin.layouts.app')]
class ServiceManager extends Component
{
    public bool $showModal = false;

    public ?int $editingId = null;

    // Editable form fields
    public string $name = '';

    public string $tagline = '';

    public string $description = '';

    public string $icon = '';

    public string $color = '';

    public string $marketing_domain = '';

    public string $marketing_url = '';

    public string $docs_url = '';

    public bool $is_enabled = true;

    public bool $is_public = true;

    public bool $is_featured = false;

    public int $sort_order = 50;

    // Read-only fields (displayed but not editable)
    public string $code = '';

    public string $module = '';

    public string $entitlement_code = '';

    /**
     * Authorize access - Hades tier only.
     */
    public function mount(): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades tier required for service management.');
        }
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'tagline' => ['nullable', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:2000'],
            'icon' => ['nullable', 'string', 'max:50'],
            'color' => ['nullable', 'string', 'max:20'],
            'marketing_domain' => ['nullable', 'string', 'max:100'],
            'marketing_url' => ['nullable', 'url', 'max:255'],
            'docs_url' => ['nullable', 'url', 'max:255'],
            'is_enabled' => ['boolean'],
            'is_public' => ['boolean'],
            'is_featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0', 'max:999'],
        ];
    }

    public function openEdit(int $id): void
    {
        $service = Service::findOrFail($id);

        $this->editingId = $id;

        // Read-only fields
        $this->code = $service->code;
        $this->module = $service->module;
        $this->entitlement_code = $service->entitlement_code ?? '';

        // Editable fields
        $this->name = $service->name;
        $this->tagline = $service->tagline ?? '';
        $this->description = $service->description ?? '';
        $this->icon = $service->icon ?? '';
        $this->color = $service->color ?? '';
        $this->marketing_domain = $service->marketing_domain ?? '';
        $this->marketing_url = $service->getRawOriginal('marketing_url') ?? '';
        $this->docs_url = $service->docs_url ?? '';
        $this->is_enabled = $service->is_enabled;
        $this->is_public = $service->is_public;
        $this->is_featured = $service->is_featured;
        $this->sort_order = $service->sort_order;

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $service = Service::findOrFail($this->editingId);

        $service->update([
            'name' => $this->name,
            'tagline' => $this->tagline ?: null,
            'description' => $this->description ?: null,
            'icon' => $this->icon ?: null,
            'color' => $this->color ?: null,
            'marketing_domain' => $this->marketing_domain ?: null,
            'marketing_url' => $this->marketing_url ?: null,
            'docs_url' => $this->docs_url ?: null,
            'is_enabled' => $this->is_enabled,
            'is_public' => $this->is_public,
            'is_featured' => $this->is_featured,
            'sort_order' => $this->sort_order,
        ]);

        session()->flash('message', 'Service updated successfully.');
        $this->closeModal();
    }

    public function toggleEnabled(int $id): void
    {
        $service = Service::findOrFail($id);
        $service->update(['is_enabled' => ! $service->is_enabled]);

        $status = $service->is_enabled ? 'enabled' : 'disabled';
        session()->flash('message', "{$service->name} has been {$status}.");
    }

    public function syncFromModules(): void
    {
        $seeder = new ServiceSeeder;
        $seeder->run();

        session()->flash('message', 'Services synced from modules successfully.');
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
        $this->module = '';
        $this->entitlement_code = '';
        $this->name = '';
        $this->tagline = '';
        $this->description = '';
        $this->icon = '';
        $this->color = '';
        $this->marketing_domain = '';
        $this->marketing_url = '';
        $this->docs_url = '';
        $this->is_enabled = true;
        $this->is_public = true;
        $this->is_featured = false;
        $this->sort_order = 50;
    }

    #[Computed]
    public function services()
    {
        return Service::ordered()->get();
    }

    #[Computed]
    public function tableColumns(): array
    {
        return [
            'Service',
            'Code',
            'Domain',
            ['label' => 'Entitlement', 'align' => 'center'],
            ['label' => 'Status', 'align' => 'center'],
            ['label' => 'Actions', 'align' => 'center'],
        ];
    }

    #[Computed]
    public function tableRows(): array
    {
        return $this->services->map(function ($s) {
            // Service name with icon and tagline
            $serviceLines = [['bold' => $s->name]];
            if ($s->tagline) {
                $serviceLines[] = ['muted' => \Illuminate\Support\Str::limit($s->tagline, 40)];
            }

            // Status badges
            $statusLines = [];
            $statusLines[] = ['badge' => $s->is_enabled ? 'Enabled' : 'Disabled', 'color' => $s->is_enabled ? 'green' : 'red'];
            if ($s->is_public) {
                $statusLines[] = ['badge' => 'Public', 'color' => 'sky'];
            }
            if ($s->is_featured) {
                $statusLines[] = ['badge' => 'Featured', 'color' => 'amber'];
            }

            return [
                [
                    'icon' => $s->icon,
                    'iconColor' => $s->color,
                    'lines' => $serviceLines,
                ],
                ['mono' => $s->code],
                $s->marketing_domain
                    ? ['link' => 'Open in Tab', 'href' => 'http://'.$s->marketing_domain, 'target' => '_blank']
                    : ['muted' => 'Not set'],
                $s->entitlement_code ? ['mono' => $s->entitlement_code] : ['muted' => '-'],
                ['lines' => $statusLines],
                [
                    'actions' => [
                        ['icon' => $s->is_enabled ? 'toggle-on' : 'toggle-off', 'click' => "toggleEnabled({$s->id})", 'title' => $s->is_enabled ? 'Disable' : 'Enable', 'class' => $s->is_enabled ? 'text-green-600' : 'text-gray-400'],
                        ['icon' => 'pencil', 'click' => "openEdit({$s->id})", 'title' => 'Edit'],
                    ],
                ],
            ];
        })->all();
    }

    public function render()
    {
        return view('hub::admin.service-manager')
            ->layout('hub::admin.layouts.app', ['title' => 'Services']);
    }
}
