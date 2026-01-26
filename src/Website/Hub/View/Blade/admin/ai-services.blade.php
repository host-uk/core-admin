<div>
    <!-- Page header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ __('hub::hub.ai_services.title') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('hub::hub.ai_services.subtitle') }}</p>
        </div>
    </div>

    <!-- Success message -->
    @if($savedMessage)
        <div
            x-data="{ show: true }"
            x-show="show"
            x-init="setTimeout(() => show = false, 3000)"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg"
        >
            <div class="flex items-center">
                <core:icon name="check-circle" class="text-green-500 mr-2" />
                <span class="text-green-700 dark:text-green-400 text-sm font-medium">{{ $savedMessage }}</span>
            </div>
        </div>
    @endif

    <!-- Tabs -->
    <div class="mb-6">
        <nav class="flex space-x-4 border-b border-gray-200 dark:border-gray-700">
            <button
                wire:click="$set('activeTab', 'claude')"
                class="pb-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'claude' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#D97757]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M13.827 3.52c-.592-1.476-2.672-1.476-3.264 0L5.347 16.756c-.464 1.16.464 2.404 1.632 2.404h3.264l1.632-4.068h.25l1.632 4.068h3.264c1.168 0 2.096-1.244 1.632-2.404L13.827 3.52zM12 11.636l-1.224 3.048h2.448L12 11.636z"/>
                    </svg>
                    {{ __('hub::hub.ai_services.providers.claude.name') }}
                </span>
            </button>
            <button
                wire:click="$set('activeTab', 'gemini')"
                class="pb-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'gemini' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                        <defs>
                            <linearGradient id="gemini-gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" style="stop-color:#4285F4"/>
                                <stop offset="50%" style="stop-color:#9B72CB"/>
                                <stop offset="100%" style="stop-color:#D96570"/>
                            </linearGradient>
                        </defs>
                        <path fill="url(#gemini-gradient)" d="M12 2C12 2 12.5 7 15.5 10C18.5 13 24 12 24 12C24 12 18.5 13 15.5 16C12.5 19 12 24 12 24C12 24 11.5 19 8.5 16C5.5 13 0 12 0 12C0 12 5.5 11 8.5 8C11.5 5 12 2 12 2Z"/>
                    </svg>
                    {{ __('hub::hub.ai_services.providers.gemini.name') }}
                </span>
            </button>
            <button
                wire:click="$set('activeTab', 'openai')"
                class="pb-4 px-1 border-b-2 font-medium text-sm transition-colors {{ $activeTab === 'openai' ? 'border-violet-500 text-violet-600 dark:text-violet-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300' }}"
            >
                <span class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-[#10A37F]" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/>
                    </svg>
                    {{ __('hub::hub.ai_services.providers.openai.name') }}
                </span>
            </button>
        </nav>
    </div>

    <!-- Claude Panel -->
    @if($activeTab === 'claude')
        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 mr-3 text-[#D97757]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M13.827 3.52c-.592-1.476-2.672-1.476-3.264 0L5.347 16.756c-.464 1.16.464 2.404 1.632 2.404h3.264l1.632-4.068h.25l1.632 4.068h3.264c1.168 0 2.096-1.244 1.632-2.404L13.827 3.52zM12 11.636l-1.224 3.048h2.448L12 11.636z"/>
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('hub::hub.ai_services.providers.claude.title') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <a href="https://console.anthropic.com/settings/keys" target="_blank" class="text-violet-500 hover:text-violet-600">
                            {{ __('hub::hub.ai_services.providers.claude.api_key_link') }}
                        </a>
                    </p>
                </div>
            </div>

            <form wire:submit="saveClaude" class="space-y-6">
                <!-- API Key -->
                <div>
                    <label for="claude-api-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('hub::hub.ai_services.labels.api_key') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model="claudeApiKey"
                        type="password"
                        id="claude-api-key"
                        placeholder="sk-ant-..."
                        autocomplete="new-password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                    />
                    @error('claudeApiKey')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Model -->
                <div>
                    <label for="claude-model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('hub::hub.ai_services.labels.model') }}
                    </label>
                    <select
                        wire:model="claudeModel"
                        id="claude-model"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                    >
                        @foreach($this->claudeModels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('claudeModel')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active -->
                <div class="flex items-center">
                    <input
                        wire:model="claudeActive"
                        type="checkbox"
                        id="claude-active"
                        class="w-4 h-4 text-violet-600 bg-gray-100 border-gray-300 rounded focus:ring-violet-500 dark:focus:ring-violet-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <label for="claude-active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('hub::hub.ai_services.labels.active') }}
                    </label>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition-colors"
                >
                    <span wire:loading.remove wire:target="saveClaude">{{ __('hub::hub.ai_services.labels.save') }}</span>
                    <span wire:loading wire:target="saveClaude" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('hub::hub.ai_services.labels.saving') }}
                    </span>
                </button>
            </form>
        </div>
    @endif

    <!-- Gemini Panel -->
    @if($activeTab === 'gemini')
        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 mr-3" viewBox="0 0 24 24">
                    <defs>
                        <linearGradient id="gemini-gradient-panel" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:#4285F4"/>
                            <stop offset="50%" style="stop-color:#9B72CB"/>
                            <stop offset="100%" style="stop-color:#D96570"/>
                        </linearGradient>
                    </defs>
                    <path fill="url(#gemini-gradient-panel)" d="M12 2C12 2 12.5 7 15.5 10C18.5 13 24 12 24 12C24 12 18.5 13 15.5 16C12.5 19 12 24 12 24C12 24 11.5 19 8.5 16C5.5 13 0 12 0 12C0 12 5.5 11 8.5 8C11.5 5 12 2 12 2Z"/>
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('hub::hub.ai_services.providers.gemini.title') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <a href="https://aistudio.google.com/app/apikey" target="_blank" class="text-violet-500 hover:text-violet-600">
                            {{ __('hub::hub.ai_services.providers.gemini.api_key_link') }}
                        </a>
                    </p>
                </div>
            </div>

            <form wire:submit="saveGemini" class="space-y-6">
                <!-- API Key -->
                <div>
                    <label for="gemini-api-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('hub::hub.ai_services.labels.api_key') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model="geminiApiKey"
                        type="password"
                        id="gemini-api-key"
                        placeholder="AIza..."
                        autocomplete="new-password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                    />
                    @error('geminiApiKey')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Model -->
                <div>
                    <label for="gemini-model" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('hub::hub.ai_services.labels.model') }}
                    </label>
                    <select
                        wire:model="geminiModel"
                        id="gemini-model"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                    >
                        @foreach($this->geminiModels as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    @error('geminiModel')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active -->
                <div class="flex items-center">
                    <input
                        wire:model="geminiActive"
                        type="checkbox"
                        id="gemini-active"
                        class="w-4 h-4 text-violet-600 bg-gray-100 border-gray-300 rounded focus:ring-violet-500 dark:focus:ring-violet-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <label for="gemini-active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('hub::hub.ai_services.labels.active') }}
                    </label>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition-colors"
                >
                    <span wire:loading.remove wire:target="saveGemini">{{ __('hub::hub.ai_services.labels.save') }}</span>
                    <span wire:loading wire:target="saveGemini" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('hub::hub.ai_services.labels.saving') }}
                    </span>
                </button>
            </form>
        </div>
    @endif

    <!-- OpenAI Panel -->
    @if($activeTab === 'openai')
        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl p-6">
            <div class="flex items-center mb-4">
                <svg class="w-8 h-8 mr-3 text-[#10A37F]" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.282 9.821a5.985 5.985 0 0 0-.516-4.91 6.046 6.046 0 0 0-6.51-2.9A6.065 6.065 0 0 0 4.981 4.18a5.985 5.985 0 0 0-3.998 2.9 6.046 6.046 0 0 0 .743 7.097 5.98 5.98 0 0 0 .51 4.911 6.051 6.051 0 0 0 6.515 2.9A5.985 5.985 0 0 0 13.26 24a6.056 6.056 0 0 0 5.772-4.206 5.99 5.99 0 0 0 3.997-2.9 6.056 6.056 0 0 0-.747-7.073zM13.26 22.43a4.476 4.476 0 0 1-2.876-1.04l.141-.081 4.779-2.758a.795.795 0 0 0 .392-.681v-6.737l2.02 1.168a.071.071 0 0 1 .038.052v5.583a4.504 4.504 0 0 1-4.494 4.494zM3.6 18.304a4.47 4.47 0 0 1-.535-3.014l.142.085 4.783 2.759a.771.771 0 0 0 .78 0l5.843-3.369v2.332a.08.08 0 0 1-.033.062L9.74 19.95a4.5 4.5 0 0 1-6.14-1.646zM2.34 7.896a4.485 4.485 0 0 1 2.366-1.973V11.6a.766.766 0 0 0 .388.676l5.815 3.355-2.02 1.168a.076.076 0 0 1-.071 0l-4.83-2.786A4.504 4.504 0 0 1 2.34 7.872zm16.597 3.855l-5.833-3.387L15.119 7.2a.076.076 0 0 1 .071 0l4.83 2.791a4.494 4.494 0 0 1-.676 8.105v-5.678a.79.79 0 0 0-.407-.667zm2.01-3.023l-.141-.085-4.774-2.782a.776.776 0 0 0-.785 0L9.409 9.23V6.897a.066.066 0 0 1 .028-.061l4.83-2.787a4.5 4.5 0 0 1 6.68 4.66zm-12.64 4.135l-2.02-1.164a.08.08 0 0 1-.038-.057V6.075a4.5 4.5 0 0 1 7.375-3.453l-.142.08L8.704 5.46a.795.795 0 0 0-.393.681zm1.097-2.365l2.602-1.5 2.607 1.5v2.999l-2.597 1.5-2.607-1.5z"/>
                </svg>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">{{ __('hub::hub.ai_services.providers.openai.title') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        <a href="https://platform.openai.com/api-keys" target="_blank" class="text-violet-500 hover:text-violet-600">
                            {{ __('hub::hub.ai_services.providers.openai.api_key_link') }}
                        </a>
                    </p>
                </div>
            </div>

            <form wire:submit="saveOpenAI" class="space-y-6">
                <!-- Secret Key -->
                <div>
                    <label for="openai-secret-key" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        {{ __('hub::hub.ai_services.labels.secret_key') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        wire:model="openaiSecretKey"
                        type="password"
                        id="openai-secret-key"
                        placeholder="sk-..."
                        autocomplete="new-password"
                        class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                    />
                    @error('openaiSecretKey')
                        <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Active -->
                <div class="flex items-center">
                    <input
                        wire:model="openaiActive"
                        type="checkbox"
                        id="openai-active"
                        class="w-4 h-4 text-violet-600 bg-gray-100 border-gray-300 rounded focus:ring-violet-500 dark:focus:ring-violet-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                    />
                    <label for="openai-active" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        {{ __('hub::hub.ai_services.labels.active') }}
                    </label>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center px-4 py-2 bg-violet-600 hover:bg-violet-700 text-white font-medium rounded-lg transition-colors"
                >
                    <span wire:loading.remove wire:target="saveOpenAI">{{ __('hub::hub.ai_services.labels.save') }}</span>
                    <span wire:loading wire:target="saveOpenAI" class="flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('hub::hub.ai_services.labels.saving') }}
                    </span>
                </button>
            </form>
        </div>
    @endif
</div>
