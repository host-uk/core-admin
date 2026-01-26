<div>
    <!-- Page header -->
    <div class="mb-8">
        <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">{{ __('hub::hub.usage.title') }}</h1>
        <p class="text-gray-500 dark:text-gray-400 mt-1">{{ __('hub::hub.usage.subtitle') }}</p>
    </div>

    <div class="space-y-6">
        <!-- Active Packages -->
        <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
            <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                <h2 class="font-semibold text-gray-800 dark:text-gray-100">{{ __('hub::hub.usage.packages.title') }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('hub::hub.usage.packages.subtitle') }}</p>
            </header>
            <div class="p-5">
                @if($activePackages->isEmpty())
                    <div class="text-center py-8 text-gray-500 dark:text-gray-400">
                        <core:icon name="box" class="size-8 mx-auto mb-2 opacity-50" />
                        <p>{{ __('hub::hub.usage.packages.empty') }}</p>
                        <p class="text-sm mt-1">{{ __('hub::hub.usage.packages.empty_hint') }}</p>
                    </div>
                @else
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($activePackages as $workspacePackage)
                            <div class="flex items-start gap-4 p-4 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                @if($workspacePackage->package->icon)
                                    <div class="shrink-0 w-10 h-10 rounded-lg bg-{{ $workspacePackage->package->color ?? 'blue' }}-500/10 flex items-center justify-center">
                                        <core:icon :name="$workspacePackage->package->icon" class="size-5 text-{{ $workspacePackage->package->color ?? 'blue' }}-500" />
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-medium text-gray-900 dark:text-gray-100">
                                        {{ $workspacePackage->package->name }}
                                    </h3>
                                    @if($workspacePackage->package->description)
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            {{ $workspacePackage->package->description }}
                                        </p>
                                    @endif
                                    <div class="flex items-center gap-2 mt-2">
                                        @if($workspacePackage->package->is_base_package)
                                            <core:badge size="sm" color="purple">{{ __('hub::hub.usage.badges.base') }}</core:badge>
                                        @else
                                            <core:badge size="sm" color="blue">{{ __('hub::hub.usage.badges.addon') }}</core:badge>
                                        @endif
                                        <core:badge size="sm" color="green">{{ __('hub::hub.usage.badges.active') }}</core:badge>
                                        @if($workspacePackage->expires_at)
                                            <span class="text-xs text-gray-500">
                                                {{ __('hub::hub.usage.packages.renews', ['time' => $workspacePackage->expires_at->diffForHumans()]) }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Usage by Category -->
        @forelse($usageSummary as $category => $features)
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100 capitalize">{{ $category ?? __('hub::hub.usage.categories.general') }}</h2>
                </header>
                <div class="p-5 space-y-4">
                    @foreach($features as $feature)
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ $feature['name'] }}
                                    </span>
                                    @if(!$feature['allowed'])
                                        <core:badge size="sm" color="gray">{{ __('hub::hub.usage.badges.not_included') }}</core:badge>
                                    @elseif($feature['unlimited'])
                                        <core:badge size="sm" color="purple">{{ __('hub::hub.usage.badges.unlimited') }}</core:badge>
                                    @elseif($feature['type'] === 'boolean')
                                        <core:badge size="sm" color="green">{{ __('hub::hub.usage.badges.enabled') }}</core:badge>
                                    @endif
                                </div>

                                @if($feature['allowed'] && !$feature['unlimited'] && $feature['type'] === 'limit')
                                    <span class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ number_format($feature['used']) }} / {{ number_format($feature['limit']) }}
                                    </span>
                                @endif
                            </div>

                            @if($feature['allowed'] && !$feature['unlimited'] && $feature['type'] === 'limit')
                                <div class="relative h-2 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                    @php
                                        $percentage = min($feature['percentage'] ?? 0, 100);
                                        $colorClass = match(true) {
                                            $percentage >= 90 => 'bg-red-500',
                                            $percentage >= 75 => 'bg-amber-500',
                                            default => 'bg-green-500',
                                        };
                                    @endphp
                                    <div
                                        class="absolute inset-y-0 left-0 {{ $colorClass }} transition-all duration-300"
                                        style="width: {{ $percentage }}%"
                                    ></div>
                                </div>
                                @if($feature['near_limit'])
                                    <p class="text-xs text-amber-600 dark:text-amber-400">
                                        <core:icon name="triangle-exclamation" class="size-3 mr-1" />
                                        {{ __('hub::hub.usage.warnings.approaching_limit', ['remaining' => $feature['remaining']]) }}
                                    </p>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                    <core:icon name="chart-bar" class="size-8 mx-auto mb-2 opacity-50" />
                    <p>{{ __('hub::hub.usage.empty.title') }}</p>
                    <p class="text-sm mt-1">{{ __('hub::hub.usage.empty.hint') }}</p>
                </div>
            </div>
        @endforelse

        <!-- Active Boosts -->
        @if($activeBoosts->isNotEmpty())
            <div class="bg-white dark:bg-gray-800 shadow-xs rounded-xl">
                <header class="px-5 py-4 border-b border-gray-100 dark:border-gray-700/60">
                    <h2 class="font-semibold text-gray-800 dark:text-gray-100">{{ __('hub::hub.usage.active_boosts.title') }}</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('hub::hub.usage.active_boosts.subtitle') }}</p>
                </header>
                <div class="p-5">
                    <div class="space-y-3">
                        @foreach($activeBoosts as $boost)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/30 rounded-lg">
                                <div>
                                    <span class="font-medium text-gray-700 dark:text-gray-300">
                                        {{ $boost->feature_code }}
                                    </span>
                                    <div class="flex items-center gap-2 mt-1">
                                        @switch($boost->boost_type)
                                            @case('add_limit')
                                                <core:badge size="sm" color="blue">
                                                    +{{ number_format($boost->limit_value) }}
                                                </core:badge>
                                                @break
                                            @case('unlimited')
                                                <core:badge size="sm" color="purple">{{ __('hub::hub.usage.badges.unlimited') }}</core:badge>
                                                @break
                                            @case('enable')
                                                <core:badge size="sm" color="green">{{ __('hub::hub.usage.badges.enabled') }}</core:badge>
                                                @break
                                        @endswitch

                                        @switch($boost->duration_type)
                                            @case('cycle_bound')
                                                <span class="text-xs text-gray-500">{{ __('hub::hub.usage.duration.cycle_bound') }}</span>
                                                @break
                                            @case('duration')
                                                @if($boost->expires_at)
                                                    <span class="text-xs text-gray-500">
                                                        {{ __('hub::hub.usage.duration.expires', ['time' => $boost->expires_at->diffForHumans()]) }}
                                                    </span>
                                                @endif
                                                @break
                                            @case('permanent')
                                                <span class="text-xs text-gray-500">{{ __('hub::hub.usage.duration.permanent') }}</span>
                                                @break
                                        @endswitch
                                    </div>
                                </div>
                                @if($boost->boost_type === 'add_limit' && $boost->limit_value)
                                    <div class="text-right">
                                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">
                                            {{ number_format($boost->getRemainingLimit()) }}
                                        </span>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('hub::hub.usage.active_boosts.remaining') }}</span>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Upgrade CTA -->
        <div class="bg-gradient-to-r from-violet-500/10 to-purple-500/10 dark:from-violet-500/20 dark:to-purple-500/20 rounded-xl p-6 text-center">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
                {{ __('hub::hub.usage.cta.title') }}
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-4">
                {{ __('hub::hub.usage.cta.subtitle') }}
            </p>
            <div class="flex justify-center gap-3">
                <core:button href="/hub/account/usage?tab=boosts" wire:navigate variant="outline">
                    <core:icon name="rocket" class="mr-2" />
                    {{ __('hub::hub.usage.cta.add_boosts') }}
                </core:button>
                <core:button href="{{ route('pricing') }}" variant="primary">
                    <core:icon name="arrow-up-right-from-square" class="mr-2" />
                    {{ __('hub::hub.usage.cta.view_plans') }}
                </core:button>
            </div>
        </div>
    </div>
</div>
