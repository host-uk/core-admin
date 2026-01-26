<div>
    {{-- Page header --}}
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div class="mb-4 sm:mb-0">
            <h1 class="text-2xl md:text-3xl text-gray-800 dark:text-gray-100 font-bold">Application Logs</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400">View recent Laravel log entries</p>
        </div>
        <div class="grid grid-flow-col sm:auto-cols-max justify-start sm:justify-end gap-2">
            <button
                wire:click="refresh"
                class="btn border-gray-300 dark:border-gray-600 hover:border-violet-500 text-gray-700 dark:text-gray-300"
            >
                <i class="fa-solid fa-refresh mr-2"></i>
                Refresh
            </button>
            <button
                wire:click="clearLogs"
                wire:confirm="Are you sure you want to clear all logs?"
                class="btn bg-red-500 hover:bg-red-600 text-white"
            >
                <i class="fa-solid fa-trash mr-2"></i>
                Clear Logs
            </button>
        </div>
    </div>

    {{-- Level filter --}}
    <div class="mb-4 flex flex-wrap gap-2">
        <button
            wire:click="setLevel('')"
            class="px-3 py-1 text-sm rounded-full {{ $levelFilter === '' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700 dark:bg-gray-700 dark:text-gray-300' }}"
        >
            All
        </button>
        <button
            wire:click="setLevel('error')"
            class="px-3 py-1 text-sm rounded-full {{ $levelFilter === 'error' ? 'bg-red-600 text-white' : 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' }}"
        >
            Error
        </button>
        <button
            wire:click="setLevel('warning')"
            class="px-3 py-1 text-sm rounded-full {{ $levelFilter === 'warning' ? 'bg-orange-600 text-white' : 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400' }}"
        >
            Warning
        </button>
        <button
            wire:click="setLevel('info')"
            class="px-3 py-1 text-sm rounded-full {{ $levelFilter === 'info' ? 'bg-blue-600 text-white' : 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' }}"
        >
            Info
        </button>
        <button
            wire:click="setLevel('debug')"
            class="px-3 py-1 text-sm rounded-full {{ $levelFilter === 'debug' ? 'bg-gray-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400' }}"
        >
            Debug
        </button>
    </div>

    {{-- Logs table --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm overflow-hidden">
        @if(count($logs) === 0)
            <div class="p-8 text-center text-gray-500 dark:text-gray-400">
                <i class="fa-solid fa-file-lines text-4xl mb-4"></i>
                <p>No log entries found</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-gray-700/50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-40">Time</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-24">Level</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Message</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($logs as $log)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                                <td class="px-4 py-3 text-gray-500 dark:text-gray-400 whitespace-nowrap font-mono text-xs">
                                    {{ $log['time'] }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @php
                                        $levelClass = match($log['level']) {
                                            'error', 'critical', 'alert', 'emergency' => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                                            'warning' => 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-400',
                                            'info', 'notice' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400',
                                            default => 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-400',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 text-xs rounded-full {{ $levelClass }}">
                                        {{ strtoupper($log['level']) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700 dark:text-gray-300 font-mono text-xs break-all">
                                    {{ Str::limit($log['message'], 300) }}
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
        Showing {{ count($logs) }} of last {{ $limit }} log entries
    </div>
</div>
