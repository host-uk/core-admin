<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Mod\Agentic\Models\Prompt;
use Core\Mod\Agentic\Models\PromptVersion;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Prompt Manager')]
#[Layout('hub::admin.layouts.app')]
class PromptManager extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $model = '';

    public ?int $editingPromptId = null;

    // Form fields
    public string $name = '';

    public string $promptCategory = 'content';

    public string $description = '';

    public string $systemPrompt = '';

    public string $userTemplate = '';

    public array $variables = [];

    public string $promptModel = 'claude';

    public array $modelConfig = [];

    public bool $isActive = true;

    // Modal states
    public bool $showEditor = false;

    public bool $showVersions = false;

    public bool $showTestPanel = false;

    // Test panel
    public string $testOutput = '';

    public bool $testing = false;

    protected $queryString = ['search', 'category', 'model'];

    public function mount(): void
    {
        $this->modelConfig = [
            'temperature' => 1.0,
            'max_tokens' => 4096,
        ];
    }

    #[Computed]
    public function prompts()
    {
        return Prompt::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->category, fn ($q) => $q->where('category', $this->category))
            ->when($this->model, fn ($q) => $q->where('model', $this->model))
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(20);
    }

    #[Computed]
    public function categories(): array
    {
        return Prompt::distinct()->pluck('category')->toArray();
    }

    #[Computed]
    public function models(): array
    {
        return ['claude', 'gemini'];
    }

    #[Computed]
    public function categoryOptions(): array
    {
        return collect($this->categories)
            ->mapWithKeys(fn ($cat) => [$cat => ucfirst($cat)])
            ->all();
    }

    #[Computed]
    public function modelOptions(): array
    {
        return [
            'claude' => 'Claude',
            'gemini' => 'Gemini',
        ];
    }

    #[Computed]
    public function tableColumns(): array
    {
        return [
            'Name',
            'Category',
            'Model',
            ['label' => 'Status', 'align' => 'center'],
            'Updated',
            ['label' => 'Actions', 'align' => 'center'],
        ];
    }

    #[Computed]
    public function tableRows(): array
    {
        $modelColors = [
            'claude' => 'orange',
            'gemini' => 'blue',
        ];

        return $this->prompts->map(function ($p) use ($modelColors) {
            $actions = [
                ['icon' => 'pencil', 'click' => "edit({$p->id})", 'title' => 'Edit'],
                ['icon' => 'document-duplicate', 'click' => "duplicate({$p->id})", 'title' => 'Duplicate'],
                ['icon' => $p->is_active ? 'pause' : 'play', 'click' => "toggleActive({$p->id})", 'title' => $p->is_active ? 'Deactivate' : 'Activate'],
                ['icon' => 'trash', 'click' => "delete({$p->id})", 'confirm' => 'Are you sure you want to delete this prompt?', 'title' => 'Delete', 'class' => 'text-red-600'],
            ];

            return [
                [
                    'lines' => array_filter([
                        ['bold' => $p->name],
                        $p->description ? ['muted' => \Illuminate\Support\Str::limit($p->description, 60)] : null,
                    ]),
                ],
                ['badge' => ucfirst($p->category), 'color' => 'violet'],
                ['badge' => ucfirst($p->model), 'color' => $modelColors[$p->model] ?? 'gray'],
                ['badge' => $p->is_active ? 'Active' : 'Inactive', 'color' => $p->is_active ? 'green' : 'gray'],
                ['muted' => $p->updated_at->diffForHumans()],
                ['actions' => $actions],
            ];
        })->all();
    }

    #[Computed]
    public function editingPrompt(): ?Prompt
    {
        return $this->editingPromptId
            ? Prompt::find($this->editingPromptId)
            : null;
    }

    #[Computed]
    public function promptVersions()
    {
        if (! $this->editingPromptId) {
            return collect();
        }

        return PromptVersion::where('prompt_id', $this->editingPromptId)
            ->with('creator')
            ->orderByDesc('version')
            ->limit(20)
            ->get();
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingPromptId = null;
        $this->showEditor = true;
    }

    public function edit(int $id): void
    {
        $prompt = Prompt::findOrFail($id);

        $this->editingPromptId = $id;
        $this->name = $prompt->name;
        $this->promptCategory = $prompt->category;
        $this->description = $prompt->description ?? '';
        $this->systemPrompt = $prompt->system_prompt;
        $this->userTemplate = $prompt->user_template;
        $this->variables = $prompt->variables ?? [];
        $this->promptModel = $prompt->model;
        $this->modelConfig = $prompt->model_config ?? ['temperature' => 1.0, 'max_tokens' => 4096];
        $this->isActive = $prompt->is_active;

        $this->showEditor = true;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'promptCategory' => 'required|string|max:50',
            'description' => 'nullable|string',
            'systemPrompt' => 'required|string',
            'userTemplate' => 'required|string',
            'variables' => 'array',
            'promptModel' => 'required|in:claude,gemini',
            'modelConfig' => 'array',
            'isActive' => 'boolean',
        ]);

        $data = [
            'name' => $this->name,
            'category' => $this->promptCategory,
            'description' => $this->description ?: null,
            'system_prompt' => $this->systemPrompt,
            'user_template' => $this->userTemplate,
            'variables' => $this->variables ?: null,
            'model' => $this->promptModel,
            'model_config' => $this->modelConfig ?: null,
            'is_active' => $this->isActive,
        ];

        if ($this->editingPromptId) {
            $prompt = Prompt::findOrFail($this->editingPromptId);

            // Create version before updating
            $prompt->createVersion(Auth::id());

            $prompt->update($data);

            Flux::toast('Prompt updated successfully');
        } else {
            Prompt::create($data);

            Flux::toast('Prompt created successfully');
        }

        $this->showEditor = false;
        $this->resetForm();
    }

    public function delete(int $id): void
    {
        $prompt = Prompt::findOrFail($id);
        $prompt->delete();

        Flux::toast('Prompt deleted');
    }

    public function duplicate(int $id): void
    {
        $original = Prompt::findOrFail($id);

        $copy = $original->replicate();
        $copy->name = $original->name.' (copy)';
        $copy->save();

        Flux::toast('Prompt duplicated');
    }

    public function toggleActive(int $id): void
    {
        $prompt = Prompt::findOrFail($id);
        $prompt->update(['is_active' => ! $prompt->is_active]);

        Flux::toast($prompt->is_active ? 'Prompt activated' : 'Prompt deactivated');
    }

    public function restoreVersion(int $versionId): void
    {
        $version = PromptVersion::findOrFail($versionId);
        $version->restore();

        // Reload the form with restored data
        $this->edit($version->prompt_id);

        Flux::toast("Restored to version {$version->version}");
    }

    public function addVariable(): void
    {
        $this->variables[] = [
            'name' => '',
            'description' => '',
            'required' => true,
            'default' => '',
        ];
    }

    public function removeVariable(int $index): void
    {
        unset($this->variables[$index]);
        $this->variables = array_values($this->variables);
    }

    public function closeEditor(): void
    {
        $this->showEditor = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->promptCategory = 'content';
        $this->description = '';
        $this->systemPrompt = '';
        $this->userTemplate = '';
        $this->variables = [];
        $this->promptModel = 'claude';
        $this->modelConfig = ['temperature' => 1.0, 'max_tokens' => 4096];
        $this->isActive = true;
        $this->editingPromptId = null;
        $this->testOutput = '';
    }

    public function render(): View
    {
        return view('hub::admin.prompt-manager');
    }
}
