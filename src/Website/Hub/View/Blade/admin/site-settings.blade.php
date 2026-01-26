@php
    // Map service colors to actual Tailwind classes (dynamic classes don't work with Tailwind purge)
    $colorClasses = [
        'violet' => [
            'bg' => 'bg-violet-500/20',
            'icon' => 'text-violet-500',
            'link' => 'text-violet-500 hover:text-violet-600',
        ],
        'blue' => [
            'bg' => 'bg-blue-500/20',
            'icon' => 'text-blue-500',
            'link' => 'text-blue-500 hover:text-blue-600',
        ],
        'cyan' => [
            'bg' => 'bg-cyan-500/20',
            'icon' => 'text-cyan-500',
            'link' => 'text-cyan-500 hover:text-cyan-600',
        ],
        'orange' => [
            'bg' => 'bg-orange-500/20',
            'icon' => 'text-orange-500',
            'link' => 'text-orange-500 hover:text-orange-600',
        ],
        'yellow' => [
            'bg' => 'bg-yellow-500/20',
            'icon' => 'text-yellow-500',
            'link' => 'text-yellow-500 hover:text-yellow-600',
        ],
        'teal' => [
            'bg' => 'bg-teal-500/20',
            'icon' => 'text-teal-500',
            'link' => 'text-teal-500 hover:text-teal-600',
        ],
    ];
@endphp

<div>
    <!-- Page Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <div class="flex items-center gap-3">
                <core:heading size="xl">Site Settings</core:heading>
                @if($this->workspace)
                    <core:badge color="violet" icon="globe">
                        {{ $this->workspace->name }}
                    </core:badge>
                @endif
            </div>
            <core:subheading>Configure your site services and settings</core:subheading>
        </div>

        <div class="flex items-center gap-3">
            <core:button variant="ghost" icon="plus">
                New Workspace
            </core:button>
        </div>
    </div>

    @if (session()->has('success'))
        <div class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 p-4 text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 p-4 text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
    @endif

    @if(!$this->workspace)
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-xl p-6">
            <div class="flex items-center">
                <core:icon name="triangle-exclamation" class="text-yellow-500 w-6 h-6 mr-3" />
                <div>
                    <h3 class="font-medium text-yellow-800 dark:text-yellow-200">No Workspace Selected</h3>
                    <p class="text-yellow-700 dark:text-yellow-300">Please select a workspace using the switcher in the header.</p>
                </div>
            </div>
        </div>
    @else
        <!-- Tab Navigation -->
        <admin:tabs :tabs="$this->tabs" :selected="$tab" />

        <!-- Tab Content -->
        @if($tab === 'services')
            <div class="mb-6 flex items-center justify-between">
                <p class="text-gray-600 dark:text-gray-400">Enable services for this site</p>
                <core:button href="/hub/account/usage?tab=boosts" wire:navigate variant="primary" icon="bolt">
                    Get More Services
                </core:button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($this->serviceCards as $service)
                    @php $colors = $colorClasses[$service['color']] ?? $colorClasses['violet']; @endphp
                    <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden border border-gray-100 dark:border-gray-700">
                        {{-- Card Header --}}
                        <div class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 rounded-lg {{ $colors['bg'] }} flex items-center justify-center mr-3">
                                        <core:icon :name="$service['icon']" class="{{ $colors['icon'] }} text-lg" />
                                    </div>
                                    <div>
                                        <h3 class="font-semibold text-gray-800 dark:text-gray-100">{{ $service['name'] }}</h3>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $service['description'] }}</p>
                                    </div>
                                </div>
                                @unless($service['entitled'])
                                    <core:button wire:click="addService('{{ $service['feature'] }}')" variant="primary" size="sm" icon="plus">
                                        Add
                                    </core:button>
                                @endunless
                            </div>
                        </div>

                        {{-- Features List --}}
                        <div class="px-5 py-4">
                            <ul class="space-y-2">
                                @foreach($service['features'] as $feature)
                                    <li class="flex items-center text-sm text-gray-600 dark:text-gray-300">
                                        <core:icon name="check" class="{{ $colors['icon'] }} mr-2 text-xs" />
                                        {{ $feature }}
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        {{-- Card Footer --}}
                        <div class="px-5 py-3 bg-gray-50 dark:bg-gray-700/20 border-t border-gray-100 dark:border-gray-700/60">
                            <div class="flex items-center justify-between">
                                @if($service['entitled'])
                                    <flux:badge color="green" size="sm" icon="check">Active</flux:badge>
                                    <flux:button href="{{ $service['adminRoute'] }}" wire:navigate variant="ghost" size="sm" icon-trailing="chevron-right">
                                        Manage
                                    </flux:button>
                                @else
                                    <flux:badge color="zinc" size="sm">Not active</flux:badge>
                                    <core:badge color="zinc" size="sm" icon="lock">Locked</core:badge>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @elseif($tab === 'general')
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="text-lg font-semibold text-gray-800 dark:text-gray-100">General Settings</h2>
                </div>
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700/60">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Site name</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $this->workspace->name }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700/60">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Domain</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $this->workspace->domain ?? 'Not configured' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-700/60">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Description</span>
                        <span class="text-sm font-medium text-gray-800 dark:text-gray-200">{{ $this->workspace->description ?? 'No description' }}</span>
                    </div>
                    <div class="flex items-center justify-between py-3">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Status</span>
                        @if($this->workspace->is_active)
                            <core:badge color="green">Active</core:badge>
                        @else
                            <core:badge color="gray">Inactive</core:badge>
                        @endif
                    </div>
                </div>
            </div>
        @elseif($tab === 'deployment')
            <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-xl p-6">
                <div class="flex items-start">
                    <core:icon name="wrench" class="text-violet-500 w-6 h-6 mr-3 flex-shrink-0" />
                    <div>
                        <h3 class="font-medium text-violet-800 dark:text-violet-200">Coming Soon</h3>
                        <p class="text-violet-700 dark:text-violet-300">
                            Deployment settings will allow you to configure Git repository, branches, build commands, and deploy hooks.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($tab === 'environment')
            <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-xl p-6">
                <div class="flex items-start">
                    <core:icon name="wrench" class="text-violet-500 w-6 h-6 mr-3 flex-shrink-0" />
                    <div>
                        <h3 class="font-medium text-violet-800 dark:text-violet-200">Coming Soon</h3>
                        <p class="text-violet-700 dark:text-violet-300">
                            Environment settings will allow you to configure environment variables, secrets, and runtime versions.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($tab === 'ssl')
            <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-xl p-6">
                <div class="flex items-start">
                    <core:icon name="wrench" class="text-violet-500 w-6 h-6 mr-3 flex-shrink-0" />
                    <div>
                        <h3 class="font-medium text-violet-800 dark:text-violet-200">Coming Soon</h3>
                        <p class="text-violet-700 dark:text-violet-300">
                            SSL & Security settings will allow you to manage SSL certificates, force HTTPS, and HTTP/2 configuration.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($tab === 'backups')
            <div class="bg-violet-50 dark:bg-violet-900/20 border border-violet-200 dark:border-violet-800 rounded-xl p-6">
                <div class="flex items-start">
                    <core:icon name="wrench" class="text-violet-500 w-6 h-6 mr-3 flex-shrink-0" />
                    <div>
                        <h3 class="font-medium text-violet-800 dark:text-violet-200">Coming Soon</h3>
                        <p class="text-violet-700 dark:text-violet-300">
                            Backup settings will allow you to configure backup frequency, retention periods, and restore points.
                        </p>
                    </div>
                </div>
            </div>
        @elseif($tab === 'danger')
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6">
                <div class="flex items-start">
                    <core:icon name="triangle-exclamation" class="text-red-500 w-6 h-6 mr-3 flex-shrink-0" />
                    <div>
                        <h3 class="font-medium text-red-800 dark:text-red-200">Danger Zone</h3>
                        <p class="text-red-700 dark:text-red-300 mb-4">
                            These actions are destructive and cannot be undone.
                        </p>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-800">
                                <div>
                                    <h4 class="font-medium text-gray-800 dark:text-gray-200">Transfer Ownership</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Transfer this site to another user</p>
                                </div>
                                <core:button variant="danger" disabled>Transfer</core:button>
                            </div>
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-red-200 dark:border-red-800">
                                <div>
                                    <h4 class="font-medium text-gray-800 dark:text-gray-200">Delete Site</h4>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Permanently delete this site and all its data</p>
                                </div>
                                <core:button variant="danger" disabled>Delete</core:button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
