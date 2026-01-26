<div x-data="{
    copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            $wire.dispatch('copy-to-clipboard', { text });
        });
    }
}" @copy-to-clipboard.window="copyToClipboard($event.detail.text)">

    <core:card>
        <div class="flex items-center gap-3 mb-6">
            <core:icon name="link" class="w-6 h-6 text-violet-500" />
            <div>
                <core:heading size="lg">WordPress Connector</core:heading>
                <core:subheading>Connect your self-hosted WordPress site to sync content</core:subheading>
            </div>
        </div>

        <div class="space-y-6">
            <!-- Enable Toggle -->
            <core:switch
                wire:model.live="enabled"
                label="Enable WordPress Connector"
                description="Allow your WordPress site to send content updates to Host Hub"
            />

            @if($enabled)
                <!-- WordPress URL -->
                <core:input
                    wire:model="wordpressUrl"
                    label="WordPress Site URL"
                    placeholder="https://your-site.com"
                    type="url"
                />

                <!-- Webhook Configuration -->
                <div class="p-4 bg-zinc-50 dark:bg-zinc-800/50 rounded-lg space-y-4">
                    <core:heading size="sm">Plugin Configuration</core:heading>
                    <core:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        Install the Host Hub Connector plugin on your WordPress site and enter these settings:
                    </core:text>

                    <!-- Webhook URL -->
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

                    <!-- Webhook Secret -->
                    <div>
                        <core:label>Webhook Secret</core:label>
                        <div class="flex gap-2 mt-1">
                            <core:input
                                :value="$this->webhookSecret"
                                readonly
                                type="password"
                                class="flex-1 font-mono text-sm"
                                x-data="{ show: false }"
                                :x-bind:type="show ? 'text' : 'password'"
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

                <!-- Connection Status -->
                <div class="flex items-center justify-between p-4 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                    <div class="flex items-center gap-3">
                        @if($this->isVerified)
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <div>
                                <core:text class="font-medium text-green-600 dark:text-green-400">Connected</core:text>
                                @if($this->lastSync)
                                    <core:text size="sm" class="text-zinc-500">Last sync: {{ $this->lastSync }}</core:text>
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
                        wire:click="testConnection"
                        wire:loading.attr="disabled"
                        variant="ghost"
                        icon="signal"
                        :loading="$testing"
                    >
                        Test Connection
                    </core:button>
                </div>

                @if($testResult)
                    <core:callout :variant="$testSuccess ? 'success' : 'danger'" icon="{{ $testSuccess ? 'check-circle' : 'exclamation-circle' }}">
                        {{ $testResult }}
                    </core:callout>
                @endif

                <!-- Plugin Download -->
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

        <div class="flex justify-end gap-3 mt-6 pt-6 border-t border-zinc-200 dark:border-zinc-700">
            <core:button wire:click="save" variant="primary">
                Save Settings
            </core:button>
        </div>
    </core:card>
</div>
