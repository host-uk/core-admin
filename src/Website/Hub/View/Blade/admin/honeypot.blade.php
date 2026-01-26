<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Honeypot Monitor</h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                Track requests to disallowed paths. These may indicate malicious crawlers.
            </p>
        </div>
        <div class="flex gap-2">
            <core:button wire:click="deleteOld(30)" variant="ghost" size="sm">
                <core:icon name="trash" class="w-4 h-4 mr-1" />
                Purge 30d+
            </core:button>
        </div>
    </div>

    {{-- Flash Message --}}
    @if (session()->has('message'))
        <core:callout variant="success">
            {{ session('message') }}
        </core:callout>
    @endif

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($stats['total']) }}</div>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">Total Hits</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($stats['today']) }}</div>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">Today</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($stats['this_week']) }}</div>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">This Week</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($stats['unique_ips']) }}</div>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">Unique IPs</div>
        </div>
        <div class="bg-white dark:bg-zinc-800 rounded-lg p-4 border border-zinc-200 dark:border-zinc-700">
            <div class="text-2xl font-bold text-orange-600">{{ number_format($stats['bots']) }}</div>
            <div class="text-sm text-zinc-500 dark:text-zinc-400">Known Bots</div>
        </div>
    </div>

    {{-- Top Offenders --}}
    <div class="grid md:grid-cols-2 gap-4">
        {{-- Top IPs --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="font-medium text-zinc-900 dark:text-white">Top IPs</h3>
            </div>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($stats['top_ips'] as $row)
                    <div class="px-4 py-2 flex items-center justify-between">
                        <code class="text-sm text-zinc-600 dark:text-zinc-300">{{ $row->ip_address }}</code>
                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $row->hits }} hits</span>
                    </div>
                @empty
                    <div class="px-4 py-3 text-sm text-zinc-500">No data yet</div>
                @endforelse
            </div>
        </div>

        {{-- Top Bots --}}
        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700">
            <div class="px-4 py-3 border-b border-zinc-200 dark:border-zinc-700">
                <h3 class="font-medium text-zinc-900 dark:text-white">Top Bots</h3>
            </div>
            <div class="divide-y divide-zinc-200 dark:divide-zinc-700">
                @forelse($stats['top_bots'] as $row)
                    <div class="px-4 py-2 flex items-center justify-between">
                        <span class="text-sm text-zinc-600 dark:text-zinc-300">{{ $row->bot_name }}</span>
                        <span class="text-sm font-medium text-zinc-900 dark:text-white">{{ $row->hits }} hits</span>
                    </div>
                @empty
                    <div class="px-4 py-3 text-sm text-zinc-500">No bots detected yet</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-wrap gap-4 items-center">
        <div class="flex-1 min-w-64">
            <core:input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Search IP, user agent, or bot name..."
            />
        </div>
        <core:select wire:model.live="botFilter" class="w-40">
            <option value="">All requests</option>
            <option value="1">Bots only</option>
            <option value="0">Non-bots</option>
        </core:select>
    </div>

    {{-- Hits Table --}}
    <div class="bg-white dark:bg-zinc-800 rounded-lg border border-zinc-200 dark:border-zinc-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-900">
                    <tr>
                        <th wire:click="sortBy('created_at')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-200">
                            Time
                            @if($sortField === 'created_at')
                                <core:icon name="{{ $sortDirection === 'asc' ? 'arrow-up' : 'arrow-down' }}" class="w-3 h-3 inline ml-1" />
                            @endif
                        </th>
                        <th wire:click="sortBy('ip_address')" class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider cursor-pointer hover:text-zinc-700 dark:hover:text-zinc-200">
                            IP Address
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Path
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            Bot
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400 uppercase tracking-wider">
                            User Agent
                        </th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($hits as $hit)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                                {{ $hit->created_at->diffForHumans() }}
                            </td>
                            <td class="px-4 py-3">
                                <code class="text-sm text-zinc-900 dark:text-white">{{ $hit->ip_address }}</code>
                                @if($hit->country)
                                    <span class="text-xs text-zinc-500 ml-1">{{ $hit->country }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-300">
                                <code>{{ $hit->path }}</code>
                            </td>
                            <td class="px-4 py-3">
                                @if($hit->is_bot)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-orange-100 text-orange-800 dark:bg-orange-900/30 dark:text-orange-400">
                                        {{ $hit->bot_name ?? 'Bot' }}
                                    </span>
                                @else
                                    <span class="text-sm text-zinc-400">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400 max-w-xs truncate" title="{{ $hit->user_agent }}">
                                {{ Str::limit($hit->user_agent, 60) }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <core:button wire:click="blockIp('{{ $hit->ip_address }}')" variant="ghost" size="xs">
                                    Block
                                </core:button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-zinc-500 dark:text-zinc-400">
                                No honeypot hits recorded yet. Good news - no one's ignoring your robots.txt!
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($hits->hasPages())
            <div class="px-4 py-3 border-t border-zinc-200 dark:border-zinc-700">
                {{ $hits->links() }}
            </div>
        @endif
    </div>
</div>
