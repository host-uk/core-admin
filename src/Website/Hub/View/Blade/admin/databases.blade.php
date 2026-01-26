<div x-data="{
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            $wire.dispatch('copy-to-clipboard', { text });
        });
    }
}" @copy-to-clipboard.window="copyToClipboard($event.detail.text)">

    <core:heading size="xl" class="mb-6">Databases & Integrations</core:heading>

    <div class="space-y-6">

        {{-- Internal WordPress (hestia.host.uk.com) --}}
        <core:card class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <core:icon name="fab fa-wordpress" class="w-5 h-5 text-blue-500" />
                    </div>
                    <div>
                        <core:heading size="lg">Host UK WordPress</core:heading>
                        <core:subheading>Internal content management system</core:subheading>
                    </div>
                </div>
                <core:badge color="{{ ($internalWpHealth['status'] ?? 'unknown') === 'healthy' ? 'green' : (($internalWpHealth['status'] ?? 'unknown') === 'degraded' ? 'amber' : 'red') }}">
                    {{ ucfirst($internalWpHealth['status'] ?? 'Unknown') }}
                </core:badge>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                {{-- API Status --}}
                <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                    <core:text size="sm" class="text-zinc-500 mb-1">REST API</core:text>
                    <div class="flex items-center gap-2">
                        @if($internalWpHealth['api_available'] ?? false)
                            <div class="w-2 h-2 rounded-full bg-green-500"></div>
                            <core:text class="font-medium text-green-600 dark:text-green-400">Available</core:text>
                        @else
                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                            <core:text class="font-medium text-red-600 dark:text-red-400">Unavailable</core:text>
                        @endif
                    </div>
                </div>

                {{-- Post Count --}}
                <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                    <core:text size="sm" class="text-zinc-500 mb-1">Posts</core:text>
                    <core:text class="text-2xl font-semibold">
                        {{ number_format($internalWpHealth['post_count'] ?? 0) }}
                    </core:text>
                </div>

                {{-- Page Count --}}
                <div class="p-4 rounded-lg bg-zinc-50 dark:bg-zinc-800/50">
                    <core:text size="sm" class="text-zinc-500 mb-1">Pages</core:text>
                    <core:text class="text-2xl font-semibold">
                        {{ number_format($internalWpHealth['page_count'] ?? 0) }}
                    </core:text>
                </div>
            </div>

            <div class="flex items-center justify-between text-sm text-zinc-500 mb-4">
                <span>{{ $internalWpHealth['url'] ?? 'Not configured' }}</span>
                <span>Last checked: {{ isset($internalWpHealth['last_check']) ? \Carbon\Carbon::parse($internalWpHealth['last_check'])->diffForHumans() : 'Never' }}</span>
            </div>

            <div class="flex items-center gap-3 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <core:button wire:click="refreshInternalHealth" variant="ghost" icon="arrow-path" size="sm">
                    Refresh
                </core:button>
                <core:button href="/hub/content/host-uk/posts" variant="subtle" icon="arrow-right" size="sm">
                    Manage Content
                </core:button>
            </div>
        </core:card>

        {{-- External WordPress Connector --}}
        <core:card class="p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 rounded-lg bg-violet-500/10 flex items-center justify-center">
                    <core:icon name="link" class="w-5 h-5 text-violet-500" />
                </div>
                <div>
                    <core:heading size="lg">WordPress Connector</core:heading>
                    <core:subheading>Connect your self-hosted WordPress site to sync content</core:subheading>
                </div>
            </div>

            <div class="space-y-6">
                {{-- Enable Toggle --}}
                <core:switch
                    wire:model.live="wpConnectorEnabled"
                    label="Enable WordPress Connector"
                    description="Allow your WordPress site to send content updates to Host Hub"
                />

                @if($wpConnectorEnabled)
                    {{-- WordPress URL --}}
                    <core:input
                        wire:model="wpConnectorUrl"
                        label="WordPress Site URL"
                        placeholder="https://your-site.com"
                        type="url"
                    />

                    {{-- Webhook Configuration --}}
                    <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg space-y-4">
                        <core:heading size="sm">Plugin Configuration</core:heading>
                        <core:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                            Install the Host Hub Connector plugin on your WordPress site and enter these settings:
                        </core:text>

                        {{-- Webhook URL --}}
                        <div>
                            <core:label>Webhook URL</core:label>
                            <div class="flex gap-2 mt-1">
                                <core:input
                                    :value="$this->webhookUrl"
                                    readonly
                                    class="flex-1 font-mono text-sm"
                                />
                                <core:button
                                    wire:click="copyToClipboard('{{ $this->webhookUrl }}')"
                                    variant="ghost"
                                    icon="clipboard"
                                />
                            </div>
                        </div>

                        {{-- Webhook Secret --}}
                        <div>
                            <core:label>Webhook Secret</core:label>
                            <div class="flex gap-2 mt-1">
                                <core:input
                                    :value="$this->webhookSecret"
                                    readonly
                                    type="password"
                                    class="flex-1 font-mono text-sm"
                                />
                                <core:button
                                    wire:click="copyToClipboard('{{ $this->webhookSecret }}')"
                                    variant="ghost"
                                    icon="clipboard"
                                />
                                <core:button
                                    wire:click="regenerateSecret"
                                    wire:confirm="This will invalidate the current secret. You'll need to update your WordPress plugin settings."
                                    variant="ghost"
                                    icon="arrow-path"
                                />
                            </div>
                            <core:text size="xs" class="text-zinc-500 mt-1">
                                Keep this secret safe. It's used to verify webhooks are from your WordPress site.
                            </core:text>
                        </div>
                    </div>

                    {{-- Connection Status --}}
                    <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                        <div class="flex items-center gap-3">
                            @if($this->isWpConnectorVerified)
                                <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                                <div>
                                    <core:text class="font-medium text-green-600 dark:text-green-400">Connected</core:text>
                                    @if($this->wpConnectorLastSync)
                                        <core:text size="sm" class="text-zinc-500">Last sync: {{ $this->wpConnectorLastSync }}</core:text>
                                    @endif
                                </div>
                            @else
                                <div class="w-3 h-3 bg-amber-500 rounded-full"></div>
                                <div>
                                    <core:text class="font-medium text-amber-600 dark:text-amber-400">Not verified</core:text>
                                    <core:text size="sm" class="text-zinc-500">Test the connection to verify</core:text>
                                </div>
                            @endif
                        </div>

                        <core:button
                            wire:click="testWpConnection"
                            wire:loading.attr="disabled"
                            variant="ghost"
                            icon="signal"
                        >
                            Test Connection
                        </core:button>
                    </div>

                    @if($testResult)
                        <core:callout :variant="$testSuccess ? 'success' : 'danger'" icon="{{ $testSuccess ? 'check-circle' : 'exclamation-circle' }}">
                            {{ $testResult }}
                        </core:callout>
                    @endif

                    {{-- Plugin Download --}}
                    <div class="p-4 border border-dashed border-zinc-300 dark:border-zinc-600 rounded-lg">
                        <div class="flex items-start gap-3">
                            <core:icon name="puzzle-piece" class="w-5 h-5 text-violet-500 mt-0.5" />
                            <div>
                                <core:heading size="sm">WordPress Plugin</core:heading>
                                <core:text size="sm" class="text-zinc-600 dark:text-zinc-400 mt-1">
                                    Download and install the Host Hub Connector plugin on your WordPress site to enable content syncing.
                                </core:text>
                                <core:button variant="subtle" size="sm" class="mt-2" icon="arrow-down-tray">
                                    Download Plugin
                                </core:button>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="flex justify-end gap-3 pt-6 mt-6 border-t border-zinc-200 dark:border-zinc-700">
                <core:button wire:click="saveWpConnector" variant="primary">
                    Save Settings
                </core:button>
            </div>
        </core:card>

        {{-- Future Integrations Placeholder --}}
        <core:card class="p-6 border-dashed">
            <div class="text-center py-6">
                <div class="w-12 h-12 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center mx-auto mb-4">
                    <core:icon name="plus" class="w-6 h-6 text-zinc-400" />
                </div>
                <core:heading size="sm" class="text-zinc-600 dark:text-zinc-400">More Integrations Coming Soon</core:heading>
                <core:text size="sm" class="text-zinc-500 mt-1">
                    Connect additional databases and external systems
                </core:text>
            </div>
        </core:card>

    </div>
</div>
