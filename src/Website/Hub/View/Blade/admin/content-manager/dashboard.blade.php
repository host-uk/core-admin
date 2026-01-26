<!-- Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mb-8">
    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-violet-100 dark:bg-violet-500/20">
                <core:icon name="document-text" class="text-violet-600 dark:text-violet-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['total'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.total_content') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-blue-100 dark:bg-blue-500/20">
                <core:icon name="newspaper" class="text-blue-600 dark:text-blue-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['posts'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.posts') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-green-100 dark:bg-green-500/20">
                <core:icon name="check-circle" class="text-green-600 dark:text-green-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['published'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.published') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-yellow-100 dark:bg-yellow-500/20">
                <core:icon name="pencil" class="text-yellow-600 dark:text-yellow-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['drafts'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.drafts') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-cyan-100 dark:bg-cyan-500/20">
                <core:icon name="arrow-path" class="text-cyan-600 dark:text-cyan-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['synced'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.synced') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-red-100 dark:bg-red-500/20">
                <core:icon name="exclamation-circle" class="text-red-600 dark:text-red-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['failed'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.failed') }}</core:subheading>
            </div>
        </div>
    </core:card>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
    <!-- Content Over Time Chart -->
    <core:card class="p-6">
        <div class="mb-4">
            <core:heading>{{ __('hub::hub.content_manager.dashboard.content_created') }}</core:heading>
        </div>

        <div class="h-64">
            <core:chart :value="$this->chartData" class="h-full">
                <core:chart.viewport class="h-48">
                    <core:chart.svg>
                        <core:chart.line field="count" class="text-violet-500" />
                        <core:chart.area field="count" class="text-violet-500/20" />
                    </core:chart.svg>

                    <core:chart.cursor>
                        <core:chart.tooltip>
                            <core:chart.tooltip.heading field="date" :format="['month' => 'short', 'day' => 'numeric']" />
                            <core:chart.tooltip.value field="count" label="{{ __('hub::hub.content_manager.dashboard.tooltip_posts') }}" />
                        </core:chart.tooltip>
                    </core:chart.cursor>
                </core:chart.viewport>

                <core:chart.axis axis="x" field="date" :format="['month' => 'short', 'day' => 'numeric']" />
            </core:chart>
        </div>
    </core:card>

    <!-- Content by Type Chart -->
    <core:card class="p-6">
        <div class="mb-4">
            <core:heading>{{ __('hub::hub.content_manager.dashboard.content_by_type') }}</core:heading>
        </div>

        <div>
            <div class="space-y-4">
                @php
                    $total = $this->stats['posts'] + $this->stats['pages'];
                    $postsPercent = $total > 0 ? round(($this->stats['posts'] / $total) * 100) : 0;
                    $pagesPercent = $total > 0 ? round(($this->stats['pages'] / $total) * 100) : 0;
                @endphp

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('hub::hub.content_manager.dashboard.posts') }}</span>
                        <span class="text-zinc-500">{{ $this->stats['posts'] }} ({{ $postsPercent }}%)</span>
                    </div>
                    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                        <div class="bg-violet-500 h-2 rounded-full transition-all duration-300" style="width: {{ $postsPercent }}%"></div>
                    </div>
                </div>

                <div>
                    <div class="flex justify-between text-sm mb-1">
                        <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('hub::hub.content_manager.dashboard.pages') }}</span>
                        <span class="text-zinc-500">{{ $this->stats['pages'] }} ({{ $pagesPercent }}%)</span>
                    </div>
                    <div class="w-full bg-zinc-200 dark:bg-zinc-700 rounded-full h-2">
                        <div class="bg-cyan-500 h-2 rounded-full transition-all duration-300" style="width: {{ $pagesPercent }}%"></div>
                    </div>
                </div>
            </div>

            <core:separator class="my-6" />

            <div class="grid grid-cols-2 gap-4 text-center">
                <div>
                    <core:heading size="xl">{{ $this->stats['categories'] }}</core:heading>
                    <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.categories') }}</core:subheading>
                </div>
                <div>
                    <core:heading size="xl">{{ $this->stats['tags'] }}</core:heading>
                    <core:subheading size="sm">{{ __('hub::hub.content_manager.dashboard.tags') }}</core:subheading>
                </div>
            </div>
        </div>
    </core:card>
</div>

<!-- Sync Status Overview -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <core:card class="p-6">
        <div class="mb-4">
            <core:heading>{{ __('hub::hub.content_manager.dashboard.sync_status') }}</core:heading>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-green-500"></div>
                    <core:text>{{ __('hub::hub.content_manager.dashboard.synced') }}</core:text>
                </div>
                <core:badge color="green">{{ $this->stats['synced'] }}</core:badge>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-yellow-500"></div>
                    <core:text>{{ __('hub::hub.content_manager.dashboard.pending') }}</core:text>
                </div>
                <core:badge color="yellow">{{ $this->stats['pending'] }}</core:badge>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-orange-500"></div>
                    <core:text>{{ __('hub::hub.content_manager.dashboard.stale') }}</core:text>
                </div>
                <core:badge color="orange">{{ $this->stats['stale'] }}</core:badge>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 rounded-full bg-red-500"></div>
                    <core:text>{{ __('hub::hub.content_manager.dashboard.failed') }}</core:text>
                </div>
                <core:badge color="red">{{ $this->stats['failed'] }}</core:badge>
            </div>
        </div>
    </core:card>

    <core:card class="p-6">
        <div class="mb-4">
            <core:heading>{{ __('hub::hub.content_manager.dashboard.taxonomies') }}</core:heading>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <core:icon name="folder" class="text-violet-500" />
                    <core:text>{{ __('hub::hub.content_manager.dashboard.categories') }}</core:text>
                </div>
                <core:badge>{{ $this->stats['categories'] }}</core:badge>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <core:icon name="hashtag" class="text-blue-500" />
                    <core:text>{{ __('hub::hub.content_manager.dashboard.tags') }}</core:text>
                </div>
                <core:badge>{{ $this->stats['tags'] }}</core:badge>
            </div>
        </div>
    </core:card>

    <core:card class="p-6">
        <div class="mb-4">
            <core:heading>{{ __('hub::hub.content_manager.dashboard.webhooks_today') }}</core:heading>
        </div>

        <div class="space-y-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <core:icon name="bolt" class="text-cyan-500" />
                    <core:text>{{ __('hub::hub.content_manager.dashboard.received') }}</core:text>
                </div>
                <core:badge color="cyan">{{ $this->stats['webhooks_today'] }}</core:badge>
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <core:icon name="exclamation-circle" class="text-red-500" />
                    <core:text>{{ __('hub::hub.content_manager.dashboard.failed') }}</core:text>
                </div>
                <core:badge color="red">{{ $this->stats['webhooks_failed'] }}</core:badge>
            </div>
        </div>
    </core:card>
</div>
