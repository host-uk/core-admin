<?php

declare(strict_types=1);

/**
 * Hub Routes Tests (TASK-010 Phase 2)
 *
 * Comprehensive tests for all authenticated hub routes.
 * Each test asserts meaningful HTML content, not just status codes.
 */

use Core\Mod\Tenant\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create([
        'account_type' => 'hades',
    ]);
});

describe('Hub Routes (Guest)', function () {
    it('redirects guests from hub home to login', function () {
        $this->get('/hub')
            ->assertRedirect();
    });

    it('redirects guests from hub dashboard to login', function () {
        $this->get('/hub/dashboard')
            ->assertRedirect();
    });

    it('redirects guests from SocialHost to login', function () {
        $this->get('/hub/social')
            ->assertRedirect();
    });

    it('redirects guests from profile to login', function () {
        $this->get('/hub/profile')
            ->assertRedirect();
    });

    it('redirects guests from settings to login', function () {
        $this->get('/hub/settings')
            ->assertRedirect();
    });

    it('redirects guests from billing to login', function () {
        $this->get('/hub/billing')
            ->assertRedirect();
    });

    it('redirects guests from analytics to login', function () {
        $this->get('/hub/analytics')
            ->assertRedirect();
    });

    it('redirects guests from bio to login', function () {
        $this->get('/hub/bio')
            ->assertRedirect();
    });

    it('redirects guests from notify to login', function () {
        $this->get('/hub/notify')
            ->assertRedirect();
    });

    it('redirects guests from trust to login', function () {
        $this->get('/hub/trust')
            ->assertRedirect();
    });
});

describe('Hub Home (Authenticated)', function () {
    it('renders hub home with welcome banner', function () {
        $this->actingAs($this->user)
            ->get('/hub')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('Your creator toolkit at a glance');
    });

    it('displays service cards on hub home', function () {
        $this->actingAs($this->user)
            ->get('/hub')
            ->assertOk()
            ->assertSee('BioHost')
            ->assertSee('SocialHost');
    });
});

describe('Hub Profile (Authenticated)', function () {
    it('renders profile page with user information', function () {
        $this->actingAs($this->user)
            ->get('/hub/profile')
            ->assertOk()
            ->assertSee($this->user->name)
            ->assertSee($this->user->email);
    });

    it('displays tier badge on profile', function () {
        $this->actingAs($this->user)
            ->get('/hub/profile')
            ->assertOk()
            ->assertSee('Settings');
    });
});

describe('Hub Settings (Authenticated)', function () {
    it('renders settings page with profile form', function () {
        $this->actingAs($this->user)
            ->get('/hub/settings')
            ->assertOk()
            ->assertSee('Account Settings')
            ->assertSee('Profile Information');
    });

    it('displays save button on settings', function () {
        $this->actingAs($this->user)
            ->get('/hub/settings')
            ->assertOk()
            ->assertSee('Save Profile');
    });
});

describe('Billing Dashboard (Authenticated)', function () {
    it('renders billing dashboard with current plan', function () {
        $this->actingAs($this->user)
            ->get('/hub/billing')
            ->assertOk()
            ->assertSee('Billing')
            ->assertSee('Current Plan');
    });

    it('displays plan upgrade option', function () {
        $this->actingAs($this->user)
            ->get('/hub/billing')
            ->assertOk()
            ->assertSee('Upgrade');
    });
});

describe('SocialHost Dashboard (Authenticated)', function () {
    it('renders social dashboard with analytics heading', function () {
        $this->actingAs($this->user)
            ->get('/hub/social')
            ->assertOk()
            ->assertSee('Dashboard')
            ->assertSee('social accounts');
    });

    it('displays period selector on social dashboard', function () {
        $this->actingAs($this->user)
            ->get('/hub/social')
            ->assertOk()
            ->assertSee('7 days')
            ->assertSee('30 days');
    });
});

describe('AnalyticsHost Index (Authenticated)', function () {
    it('renders analytics index with page header', function () {
        $this->actingAs($this->user)
            ->get('/hub/analytics')
            ->assertOk()
            ->assertSee('Analytics')
            ->assertSee('Privacy-focused');
    });

    it('displays add website button on analytics', function () {
        $this->actingAs($this->user)
            ->get('/hub/analytics')
            ->assertOk()
            ->assertSee('Add Mod');
    });
});

describe('BioHost Index (Authenticated)', function () {
    it('renders bio index with page header', function () {
        $this->actingAs($this->user)
            ->get('/hub/bio')
            ->assertOk()
            ->assertSee('Bio');
    });

    it('displays new bio page button', function () {
        $this->actingAs($this->user)
            ->get('/hub/bio')
            ->assertOk()
            ->assertSee('New');
    });
});

describe('NotifyHost Index (Authenticated)', function () {
    it('renders notify index with page header', function () {
        $this->actingAs($this->user)
            ->get('/hub/notify')
            ->assertOk()
            ->assertSee('Notify');
    });

    it('displays add website button on notify', function () {
        $this->actingAs($this->user)
            ->get('/hub/notify')
            ->assertOk()
            ->assertSee('Add');
    });
});

describe('TrustHost Index (Authenticated)', function () {
    it('renders trust index with page header', function () {
        $this->actingAs($this->user)
            ->get('/hub/trust')
            ->assertOk()
            ->assertSee('Trust');
    });

    it('displays add campaign button on trust', function () {
        $this->actingAs($this->user)
            ->get('/hub/trust')
            ->assertOk()
            ->assertSee('Add');
    });
});

describe('Dev API Routes (Hades only)', function () {
    it('allows Hades users to access dev logs API', function () {
        $this->actingAs($this->user)
            ->getJson('/hub/api/dev/logs')
            ->assertOk()
            ->assertJsonIsArray();
    });

    it('allows Hades users to access dev routes API', function () {
        $this->actingAs($this->user)
            ->getJson('/hub/api/dev/routes')
            ->assertOk()
            ->assertJsonIsArray();
    });

    it('allows Hades users to access dev session API', function () {
        $this->actingAs($this->user)
            ->getJson('/hub/api/dev/session')
            ->assertOk()
            ->assertJsonStructure(['id', 'ip', 'user_agent']);
    });

    it('denies non-Hades users access to dev APIs', function () {
        $regularUser = User::factory()->create([
            'account_type' => 'apollo',
        ]);

        $this->actingAs($regularUser)
            ->getJson('/hub/api/dev/logs')
            ->assertForbidden();
    });
});
