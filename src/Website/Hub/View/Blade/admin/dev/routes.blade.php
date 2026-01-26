<div>
    {{-- Page header --}}
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Application Routes</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">Browse all registered routes ({{ count($routes) }} total)</p>
        </div>
    </div>

    {{-- Search and filter --}}
    <div class="mb-4 flex flex-col sm:flex-row gap-4">
        <div class="flex-1">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Search by URI, name, or controller..."
                class="w-full px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 focus:border-violet-500 focus:ring-violet-500"
            >
        </div>
        <div class="flex flex-wrap gap-2">
            <button
                wire:click="setMethod('')"
                class="px-3 py-2 text-sm rounded-lg {{ $methodFilter === '' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
            >
                All
            </button>
            <button
                wire:click="setMethod('GET')"
                class="px-3 py-2 text-sm rounded-lg {{ $methodFilter === 'GET' ? 'bg-green-600 text-white' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }}"
            >
                GET
            </button>
            <button
                wire:click="setMethod('POST')"
                class="px-3 py-2 text-sm rounded-lg {{ $methodFilter === 'POST' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}"
            >
                POST
            </button>
            <button
                wire:click="setMethod('PUT')"
                class="px-3 py-2 text-sm rounded-lg {{ $methodFilter === 'PUT' ? 'bg-orange-600 text-white' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' }}"
            >
                PUT
            </button>
            <button
                wire:click="setMethod('DELETE')"
                class="px-3 py-2 text-sm rounded-lg {{ $methodFilter === 'DELETE' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}"
            >
                DELETE
            </button>
        </div>
    </div>

    {{-- Routes table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        @php $filteredRoutes = $this->filteredRoutes; @endphp
        @if(count($filteredRoutes) === 0)
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-route text-4xl mb-4"></i>
                <p>No routes match your search</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-20">Method</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">URI</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Name</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($filteredRoutes as $route)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-2 whitespace-nowrap">
                                    @php
                                        $methodClass = match($route['method']) {
                                            'GET' => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                            'POST' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            'PUT', 'PATCH' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                            'DELETE' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs font-medium rounded {{ $methodClass }}">
                                        {{ $route['method'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 font-mono text-xs text-gray-700 dark:text-gray-300">
                                    {{ $route['uri'] }}
                                </td>
                                <td class="px-4 py-2 text-gray-500 dark:text-gray-400 text-xs">
                                    {{ $route['name'] ?? '-' }}
                                </td>
                                <td class="px-4 py-2 font-mono text-xs text-gray-500 dark:text-gray-400 break-all">
                                    {{ Str::limit($route['action'], 60) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Show count --}}
    <div class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        Showing {{ count($filteredRoutes) }} of {{ count($routes) }} routes
    </div>
</div>
