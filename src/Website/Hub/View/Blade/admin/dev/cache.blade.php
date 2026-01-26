<div>
    {{-- Page header --}}
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Cache Management</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Clear application caches and optimise performance</p>
        </div>
    </div>

    {{-- Cache actions grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-8">
        {{-- Application Cache --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                    <i class="fa-solid fa-database text-blue-600 dark:text-blue-400"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Application Cache</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Redis/file cache data</p>
                </div>
            </div>
            <flux:button
                wire:click="clearCache"
                variant="filled"
                class="w-full"
            >
                <span wire:loading.remove wire:target="clearCache">Clear Cache</span>
                <span wire:loading wire:target="clearCache">Clearing...</span>
            </flux:button>
        </div>

        {{-- Config Cache --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                    <i class="fa-solid fa-cog text-green-600 dark:text-green-400"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Configuration Cache</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Compiled config files</p>
                </div>
            </div>
            <flux:button
                wire:click="clearConfig"
                variant="filled"
                class="w-full"
            >
                <span wire:loading.remove wire:target="clearConfig">Clear Config</span>
                <span wire:loading wire:target="clearConfig">Clearing...</span>
            </flux:button>
        </div>

        {{-- View Cache --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center">
                    <i class="fa-solid fa-eye text-purple-600 dark:text-purple-400"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">View Cache</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Compiled Blade templates</p>
                </div>
            </div>
            <flux:button
                wire:click="clearViews"
                variant="filled"
                class="w-full"
            >
                <span wire:loading.remove wire:target="clearViews">Clear Views</span>
                <span wire:loading wire:target="clearViews">Clearing...</span>
            </flux:button>
        </div>

        {{-- Route Cache --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-orange-100 dark:bg-orange-900/30 flex items-center justify-center">
                    <i class="fa-solid fa-route text-orange-600 dark:text-orange-400"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Route Cache</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Compiled route files</p>
                </div>
            </div>
            <flux:button
                wire:click="clearRoutes"
                variant="filled"
                class="w-full"
            >
                <span wire:loading.remove wire:target="clearRoutes">Clear Routes</span>
                <span wire:loading wire:target="clearRoutes">Clearing...</span>
            </flux:button>
        </div>

        {{-- Clear All --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900/30 flex items-center justify-center">
                    <i class="fa-solid fa-trash text-red-600 dark:text-red-400"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Clear All</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">All caches at once</p>
                </div>
            </div>
            <flux:button
                wire:click="clearAll"
                variant="danger"
                class="w-full"
            >
                <span wire:loading.remove wire:target="clearAll">Clear All Caches</span>
                <span wire:loading wire:target="clearAll">Clearing...</span>
            </flux:button>
        </div>

        {{-- Optimise --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-violet-100 dark:bg-violet-900/30 flex items-center justify-center">
                    <i class="fa-solid fa-bolt text-violet-600 dark:text-violet-400"></i>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 dark:text-gray-100">Optimise</h3>
                    <p class="text-xs text-gray-500 dark:text-gray-400">Rebuild all caches</p>
                </div>
            </div>
            <flux:button
                wire:click="optimise"
                variant="primary"
                class="w-full"
            >
                <span wire:loading.remove wire:target="optimise">Optimise App</span>
                <span wire:loading wire:target="optimise">Optimising...</span>
            </flux:button>
        </div>
    </div>

    {{-- Last action output --}}
    @if($lastOutput)
        <div class="bg-gray-900 rounded-lg shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-sm font-medium text-gray-400">Last Action: {{ $lastAction }}</h3>
            </div>
            <pre class="text-sm text-green-400 font-mono whitespace-pre-wrap">{{ $lastOutput }}</pre>
        </div>
    @endif
</div>
