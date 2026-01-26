<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Mod\Tenant\Models\Workspace;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Component;

class WpConnectorSettings extends Component
{
    public Workspace $workspace;

    public bool $enabled = false;

    public string $wordpressUrl = '';

    public bool $testing = false;

    public ?string $testResult = null;

    public bool $testSuccess = false;

    public function mount(Workspace $workspace): void
    {
        $this->workspace = $workspace;
        $this->enabled = $workspace->wp_connector_enabled;
        $this->wordpressUrl = $workspace->wp_connector_url ?? '';
    }

    #[Computed]
    public function webhookUrl(): string
    {
        return $this->workspace->wp_connector_webhook_url;
    }

    #[Computed]
    public function webhookSecret(): string
    {
        return $this->workspace->wp_connector_secret ?? '';
    }

    #[Computed]
    public function isVerified(): bool
    {
        return $this->workspace->wp_connector_verified_at !== null;
    }

    #[Computed]
    public function lastSync(): ?string
    {
        return $this->workspace->wp_connector_last_sync?->diffForHumans();
    }

    public function save(): void
    {
        $this->validate([
            'wordpressUrl' => 'nullable|url',
        ]);

        if ($this->enabled && empty($this->wordpressUrl)) {
            Flux::toast('WordPress URL is required when connector is enabled', variant: 'danger');

            return;
        }

        if ($this->enabled) {
            $this->workspace->enableWpConnector($this->wordpressUrl);
            Flux::toast('WordPress connector enabled');
        } else {
            $this->workspace->disableWpConnector();
            Flux::toast('WordPress connector disabled');
        }

        $this->workspace->refresh();
    }

    public function regenerateSecret(): void
    {
        $this->workspace->generateWpConnectorSecret();
        $this->workspace->refresh();

        Flux::toast('Webhook secret regenerated. Update the secret in your WordPress plugin.');
    }

    public function testConnection(): void
    {
        $this->testing = true;
        $this->testResult = null;

        if (empty($this->workspace->wp_connector_url)) {
            $this->testResult = 'WordPress URL is not configured';
            $this->testSuccess = false;
            $this->testing = false;

            return;
        }

        try {
            // Try to reach the WordPress REST API
            $response = Http::timeout(10)->get(
                $this->workspace->wp_connector_url.'/wp-json/wp/v2'
            );

            if ($response->successful()) {
                $this->testResult = 'Connected to WordPress REST API';
                $this->testSuccess = true;
                $this->workspace->markWpConnectorVerified();
            } else {
                $this->testResult = 'WordPress returned HTTP '.$response->status();
                $this->testSuccess = false;
            }
        } catch (\Exception $e) {
            $this->testResult = 'Connection failed: '.$e->getMessage();
            $this->testSuccess = false;
        }

        $this->testing = false;
        $this->workspace->refresh();
    }

    public function copyToClipboard(string $value): void
    {
        $this->dispatch('copy-to-clipboard', text: $value);
        Flux::toast('Copied to clipboard');
    }

    public function render(): View
    {
        return view('hub::admin.wp-connector-settings');
    }
}
