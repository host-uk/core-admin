<admin:module :title="__('hub::hub.prompts.title')" :subtitle="__('hub::hub.prompts.subtitle')">
    <x-slot:actions>
        <core:button wire:click="create" icon="plus">{{ __('hub::hub.prompts.labels.new_prompt') }}</core:button>
    </x-slot:actions>

    <admin:filter-bar cols="4">
        <admin:search model="search" :placeholder="__('hub::hub.prompts.labels.search_prompts')" />
        <admin:filter model="category" :options="$this->categoryOptions" :placeholder="__('hub::hub.prompts.labels.all_categories')" />
        <admin:filter model="model" :options="$this->modelOptions" :placeholder="__('hub::hub.prompts.labels.all_models')" />
        <admin:clear-filters :fields="['search', 'category', 'model']" />
    </admin:filter-bar>

    <admin:manager-table
        :columns="$this->tableColumns"
        :rows="$this->tableRows"
        :pagination="$this->prompts"
        :empty="__('hub::hub.prompts.labels.empty')"
        emptyIcon="document-text"
    />

    {{-- Editor Modal --}}
    <core:modal name="prompt-editor" :show="$showEditor" class="max-w-6xl" @close="closeEditor">
        <div class="space-y-6">
            <core:heading size="lg">
                {{ $editingPromptId ? __('hub::hub.prompts.editor.edit_title') : __('hub::hub.prompts.editor.new_title') }}
            </core:heading>

            <form wire:submit="save" class="space-y-6">
                {{-- Basic Info --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <core:input
                        wire:model="name"
                        :label="__('hub::hub.prompts.editor.name')"
                        :placeholder="__('hub::hub.prompts.editor.name_placeholder')"
                        required
                    />

                    <core:select wire:model="promptCategory" :label="__('hub::hub.prompts.editor.category')">
                        <core:select.option value="content">{{ __('hub::hub.prompts.categories.content') }}</core:select.option>
                        <core:select.option value="seo">{{ __('hub::hub.prompts.categories.seo') }}</core:select.option>
                        <core:select.option value="refinement">{{ __('hub::hub.prompts.categories.refinement') }}</core:select.option>
                        <core:select.option value="translation">{{ __('hub::hub.prompts.categories.translation') }}</core:select.option>
                        <core:select.option value="analysis">{{ __('hub::hub.prompts.categories.analysis') }}</core:select.option>
                    </core:select>
                </div>

                <core:textarea
                    wire:model="description"
                    :label="__('hub::hub.prompts.editor.description')"
                    :placeholder="__('hub::hub.prompts.editor.description_placeholder')"
                    rows="2"
                />

                {{-- Model Settings --}}
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <core:select wire:model="promptModel" :label="__('hub::hub.prompts.editor.model')">
                        <core:select.option value="claude">{{ __('hub::hub.prompts.models.claude') }}</core:select.option>
                        <core:select.option value="gemini">{{ __('hub::hub.prompts.models.gemini') }}</core:select.option>
                    </core:select>

                    <core:input
                        type="number"
                        wire:model="modelConfig.temperature"
                        :label="__('hub::hub.prompts.editor.temperature')"
                        min="0"
                        max="2"
                        step="0.1"
                    />

                    <core:input
                        type="number"
                        wire:model="modelConfig.max_tokens"
                        :label="__('hub::hub.prompts.editor.max_tokens')"
                        min="100"
                        max="200000"
                        step="100"
                    />
                </div>

                {{-- System Prompt with Monaco --}}
                <div>
                    <core:label>{{ __('hub::hub.prompts.editor.system_prompt') }}</core:label>
                    <div wire:ignore class="mt-1">
                        <div
                            id="system-prompt-editor"
                            class="h-64 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden"
                            x-data="{
                                editor: null,
                                init() {
                                    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });
                                    require(['vs/editor/editor.main'], () => {
                                        this.editor = monaco.editor.create(this.$el, {
                                            value: @js($systemPrompt),
                                            language: 'markdown',
                                            theme: document.documentElement.classList.contains('dark') ? 'vs-dark' : 'vs',
                                            minimap: { enabled: false },
                                            wordWrap: 'on',
                                            lineNumbers: 'off',
                                            fontSize: 14,
                                            padding: { top: 12, bottom: 12 }
                                        });
                                        this.editor.onDidChangeModelContent(() => {
                                            $wire.set('systemPrompt', this.editor.getValue());
                                        });
                                    });
                                }
                            }"
                        ></div>
                    </div>
                </div>

                {{-- User Template with Monaco --}}
                <div>
                    <core:label>{{ __('hub::hub.prompts.editor.user_template') }}</core:label>
                    <core:text size="sm" class="text-zinc-500 mb-1">{{ __('hub::hub.prompts.editor.user_template_hint') }}</core:text>
                    <div wire:ignore class="mt-1">
                        <div
                            id="user-template-editor"
                            class="h-48 border border-zinc-200 dark:border-zinc-700 rounded-lg overflow-hidden"
                            x-data="{
                                editor: null,
                                init() {
                                    require.config({ paths: { vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs' } });
                                    require(['vs/editor/editor.main'], () => {
                                        this.editor = monaco.editor.create(this.$el, {
                                            value: @js($userTemplate),
                                            language: 'markdown',
                                            theme: document.documentElement.classList.contains('dark') ? 'vs-dark' : 'vs',
                                            minimap: { enabled: false },
                                            wordWrap: 'on',
                                            lineNumbers: 'off',
                                            fontSize: 14,
                                            padding: { top: 12, bottom: 12 }
                                        });
                                        this.editor.onDidChangeModelContent(() => {
                                            $wire.set('userTemplate', this.editor.getValue());
                                        });
                                    });
                                }
                            }"
                        ></div>
                    </div>
                </div>

                {{-- Variables --}}
                <div>
                    <div class="flex justify-between items-center mb-2">
                        <core:label>{{ __('hub::hub.prompts.editor.template_variables') }}</core:label>
                        <core:button type="button" wire:click="addVariable" size="xs" variant="ghost" icon="plus">
                            {{ __('hub::hub.prompts.editor.add_variable') }}
                        </core:button>
                    </div>

                    @if(count($variables) > 0)
                        <div class="space-y-2">
                            @foreach($variables as $index => $var)
                                <div class="flex gap-2 items-start p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                                    <core:input
                                        wire:model="variables.{{ $index }}.name"
                                        :placeholder="__('hub::hub.prompts.editor.variable_name')"
                                        size="sm"
                                        class="flex-1"
                                    />
                                    <core:input
                                        wire:model="variables.{{ $index }}.description"
                                        :placeholder="__('hub::hub.prompts.editor.variable_description')"
                                        size="sm"
                                        class="flex-1"
                                    />
                                    <core:input
                                        wire:model="variables.{{ $index }}.default"
                                        :placeholder="__('hub::hub.prompts.editor.variable_default')"
                                        size="sm"
                                        class="flex-1"
                                    />
                                    <core:button type="button" wire:click="removeVariable({{ $index }})" size="sm" variant="ghost" icon="x-mark" />
                                </div>
                            @endforeach
                        </div>
                    @else
                        <core:text size="sm" class="text-zinc-500 italic">{{ __('hub::hub.prompts.editor.no_variables') }}</core:text>
                    @endif
                </div>

                {{-- Active Toggle --}}
                <core:switch wire:model="isActive" :label="__('hub::hub.prompts.editor.active')" :description="__('hub::hub.prompts.editor.active_description')" />

                {{-- Actions --}}
                <div class="flex justify-between pt-4 border-t border-zinc-200 dark:border-zinc-700">
                    @if($editingPromptId)
                        <core:button type="button" wire:click="$set('showVersions', true)" variant="ghost" icon="clock">
                            {{ __('hub::hub.prompts.editor.version_history') }}
                        </core:button>
                    @else
                        <div></div>
                    @endif

                    <div class="flex gap-3">
                        <core:button type="button" wire:click="closeEditor" variant="ghost">
                            {{ __('hub::hub.prompts.editor.cancel') }}
                        </core:button>
                        <core:button type="submit" variant="primary">
                            {{ $editingPromptId ? __('hub::hub.prompts.editor.update_prompt') : __('hub::hub.prompts.editor.create_prompt') }}
                        </core:button>
                    </div>
                </div>
            </form>
        </div>
    </core:modal>

    {{-- Version History Modal --}}
    <core:modal name="version-history" :show="$showVersions" @close="$set('showVersions', false)">
        <core:heading size="lg" class="mb-4">{{ __('hub::hub.prompts.versions.title') }}</core:heading>

        @if($this->promptVersions->isNotEmpty())
            <div class="space-y-2 max-h-96 overflow-y-auto">
                @foreach($this->promptVersions as $version)
                    <div class="flex justify-between items-center p-3 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg">
                        <div>
                            <core:text class="font-medium">{{ __('hub::hub.prompts.versions.version', ['number' => $version->version]) }}</core:text>
                            <core:text size="sm" class="text-zinc-500">
                                {{ $version->created_at->format('M j, Y H:i') }}
                                @if($version->creator)
                                    {{ __('hub::hub.prompts.versions.by', ['name' => $version->creator->name]) }}
                                @endif
                            </core:text>
                        </div>
                        <core:button wire:click="restoreVersion({{ $version->id }})" size="sm" variant="ghost" icon="arrow-uturn-left">
                            {{ __('hub::hub.prompts.versions.restore') }}
                        </core:button>
                    </div>
                @endforeach
            </div>
        @else
            <core:text class="text-zinc-500 italic">{{ __('hub::hub.prompts.versions.no_history') }}</core:text>
        @endif
    </core:modal>
</admin:module>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/monaco-editor@0.45.0/min/vs/loader.js"></script>
@endpush
