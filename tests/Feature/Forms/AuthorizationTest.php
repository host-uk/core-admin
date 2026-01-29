<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Core\Admin\Forms\View\Components\Button;
use Core\Admin\Forms\View\Components\Checkbox;
use Core\Admin\Forms\View\Components\Input;
use Core\Admin\Forms\View\Components\Select;
use Core\Admin\Forms\View\Components\Textarea;
use Core\Admin\Forms\View\Components\Toggle;
use Illuminate\Contracts\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Access\Gate;

/**
 * Tests for form component authorization props.
 *
 * These tests verify that form components correctly handle the `canGate`,
 * `canResource`, and `canHide` authorization props to disable or hide
 * components based on user permissions.
 */

beforeEach(function () {
    // Reset gate mock between tests
    app()->forgetInstance(Gate::class);
});

afterEach(function () {
    Mockery::close();
});

/**
 * Create a mock user that can/cannot perform an action.
 */
function mockUserWithPermission(bool $canPerform): void
{
    $user = Mockery::mock(Authorizable::class);
    $user->shouldReceive('can')
        ->andReturn($canPerform);

    test()->actingAs($user);
}

/**
 * Create a mock resource for testing authorization.
 */
function mockResource(): object
{
    return new class
    {
        public int $id = 1;

        public int $workspace_id = 1;
    };
}

// =============================================================================
// Button Component Authorization Tests
// =============================================================================

describe('Button component authorization', function () {
    it('is enabled when no authorization props are provided', function () {
        mockUserWithPermission(true);

        $button = new Button(
            type: 'submit',
            variant: 'primary',
        );

        expect($button->disabled)->toBeFalse()
            ->and($button->hidden)->toBeFalse();
    });

    it('is enabled when user has permission', function () {
        mockUserWithPermission(true);

        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($button->disabled)->toBeFalse()
            ->and($button->hidden)->toBeFalse();
    });

    it('is disabled when user lacks permission', function () {
        mockUserWithPermission(false);

        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($button->disabled)->toBeTrue()
            ->and($button->hidden)->toBeFalse();
    });

    it('is hidden when user lacks permission and canHide is true', function () {
        mockUserWithPermission(false);

        $button = new Button(
            type: 'submit',
            variant: 'danger',
            canGate: 'delete',
            canResource: mockResource(),
            canHide: true,
        );

        expect($button->disabled)->toBeTrue()
            ->and($button->hidden)->toBeTrue();
    });

    it('is visible when user has permission and canHide is true', function () {
        mockUserWithPermission(true);

        $button = new Button(
            type: 'submit',
            variant: 'danger',
            canGate: 'delete',
            canResource: mockResource(),
            canHide: true,
        );

        expect($button->disabled)->toBeFalse()
            ->and($button->hidden)->toBeFalse();
    });

    it('respects explicit disabled state over authorization', function () {
        mockUserWithPermission(true);

        $button = new Button(
            type: 'submit',
            variant: 'primary',
            disabled: true,
            canGate: 'update',
            canResource: mockResource(),
        );

        // Even with permission, explicit disabled takes precedence
        expect($button->disabled)->toBeTrue();
    });

    it('does not check authorization when only canGate is provided', function () {
        mockUserWithPermission(false);

        // Without canResource, authorization check should not happen
        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: 'update',
            canResource: null,
        );

        expect($button->disabled)->toBeFalse();
    });

    it('is disabled when no authenticated user', function () {
        // No user authenticated
        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: 'update',
            canResource: mockResource(),
        );

        // Should be disabled when no user is authenticated
        expect($button->disabled)->toBeTrue();
    });
});

// =============================================================================
// Input Component Authorization Tests
// =============================================================================

describe('Input component authorization', function () {
    it('is enabled when no authorization props are provided', function () {
        mockUserWithPermission(true);

        $input = new Input(
            id: 'name',
            label: 'Name',
        );

        expect($input->disabled)->toBeFalse()
            ->and($input->hidden)->toBeFalse();
    });

    it('is enabled when user has permission', function () {
        mockUserWithPermission(true);

        $input = new Input(
            id: 'name',
            label: 'Name',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($input->disabled)->toBeFalse()
            ->and($input->hidden)->toBeFalse();
    });

    it('is disabled when user lacks permission', function () {
        mockUserWithPermission(false);

        $input = new Input(
            id: 'name',
            label: 'Name',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($input->disabled)->toBeTrue()
            ->and($input->hidden)->toBeFalse();
    });

    it('is hidden when user lacks permission and canHide is true', function () {
        mockUserWithPermission(false);

        $input = new Input(
            id: 'secret_key',
            label: 'Secret Key',
            canGate: 'viewSecrets',
            canResource: mockResource(),
            canHide: true,
        );

        expect($input->disabled)->toBeTrue()
            ->and($input->hidden)->toBeTrue();
    });

    it('respects explicit disabled state', function () {
        mockUserWithPermission(true);

        $input = new Input(
            id: 'readonly_field',
            label: 'Read Only',
            disabled: true,
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($input->disabled)->toBeTrue();
    });
});

// =============================================================================
// Select Component Authorization Tests
// =============================================================================

describe('Select component authorization', function () {
    it('is enabled when no authorization props are provided', function () {
        mockUserWithPermission(true);

        $select = new Select(
            id: 'status',
            options: ['draft' => 'Draft', 'published' => 'Published'],
            label: 'Status',
        );

        expect($select->disabled)->toBeFalse()
            ->and($select->hidden)->toBeFalse();
    });

    it('is enabled when user has permission', function () {
        mockUserWithPermission(true);

        $select = new Select(
            id: 'status',
            options: ['draft' => 'Draft', 'published' => 'Published'],
            label: 'Status',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($select->disabled)->toBeFalse()
            ->and($select->hidden)->toBeFalse();
    });

    it('is disabled when user lacks permission', function () {
        mockUserWithPermission(false);

        $select = new Select(
            id: 'status',
            options: ['draft' => 'Draft', 'published' => 'Published'],
            label: 'Status',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($select->disabled)->toBeTrue()
            ->and($select->hidden)->toBeFalse();
    });

    it('is hidden when user lacks permission and canHide is true', function () {
        mockUserWithPermission(false);

        $select = new Select(
            id: 'role',
            options: ['admin' => 'Admin', 'user' => 'User'],
            label: 'Role',
            canGate: 'assignRoles',
            canResource: mockResource(),
            canHide: true,
        );

        expect($select->disabled)->toBeTrue()
            ->and($select->hidden)->toBeTrue();
    });

    it('respects explicit disabled state', function () {
        mockUserWithPermission(true);

        $select = new Select(
            id: 'locked_field',
            options: ['a' => 'A', 'b' => 'B'],
            label: 'Locked',
            disabled: true,
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($select->disabled)->toBeTrue();
    });
});

// =============================================================================
// Checkbox Component Authorization Tests
// =============================================================================

describe('Checkbox component authorization', function () {
    it('is enabled when no authorization props are provided', function () {
        mockUserWithPermission(true);

        $checkbox = new Checkbox(
            id: 'is_active',
            label: 'Active',
        );

        expect($checkbox->disabled)->toBeFalse()
            ->and($checkbox->hidden)->toBeFalse();
    });

    it('is enabled when user has permission', function () {
        mockUserWithPermission(true);

        $checkbox = new Checkbox(
            id: 'is_active',
            label: 'Active',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($checkbox->disabled)->toBeFalse()
            ->and($checkbox->hidden)->toBeFalse();
    });

    it('is disabled when user lacks permission', function () {
        mockUserWithPermission(false);

        $checkbox = new Checkbox(
            id: 'is_active',
            label: 'Active',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($checkbox->disabled)->toBeTrue()
            ->and($checkbox->hidden)->toBeFalse();
    });

    it('is hidden when user lacks permission and canHide is true', function () {
        mockUserWithPermission(false);

        $checkbox = new Checkbox(
            id: 'is_admin',
            label: 'Administrator',
            canGate: 'promoteToAdmin',
            canResource: mockResource(),
            canHide: true,
        );

        expect($checkbox->disabled)->toBeTrue()
            ->and($checkbox->hidden)->toBeTrue();
    });

    it('respects explicit disabled state', function () {
        mockUserWithPermission(true);

        $checkbox = new Checkbox(
            id: 'locked_option',
            label: 'Locked',
            disabled: true,
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($checkbox->disabled)->toBeTrue();
    });
});

// =============================================================================
// Toggle Component Authorization Tests
// =============================================================================

describe('Toggle component authorization', function () {
    it('is enabled when no authorization props are provided', function () {
        mockUserWithPermission(true);

        $toggle = new Toggle(
            id: 'is_public',
            label: 'Public',
        );

        expect($toggle->disabled)->toBeFalse()
            ->and($toggle->hidden)->toBeFalse();
    });

    it('is enabled when user has permission', function () {
        mockUserWithPermission(true);

        $toggle = new Toggle(
            id: 'is_public',
            label: 'Public',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($toggle->disabled)->toBeFalse()
            ->and($toggle->hidden)->toBeFalse();
    });

    it('is disabled when user lacks permission', function () {
        mockUserWithPermission(false);

        $toggle = new Toggle(
            id: 'is_public',
            label: 'Public',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($toggle->disabled)->toBeTrue()
            ->and($toggle->hidden)->toBeFalse();
    });

    it('is hidden when user lacks permission and canHide is true', function () {
        mockUserWithPermission(false);

        $toggle = new Toggle(
            id: 'enable_feature',
            label: 'Enable Feature',
            canGate: 'manageFeatures',
            canResource: mockResource(),
            canHide: true,
        );

        expect($toggle->disabled)->toBeTrue()
            ->and($toggle->hidden)->toBeTrue();
    });

    it('respects explicit disabled state', function () {
        mockUserWithPermission(true);

        $toggle = new Toggle(
            id: 'locked_toggle',
            label: 'Locked',
            disabled: true,
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($toggle->disabled)->toBeTrue();
    });

    it('wireChange returns null when instantSave is disabled', function () {
        $toggle = new Toggle(
            id: 'test',
            instantSave: false,
        );

        expect($toggle->wireChange())->toBeNull();
    });

    it('wireChange returns default save method when instantSave is enabled', function () {
        $toggle = new Toggle(
            id: 'test',
            instantSave: true,
        );

        expect($toggle->wireChange())->toBe('save');
    });

    it('wireChange returns custom method when specified', function () {
        $toggle = new Toggle(
            id: 'test',
            instantSave: true,
            instantSaveMethod: 'updateSetting',
        );

        expect($toggle->wireChange())->toBe('updateSetting');
    });
});

// =============================================================================
// Textarea Component Authorization Tests
// =============================================================================

describe('Textarea component authorization', function () {
    it('is enabled when no authorization props are provided', function () {
        mockUserWithPermission(true);

        $textarea = new Textarea(
            id: 'description',
            label: 'Description',
        );

        expect($textarea->disabled)->toBeFalse()
            ->and($textarea->hidden)->toBeFalse();
    });

    it('is enabled when user has permission', function () {
        mockUserWithPermission(true);

        $textarea = new Textarea(
            id: 'description',
            label: 'Description',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($textarea->disabled)->toBeFalse()
            ->and($textarea->hidden)->toBeFalse();
    });

    it('is disabled when user lacks permission', function () {
        mockUserWithPermission(false);

        $textarea = new Textarea(
            id: 'description',
            label: 'Description',
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($textarea->disabled)->toBeTrue()
            ->and($textarea->hidden)->toBeFalse();
    });

    it('is hidden when user lacks permission and canHide is true', function () {
        mockUserWithPermission(false);

        $textarea = new Textarea(
            id: 'internal_notes',
            label: 'Internal Notes',
            canGate: 'viewInternalNotes',
            canResource: mockResource(),
            canHide: true,
        );

        expect($textarea->disabled)->toBeTrue()
            ->and($textarea->hidden)->toBeTrue();
    });

    it('respects explicit disabled state', function () {
        mockUserWithPermission(true);

        $textarea = new Textarea(
            id: 'readonly_notes',
            label: 'Notes',
            disabled: true,
            canGate: 'update',
            canResource: mockResource(),
        );

        expect($textarea->disabled)->toBeTrue();
    });
});

// =============================================================================
// Workspace Context Tests
// =============================================================================

describe('Workspace context in authorization', function () {
    it('Button works with workspace-scoped resource', function () {
        $workspaceResource = new class
        {
            public int $id = 1;

            public int $workspace_id = 42;

            public string $name = 'Test Resource';
        };

        mockUserWithPermission(true);

        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: 'update',
            canResource: $workspaceResource,
        );

        expect($button->disabled)->toBeFalse()
            ->and($button->canResource)->toBe($workspaceResource)
            ->and($button->canResource->workspace_id)->toBe(42);
    });

    it('Input works with workspace-scoped resource', function () {
        $workspaceResource = new class
        {
            public int $id = 1;

            public int $workspace_id = 42;
        };

        mockUserWithPermission(false);

        $input = new Input(
            id: 'workspace_field',
            label: 'Workspace Field',
            canGate: 'update',
            canResource: $workspaceResource,
        );

        expect($input->disabled)->toBeTrue()
            ->and($input->canResource->workspace_id)->toBe(42);
    });
});

// =============================================================================
// Edge Cases and Boundary Tests
// =============================================================================

describe('Edge cases in authorization', function () {
    it('button with null resource does not check authorization', function () {
        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: 'update',
            canResource: null,
        );

        expect($button->disabled)->toBeFalse()
            ->and($button->hidden)->toBeFalse();
    });

    it('button with empty canGate does not check authorization', function () {
        mockUserWithPermission(false);

        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: '',
            canResource: mockResource(),
        );

        // Empty gate = no check = enabled
        expect($button->disabled)->toBeFalse();
    });

    it('canHide without canGate does nothing', function () {
        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canHide: true,
        );

        expect($button->hidden)->toBeFalse();
    });

    it('canHide without canResource does nothing', function () {
        $button = new Button(
            type: 'submit',
            variant: 'primary',
            canGate: 'delete',
            canResource: null,
            canHide: true,
        );

        expect($button->hidden)->toBeFalse();
    });
});

// =============================================================================
// Cross-Component Consistency Tests
// =============================================================================

describe('Cross-component consistency', function () {
    it('all components disable consistently when user lacks permission', function () {
        mockUserWithPermission(false);
        $resource = mockResource();

        $components = [
            'Button' => new Button(type: 'submit', variant: 'primary', canGate: 'update', canResource: $resource),
            'Input' => new Input(id: 'test', canGate: 'update', canResource: $resource),
            'Select' => new Select(id: 'test', options: [], canGate: 'update', canResource: $resource),
            'Checkbox' => new Checkbox(id: 'test', canGate: 'update', canResource: $resource),
            'Toggle' => new Toggle(id: 'test', canGate: 'update', canResource: $resource),
            'Textarea' => new Textarea(id: 'test', canGate: 'update', canResource: $resource),
        ];

        foreach ($components as $name => $component) {
            expect($component->disabled)->toBeTrue("$name should be disabled when user lacks permission");
            expect($component->hidden)->toBeFalse("$name should not be hidden without canHide flag");
        }
    });

    it('all components hide consistently when canHide is true', function () {
        mockUserWithPermission(false);
        $resource = mockResource();

        $components = [
            'Button' => new Button(type: 'submit', variant: 'primary', canGate: 'update', canResource: $resource, canHide: true),
            'Input' => new Input(id: 'test', canGate: 'update', canResource: $resource, canHide: true),
            'Select' => new Select(id: 'test', options: [], canGate: 'update', canResource: $resource, canHide: true),
            'Checkbox' => new Checkbox(id: 'test', canGate: 'update', canResource: $resource, canHide: true),
            'Toggle' => new Toggle(id: 'test', canGate: 'update', canResource: $resource, canHide: true),
            'Textarea' => new Textarea(id: 'test', canGate: 'update', canResource: $resource, canHide: true),
        ];

        foreach ($components as $name => $component) {
            expect($component->disabled)->toBeTrue("$name should be disabled");
            expect($component->hidden)->toBeTrue("$name should be hidden with canHide flag");
        }
    });

    it('all components enable consistently when user has permission', function () {
        mockUserWithPermission(true);
        $resource = mockResource();

        $components = [
            'Button' => new Button(type: 'submit', variant: 'primary', canGate: 'update', canResource: $resource),
            'Input' => new Input(id: 'test', canGate: 'update', canResource: $resource),
            'Select' => new Select(id: 'test', options: [], canGate: 'update', canResource: $resource),
            'Checkbox' => new Checkbox(id: 'test', canGate: 'update', canResource: $resource),
            'Toggle' => new Toggle(id: 'test', canGate: 'update', canResource: $resource),
            'Textarea' => new Textarea(id: 'test', canGate: 'update', canResource: $resource),
        ];

        foreach ($components as $name => $component) {
            expect($component->disabled)->toBeFalse("$name should be enabled when user has permission");
            expect($component->hidden)->toBeFalse("$name should be visible when user has permission");
        }
    });
});
