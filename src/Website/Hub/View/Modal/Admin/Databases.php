<?php

declare(strict_types=1);

namespace Website\Hub\View\Modal\Admin;

use Core\Mod\Tenant\Models\Workspace;
use Core\Mod\Tenant\Services\WorkspaceService;
use Flux\Flux;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('hub::admin.layouts.app')]
class Databases extends Component
{
    public ?Workspace $workspace = null;

    // WP Connector settings
    public bool $wpConnectorEnabled = false;

    public string $wpConnectorUrl = '';

    public bool $testingConnection = false;

    public ?string $testResult = null;

    public bool $testSuccess = false;

    // Internal WordPress health
    public array $internalWpHealth = [];

    public bool $loadingHealth = true;

    public function mount(WorkspaceService $workspaceService): void
    {
        if (! auth()->user()?->isHades()) {
            abort(403, 'Hades access required');
        }
        $slug = $workspaceService->currentSlug();
        $this->workspace = Workspace::where('slug', $slug)->first();

        if ($this->workspace) {
            $this->wpConnectorEnabled = $this->workspace->wp_connector_enabled ?? false;
            $this->wpConnectorUrl = $this->workspace->wp_connector_url ?? '';
        }

        $this->loadInternalWordPressHealth();
    }

    #[Computed]
    public function webhookUrl(): string
    {
        return $this->workspace?->wp_connector_webhook_url ?? '';
    }

    #[Computed]
    public function webhookSecret(): string
    {
        return $this->workspace?->wp_connector_secret ?? '';
    }

    #[Computed]
    public function isWpConnectorVerified(): bool
    {
        return $this->workspace?->wp_connector_verified_at !== null;
    }

    #[Computed]
    public function wpConnectorLastSync(): ?string
    {
        return $this->workspace?->wp_connector_last_sync?->diffForHumans();
    }

    public function loadInternalWordPressHealth(): void
    {
        $this->loadingHealth = true;

        // Cache health check for 5 minutes
        $this->internalWpHealth = Cache::remember('internal_wp_health', 300, function () {
            $health = [
                'status' => 'unknown',
                'url' => config('services.wordpress.url', 'https://hestia.host.uk.com'),
                'api_available' => false,
                'version' => null,
                'post_count' => null,
                'page_count' => null,
                'last_check' => now()->toIso8601String(),
            ];

            try {
                $response = Http::timeout(5)->get($health['url'].'/wp-json/wp/v2');

                if ($response->successful()) {
                    $health['api_available'] = true;
                    $health['status'] = 'healthy';

                    // Get post count
                    $postsResponse = Http::timeout(5)->head($health['url'].'/wp-json/wp/v2/posts');
                    if ($postsResponse->successful()) {
                        $health['post_count'] = (int) $postsResponse->header('X-WP-Total', 0);
                    }

                    // Get page count
                    $pagesResponse = Http::timeout(5)->head($health['url'].'/wp-json/wp/v2/pages');
                    if ($pagesResponse->successful()) {
                        $health['page_count'] = (int) $pagesResponse->header('X-WP-Total', 0);
                    }
                } else {
                    $health['status'] = 'degraded';
                }
            } catch (\Exception $e) {
                $health['status'] = 'offline';
                $health['error'] = $e->getMessage();
            }

            return $health;
        });

        $this->loadingHealth = false;
    }

    public function refreshInternalHealth(): void
    {
        Cache::forget('internal_wp_health');
        $this->loadInternalWordPressHealth();
        Flux::toast('Health check refreshed');
    }

    public function saveWpConnector(): void
    {
        if (! $this->workspace) {
            Flux::toast('No workspace selected', variant: 'danger');

            return;
        }

        $this->validate([
            'wpConnectorUrl' => 'nullable|url',
        ]);

        if ($this->wpConnectorEnabled && empty($this->wpConnectorUrl)) {
            Flux::toast('WordPress URL is required when connector is enabled', variant: 'danger');

            return;
        }

        if ($this->wpConnectorEnabled) {
            $this->workspace->enableWpConnector($this->wpConnectorUrl);
            Flux::toast('WordPress connector enabled');
        } else {
            $this->workspace->disableWpConnector();
            Flux::toast('WordPress connector disabled');
        }

        $this->workspace->refresh();
    }

    public function regenerateSecret(): void
    {
        if (! $this->workspace) {
            return;
        }

        $this->workspace->generateWpConnectorSecret();
        $this->workspace->refresh();

        Flux::toast('Webhook secret regenerated. Update the secret in your WordPress plugin.');
    }

    public function testWpConnection(): void
    {
        $this->testingConnection = true;
        $this->testResult = null;

        if (empty($this->workspace?->wp_connector_url)) {
            $this->testResult = 'WordPress URL is not configured';
            $this->testSuccess = false;
            $this->testingConnection = false;

            return;
        }

        try {
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

        $this->testingConnection = false;
        $this->workspace->refresh();
    }

    public function copyToClipboard(string $value): void
    {
        $this->dispatch('copy-to-clipboard', text: $value);
        Flux::toast('Copied to clipboard');
    }

    public function render(): View
    {
        return view('hub::admin.databases');
    }
}
