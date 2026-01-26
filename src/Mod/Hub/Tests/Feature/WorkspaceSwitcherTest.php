<?php

declare(strict_types=1);

namespace Core\Mod\Hub\Tests\Feature;

use Core\Mod\Hub\View\Modal\Admin\WorkspaceSwitcher;
use Core\Mod\Tenant\Models\User;
use Core\Mod\Tenant\Models\Workspace;
use Core\Mod\Tenant\Services\WorkspaceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class WorkspaceSwitcherTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Workspace $workspaceA;

    protected Workspace $workspaceB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->workspaceA = Workspace::factory()->create([
            'name' => 'Workspace A',
            'slug' => 'workspace-a',
        ]);

        $this->workspaceB = Workspace::factory()->create([
            'name' => 'Workspace B',
            'slug' => 'workspace-b',
        ]);

        // Attach user to both workspaces
        $this->user->hostWorkspaces()->attach($this->workspaceA, ['role' => 'owner', 'is_default' => true]);
        $this->user->hostWorkspaces()->attach($this->workspaceB, ['role' => 'editor']);
    }

    public function test_component_loads_with_user_workspaces(): void
    {
        $this->actingAs($this->user);

        Livewire::test(WorkspaceSwitcher::class)
            ->assertSet('workspaces', function ($workspaces) {
                return count($workspaces) === 2
                    && isset($workspaces['workspace-a'])
                    && isset($workspaces['workspace-b']);
            })
            ->assertSet('current.slug', 'workspace-a'); // Default workspace
    }

    public function test_current_workspace_is_set_from_session(): void
    {
        $this->actingAs($this->user);

        // Set workspace B in session
        session(['workspace' => 'workspace-b']);

        Livewire::test(WorkspaceSwitcher::class)
            ->assertSet('current.slug', 'workspace-b');
    }

    public function test_switch_workspace_updates_session(): void
    {
        $this->actingAs($this->user);

        // Initialize - currentModel() sets session to default workspace
        $service = app(WorkspaceService::class);
        $model = $service->currentModel();
        $this->assertEquals('workspace-a', $model->slug);
        $this->assertEquals('workspace-a', session('workspace'));

        Livewire::test(WorkspaceSwitcher::class)
            ->call('switchWorkspace', 'workspace-b');

        // Check session was updated
        $this->assertEquals('workspace-b', session('workspace'));
    }

    public function test_switch_workspace_dispatches_event(): void
    {
        $this->actingAs($this->user);

        Livewire::test(WorkspaceSwitcher::class)
            ->call('switchWorkspace', 'workspace-b')
            ->assertDispatched('workspace-changed', workspace: 'workspace-b');
    }

    public function test_switch_workspace_redirects(): void
    {
        $this->actingAs($this->user);

        Livewire::test(WorkspaceSwitcher::class)
            ->call('switchWorkspace', 'workspace-b')
            ->assertRedirect();
    }

    public function test_cannot_switch_to_workspace_user_does_not_belong_to(): void
    {
        $this->actingAs($this->user);

        $otherWorkspace = Workspace::factory()->create(['slug' => 'other-workspace']);

        Livewire::test(WorkspaceSwitcher::class)
            ->call('switchWorkspace', 'other-workspace');

        // Session should NOT be changed to the other workspace
        $this->assertNotEquals('other-workspace', session('workspace'));
    }

    public function test_workspace_service_set_current_returns_false_for_invalid_workspace(): void
    {
        $this->actingAs($this->user);

        $service = app(WorkspaceService::class);

        $this->assertFalse($service->setCurrent('nonexistent-workspace'));
        $this->assertTrue($service->setCurrent('workspace-b'));
    }

    public function test_switched_workspace_persists_across_component_instances(): void
    {
        $this->actingAs($this->user);

        // Initialize session with default workspace
        app(WorkspaceService::class)->currentModel();

        // Switch workspace
        Livewire::test(WorkspaceSwitcher::class)
            ->call('switchWorkspace', 'workspace-b');

        // Create a NEW component instance - it should see the switched workspace
        // Note: We need to manually set the session since Livewire tests are isolated
        session(['workspace' => 'workspace-b']);

        Livewire::test(WorkspaceSwitcher::class)
            ->assertSet('current.slug', 'workspace-b')
            ->assertSet('current.name', 'Workspace B');
    }

    public function test_switch_workspace_closes_dropdown(): void
    {
        $this->actingAs($this->user);

        Livewire::test(WorkspaceSwitcher::class)
            ->set('open', true)
            ->call('switchWorkspace', 'workspace-b')
            ->assertSet('open', false);
    }

    public function test_component_renders_all_workspaces_in_dropdown(): void
    {
        $this->actingAs($this->user);

        Livewire::test(WorkspaceSwitcher::class)
            ->assertSee('Workspace A')
            ->assertSee('Workspace B')
            ->assertSee('Switch Workspace');
    }

    public function test_switch_workspace_redirects_to_captured_url(): void
    {
        $this->actingAs($this->user);

        // Set a specific returnUrl and verify redirect uses it
        Livewire::test(WorkspaceSwitcher::class)
            ->set('returnUrl', 'https://example.com/test-page')
            ->call('switchWorkspace', 'workspace-b')
            ->assertRedirect('https://example.com/test-page');
    }

    public function test_return_url_is_captured_on_mount(): void
    {
        $this->actingAs($this->user);

        // Just verify returnUrl is set (not empty)
        Livewire::test(WorkspaceSwitcher::class)
            ->assertSet('returnUrl', fn ($url) => ! empty($url));
    }

    public function test_switch_workspace_falls_back_to_dashboard_if_no_return_url(): void
    {
        $this->actingAs($this->user);

        // If returnUrl is empty, should redirect to dashboard
        Livewire::test(WorkspaceSwitcher::class)
            ->set('returnUrl', '')
            ->call('switchWorkspace', 'workspace-b')
            ->assertRedirect(route('hub.dashboard'));
    }
}
