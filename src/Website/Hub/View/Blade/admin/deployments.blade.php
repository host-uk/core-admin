<div>
    <core:heading size="xl" class="mb-2">Deployments & System Status</core:heading>
    <core:subheading class="mb-6">Monitor system health and recent deployments</core:subheading>

    {{-- Current Deployment Info --}}
    <core:card class="p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-violet-500/10 flex items-center justify-center">
                    <core:icon name="rocket-launch" class="w-5 h-5 text-violet-500" />
                </div>
                <div>
                    <core:heading size="lg">Current Deployment</core:heading>
                    <core:subheading>Branch: <code class="text-violet-600 dark:text-violet-400">{{ $this->gitInfo['branch'] }}</code></core:subheading>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <core:button wire:click="refresh" wire:loading.attr="disabled" variant="ghost" icon="arrow-path" size="sm">
                    Refresh
                </core:button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                <core:text size="sm" class="text-zinc-500 mb-1">Commit</core:text>
                <code class="text-sm font-mono text-zinc-800 dark:text-zinc-200">{{ $this->gitInfo['commit'] }}</code>
            </div>
            <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                <core:text size="sm" class="text-zinc-500 mb-1">Message</core:text>
                <core:text class="font-medium truncate" title="{{ $this->gitInfo['message'] }}">{{ \Illuminate\Support\Str::limit($this->gitInfo['message'], 30) }}</core:text>
            </div>
            <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                <core:text size="sm" class="text-zinc-500 mb-1">Author</core:text>
                <core:text class="font-medium">{{ $this->gitInfo['author'] }}</core:text>
            </div>
            <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                <core:text size="sm" class="text-zinc-500 mb-1">Deployed</core:text>
                <core:text class="font-medium">{{ $this->gitInfo['date'] ?? 'Unknown' }}</core:text>
            </div>
        </div>
    </core:card>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        @foreach($this->stats as $stat)
            <core:card class="p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-{{ $stat['color'] }}-500/10 flex items-center justify-center">
                        <core:icon name="{{ $stat['icon'] }}" class="w-5 h-5 text-{{ $stat['color'] }}-500" />
                    </div>
                    <div>
                        <core:text size="sm" class="text-zinc-500">{{ $stat['label'] }}</core:text>
                        <core:text class="text-lg font-semibold">{{ $stat['value'] }}</core:text>
                    </div>
                </div>
            </core:card>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Service Health --}}
        <core:card class="p-6">
            <core:heading size="lg" class="mb-4">Service Health</core:heading>

            <div class="space-y-3">
                @foreach($this->services as $service)
                    <div class="flex items-center justify-between p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                        <div class="flex items-center gap-3">
                            <core:icon name="{{ $service['icon'] }}" class="w-5 h-5 text-zinc-500" />
                            <div>
                                <core:text class="font-medium">{{ $service['name'] }}</core:text>
                                @if(isset($service['details']))
                                    <core:text size="sm" class="text-zinc-500">
                                        @if(isset($service['details']['version']))
                                            v{{ $service['details']['version'] }}
                                        @endif
                                        @if(isset($service['details']['memory']))
                                            &middot; {{ $service['details']['memory'] }}
                                        @endif
                                        @if(isset($service['details']['pending']))
                                            &middot; {{ $service['details']['pending'] }} pending
                                        @endif
                                        @if(isset($service['details']['used_percent']))
                                            &middot; {{ $service['details']['used_percent'] }} used
                                        @endif
                                    </core:text>
                                @endif
                                @if(isset($service['error']))
                                    <core:text size="sm" class="text-red-500">{{ $service['error'] }}</core:text>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($service['status'] === 'healthy')
                                <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                <core:text size="sm" class="text-green-600 dark:text-green-400">Healthy</core:text>
                            @elseif($service['status'] === 'warning')
                                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                <core:text size="sm" class="text-amber-600 dark:text-amber-400">Warning</core:text>
                            @elseif($service['status'] === 'unknown')
                                <span class="w-2 h-2 rounded-full bg-zinc-400"></span>
                                <core:text size="sm" class="text-zinc-500">Unknown</core:text>
                            @else
                                <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                <core:text size="sm" class="text-red-600 dark:text-red-400">Unhealthy</core:text>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <core:button wire:click="clearCache" variant="subtle" icon="trash" size="sm">
                    Clear Application Cache
                </core:button>
            </div>
        </core:card>

        {{-- Recent Commits --}}
        <core:card class="p-6">
            <core:heading size="lg" class="mb-4">Recent Commits</core:heading>

            @if(count($this->recentCommits) > 0)
                <div class="space-y-2">
                    @foreach($this->recentCommits as $commit)
                        <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                            <code class="text-xs font-mono text-violet-600 dark:text-violet-400 bg-violet-500/10 px-2 py-1 rounded mt-0.5">{{ $commit['hash'] }}</code>
                            <div class="flex-1 min-w-0">
                                <core:text class="truncate" title="{{ $commit['message'] }}">{{ $commit['message'] }}</core:text>
                                <core:text size="sm" class="text-zinc-500">{{ $commit['author'] }} &middot; {{ $commit['date'] }}</core:text>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center py-8 text-center">
                    <core:icon name="code-bracket" class="w-12 h-12 text-zinc-300 dark:text-zinc-600 mb-3" />
                    <core:text class="text-zinc-500">No commit history available</core:text>
                    <core:text size="sm" class="text-zinc-400">Git may not be available in this environment</core:text>
                </div>
            @endif
        </core:card>
    </div>

    {{-- Future Coolify Integration Notice --}}
    <core:card class="p-6 mt-6 border-dashed">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center flex-shrink-0">
                <core:icon name="rocket-launch" class="w-5 h-5 text-blue-500" />
            </div>
            <div>
                <core:heading size="sm">Coming Soon: Deployment Management</core:heading>
                <core:text size="sm" class="text-zinc-600 dark:text-zinc-400 mt-1">
                    Full deployment management with Coolify integration is planned. You'll be able to trigger deployments, view build logs, rollback to previous versions, and monitor deployment health.
                </core:text>
            </div>
        </div>
    </core:card>
</div>
