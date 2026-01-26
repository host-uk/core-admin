<div class="relative" x-data="{ open: @entangle('open') }">
    <!-- Trigger Button -->
    <button
        @click="open = !open"
        class="flex items-center gap-2 px-3 py-2 rounded-lg bg-gray-100 dark:bg-gray-700/50 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
    >
        <div class="w-6 h-6 rounded-md bg-{{ $current['color'] }}-500/20 flex items-center justify-center">
            <core:icon :name="$current['icon']" class="text-{{ $current['color'] }}-500 text-xs" />
        </div>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ $current['name'] }}</span>
        <svg class="w-4 h-4 text-gray-400 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown -->
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        x-transition:leave="transition ease-in duration-75"
        x-transition:leave-start="opacity-100 scale-100"
        x-transition:leave-end="opacity-0 scale-95"
        @click.outside="open = false"
        class="absolute left-0 mt-2 w-64 bg-white dark:bg-gray-800 rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
        x-cloak
    >
        <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
            <p class="text-xs font-semibold text-gray-400 dark:text-gray-500 uppercase tracking-wider">{{ __('hub::hub.workspace_switcher.title') }}</p>
        </div>
        <div class="py-2">
            @foreach($workspaces as $slug => $workspace)
            <button
                wire:click="switchWorkspace('{{ $slug }}')"
                class="w-full flex items-center gap-3 px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition {{ $current['slug'] === $slug ? 'bg-gray-50 dark:bg-gray-700/30' : '' }}"
            >
                <div class="w-8 h-8 rounded-lg bg-{{ $workspace['color'] }}-500/20 flex items-center justify-center shrink-0">
                    <core:icon :name="$workspace['icon']" class="text-{{ $workspace['color'] }}-500" />
                </div>
                <div class="flex-1 text-left">
                    <div class="text-sm font-medium text-gray-800 dark:text-gray-100">{{ $workspace['name'] }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $workspace['description'] }}</div>
                </div>
                @if($current['slug'] === $slug)
                <core:icon name="check" class="text-{{ $workspace['color'] }}-500" />
                @endif
            </button>
            @endforeach
        </div>
        <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/20">
            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                <core:icon name="globe" />
                <span class="truncate">{{ $current['domain'] }}</span>
            </div>
        </div>
    </div>
</div>
