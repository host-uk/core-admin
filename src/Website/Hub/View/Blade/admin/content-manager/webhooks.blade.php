<!-- Webhooks Overview -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-cyan-100 dark:bg-cyan-500/20">
                <core:icon name="bolt" class="text-cyan-600 dark:text-cyan-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['webhooks_today'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.webhooks.today') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-green-100 dark:bg-green-500/20">
                <core:icon name="check" class="text-green-600 dark:text-green-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->webhookLogs->where('status', 'completed')->count() }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.webhooks.completed') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-yellow-100 dark:bg-yellow-500/20">
                <core:icon name="clock" class="text-yellow-600 dark:text-yellow-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->webhookLogs->where('status', 'pending')->count() }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.webhooks.pending') }}</core:subheading>
            </div>
        </div>
    </core:card>

    <core:card class="p-4">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg bg-red-100 dark:bg-red-500/20">
                <core:icon name="exclamation-circle" class="text-red-600 dark:text-red-400" />
            </div>
            <div>
                <core:heading size="xl">{{ $this->stats['webhooks_failed'] }}</core:heading>
                <core:subheading size="sm">{{ __('hub::hub.content_manager.webhooks.failed') }}</core:subheading>
            </div>
        </div>
    </core:card>
</div>

<!-- Webhook Logs Table -->
<core:card>
    <core:table :paginate="$this->webhookLogs">
        <core:table.columns>
            <core:table.column>{{ __('hub::hub.content_manager.webhooks.columns.id') }}</core:table.column>
            <core:table.column>{{ __('hub::hub.content_manager.webhooks.columns.event') }}</core:table.column>
            <core:table.column class="hidden md:table-cell">{{ __('hub::hub.content_manager.webhooks.columns.content') }}</core:table.column>
            <core:table.column>{{ __('hub::hub.content_manager.webhooks.columns.status') }}</core:table.column>
            <core:table.column class="hidden lg:table-cell">{{ __('hub::hub.content_manager.webhooks.columns.source_ip') }}</core:table.column>
            <core:table.column class="hidden lg:table-cell">{{ __('hub::hub.content_manager.webhooks.columns.received') }}</core:table.column>
            <core:table.column class="hidden xl:table-cell">{{ __('hub::hub.content_manager.webhooks.columns.processed') }}</core:table.column>
            <core:table.column align="end"></core:table.column>
        </core:table.columns>

        <core:table.rows>
            @forelse($this->webhookLogs as $log)
                <core:table.row :key="$log->id">
                    <core:table.cell>
                        <core:text class="text-zinc-500">#{{ $log->id }}</core:text>
                    </core:table.cell>

                    <core:table.cell variant="strong">
                        {{ $log->event_type }}
                    </core:table.cell>

                    <core:table.cell class="hidden md:table-cell">
                        <div class="flex items-center gap-2">
                            <core:badge color="blue" size="sm">{{ $log->content_type }}</core:badge>
                            <core:text size="sm" class="text-zinc-500">#{{ $log->wp_id }}</core:text>
                        </div>
                    </core:table.cell>

                    <core:table.cell>
                        <x-content.webhook-badge :status="$log->status" />
                    </core:table.cell>

                    <core:table.cell class="hidden lg:table-cell">
                        <core:text size="sm" class="font-mono text-zinc-500">{{ $log->source_ip }}</core:text>
                    </core:table.cell>

                    <core:table.cell class="hidden lg:table-cell">
                        <core:text size="sm">{{ $log->created_at->diffForHumans() }}</core:text>
                    </core:table.cell>

                    <core:table.cell class="hidden xl:table-cell">
                        <core:text size="sm">{{ $log->processed_at?->diffForHumans() ?? '-' }}</core:text>
                    </core:table.cell>

                    <core:table.cell align="end">
                        <core:dropdown>
                            <core:button variant="ghost" size="sm" icon="ellipsis-horizontal" />

                            <core:menu>
                                @if($log->status === 'failed')
                                    <core:menu.item wire:click="retryWebhook({{ $log->id }})" icon="arrow-path">
                                        {{ __('hub::hub.content_manager.webhooks.actions.retry') }}
                                    </core:menu.item>
                                @endif

                                <core:menu.item x-on:click="$dispatch('show-payload', { payload: {{ json_encode($log->payload) }} })" icon="code-bracket">
                                    {{ __('hub::hub.content_manager.webhooks.actions.view_payload') }}
                                </core:menu.item>

                                @if($log->error_message)
                                    <core:menu.separator />
                                    <div class="px-3 py-2 text-xs text-red-600 dark:text-red-400">
                                        <strong>{{ __('hub::hub.content_manager.webhooks.error') }}:</strong> {{ Str::limit($log->error_message, 80) }}
                                    </div>
                                @endif
                            </core:menu>
                        </core:dropdown>
                    </core:table.cell>
                </core:table.row>
            @empty
                <core:table.row>
                    <core:table.cell colspan="8" class="text-center py-12">
                        <div class="flex flex-col items-center">
                            <core:icon name="bolt" class="size-12 text-zinc-300 dark:text-zinc-600 mb-3" />
                            <core:text>{{ __('hub::hub.content_manager.webhooks.no_logs') }}</core:text>
                            <core:text size="sm" class="text-zinc-500 mt-1">
                                {{ __('hub::hub.content_manager.webhooks.no_logs_description') }}
                            </core:text>
                        </div>
                    </core:table.cell>
                </core:table.row>
            @endforelse
        </core:table.rows>
    </core:table>
</core:card>

<!-- Webhook Endpoint Info -->
<core:card class="mt-6 p-6">
    <core:heading size="sm" class="mb-2">{{ __('hub::hub.content_manager.webhooks.endpoint.title') }}</core:heading>
    <div class="bg-zinc-50 dark:bg-zinc-800 px-4 py-3 rounded-lg font-mono text-sm text-violet-600 dark:text-violet-400 overflow-x-auto">
        POST {{ url('/api/v1/webhook/content') }}
    </div>
    <core:text size="sm" class="text-zinc-500 mt-3">
        {{ __('hub::hub.content_manager.webhooks.endpoint.description', ['header' => 'X-WP-Signature']) }}
    </core:text>
</core:card>

<!-- Payload Modal -->
<div x-data="{ payload: null }"
     x-on:show-payload.window="payload = $event.detail.payload; $dispatch('modal-show', { name: 'webhook-payload' })">
    <core:modal name="webhook-payload" class="max-w-2xl">
        <div class="mb-4">
            <core:heading>{{ __('hub::hub.content_manager.webhooks.payload_modal.title') }}</core:heading>
        </div>

        <div class="font-mono text-xs bg-zinc-900 text-zinc-100 p-4 rounded-lg overflow-auto max-h-96">
            <pre x-text="JSON.stringify(payload, null, 2)"></pre>
        </div>
    </core:modal>
</div>
