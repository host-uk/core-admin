<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Core\Front\Admin\AdminMenuRegistry;
use Core\Front\Admin\Concerns\HasMenuPermissions;
use Core\Front\Admin\Contracts\AdminMenuProvider;
use Core\Front\Admin\Support\MenuItemBuilder;
use Core\Front\Admin\Support\MenuItemGroup;
use Core\Front\Admin\Validation\IconValidator;
use Illuminate\Support\Facades\Cache;

/**
 * Tests for the admin menu system.
 *
 * These tests verify the complete admin menu system including:
 * - AdminMenuRegistry with multiple providers
 * - MenuItemBuilder fluent interface and badges
 * - Menu authorization (can/canAny)
 * - Menu active state detection
 * - IconValidator functionality
 */

// =============================================================================
// Helper Functions
// =============================================================================

/**
 * Create a mock admin menu provider.
 *
 * @param  array<int, array>  $items  Menu items to return
 * @param  array<string>  $permissions  Required permissions
 * @param  bool  $canView  Whether provider allows viewing
 */
function createMockProvider(
    array $items,
    array $permissions = [],
    bool $canView = true
): AdminMenuProvider {
    return new class($items, $permissions, $canView) implements AdminMenuProvider
    {
        use HasMenuPermissions;

        public function __construct(
            private array $items,
            private array $requiredPermissions,
            private bool $canView
        ) {}

        public function adminMenuItems(): array
        {
            return $this->items;
        }

        public function menuPermissions(): array
        {
            return $this->requiredPermissions;
        }

        public function canViewMenu(?object $user, ?object $workspace): bool
        {
            return $this->canView;
        }
    };
}

/**
 * Create a mock user object with permission checking.
 */
function createMockUser(int $id = 1, array $allowedPermissions = []): object
{
    return new class($id, $allowedPermissions)
    {
        public function __construct(
            public int $id,
            private array $allowedPermissions
        ) {}

        public function can(string $permission, mixed $resource = null): bool
        {
            return in_array($permission, $this->allowedPermissions, true);
        }

        public function hasPermission(string $permission): bool
        {
            return $this->can($permission);
        }
    };
}

/**
 * Create a mock workspace object.
 */
function createMockWorkspace(int $id = 1, string $slug = 'test-workspace'): object
{
    return new class($id, $slug)
    {
        public function __construct(
            public int $id,
            public string $slug
        ) {}
    };
}

/**
 * Create a fresh registry instance for testing.
 */
function createRegistry(): AdminMenuRegistry
{
    $registry = new AdminMenuRegistry(null, new IconValidator);
    $registry->setCachingEnabled(false);

    return $registry;
}

// =============================================================================
// AdminMenuRegistry Tests
// =============================================================================

describe('AdminMenuRegistry', function () {
    describe('provider registration', function () {
        it('returns empty array when no providers registered', function () {
            $registry = createRegistry();
            $menu = $registry->build(null);

            expect($menu)->toBeArray()
                ->and($menu)->toBeEmpty();
        });

        it('registers single provider', function () {
            $registry = createRegistry();
            $provider = createMockProvider([
                [
                    'group' => 'services',
                    'priority' => 10,
                    'item' => fn () => ['label' => 'Test Service', 'icon' => 'cog', 'href' => '/test'],
                ],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null);

            expect($menu)->not->toBeEmpty();
        });

        it('registers multiple providers', function () {
            $registry = createRegistry();

            $provider1 = createMockProvider([
                [
                    'group' => 'dashboard',
                    'priority' => 10,
                    'item' => fn () => ['label' => 'Provider 1', 'icon' => 'home', 'href' => '/one'],
                ],
            ]);

            $provider2 = createMockProvider([
                [
                    'group' => 'dashboard',
                    'priority' => 20,
                    'item' => fn () => ['label' => 'Provider 2', 'icon' => 'star', 'href' => '/two'],
                ],
            ]);

            $registry->register($provider1);
            $registry->register($provider2);

            $menu = $registry->build(null);
            $labels = array_column($menu, 'label');

            expect($labels)->toContain('Provider 1')
                ->and($labels)->toContain('Provider 2');
        });
    });

    describe('menu structure', function () {
        it('returns predefined group keys', function () {
            $registry = createRegistry();
            $groups = $registry->getGroups();

            expect($groups)->toBeArray()
                ->and($groups)->toContain('dashboard')
                ->and($groups)->toContain('workspaces')
                ->and($groups)->toContain('services')
                ->and($groups)->toContain('settings')
                ->and($groups)->toContain('admin');
        });

        it('returns group configuration for known groups', function () {
            $registry = createRegistry();
            $config = $registry->getGroupConfig('settings');

            expect($config)->toBeArray()
                ->and($config)->toHaveKey('label')
                ->and($config['label'])->toBe('Account');
        });

        it('returns empty array for unknown groups', function () {
            $registry = createRegistry();
            $config = $registry->getGroupConfig('nonexistent');

            expect($config)->toBeArray()
                ->and($config)->toBeEmpty();
        });

        it('sorts items by priority within group', function () {
            $registry = createRegistry();
            $provider = createMockProvider([
                ['group' => 'dashboard', 'priority' => 30, 'item' => fn () => ['label' => 'Third', 'icon' => 'cog', 'href' => '/third']],
                ['group' => 'dashboard', 'priority' => 10, 'item' => fn () => ['label' => 'First', 'icon' => 'home', 'href' => '/first']],
                ['group' => 'dashboard', 'priority' => 20, 'item' => fn () => ['label' => 'Second', 'icon' => 'star', 'href' => '/second']],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null);

            $labels = array_column($menu, 'label');
            expect($labels)->toBe(['First', 'Second', 'Third']);
        });

        it('uses default priority 50 when not specified', function () {
            $registry = createRegistry();
            $provider = createMockProvider([
                ['group' => 'dashboard', 'priority' => 100, 'item' => fn () => ['label' => 'Low', 'icon' => 'down', 'href' => '/low']],
                ['group' => 'dashboard', 'item' => fn () => ['label' => 'Default', 'icon' => 'minus', 'href' => '/default']],
                ['group' => 'dashboard', 'priority' => 10, 'item' => fn () => ['label' => 'High', 'icon' => 'up', 'href' => '/high']],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null);

            $labels = array_column($menu, 'label');
            expect($labels)->toBe(['High', 'Default', 'Low']);
        });

        it('adds dividers between different groups', function () {
            $registry = createRegistry();
            $provider = createMockProvider([
                ['group' => 'dashboard', 'priority' => 10, 'item' => fn () => ['label' => 'Dashboard Item', 'icon' => 'home', 'href' => '/']],
                ['group' => 'services', 'priority' => 10, 'item' => fn () => ['label' => 'Service Item', 'icon' => 'cog', 'href' => '/service']],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null);

            $hasDivider = collect($menu)->contains(fn ($item) => isset($item['divider']) && $item['divider'] === true);
            expect($hasDivider)->toBeTrue();
        });

        it('creates dropdown for non-standalone groups', function () {
            $registry = createRegistry();
            $provider = createMockProvider([
                ['group' => 'settings', 'priority' => 10, 'item' => fn () => ['label' => 'Profile', 'icon' => 'user', 'href' => '/profile']],
                ['group' => 'settings', 'priority' => 20, 'item' => fn () => ['label' => 'Security', 'icon' => 'lock', 'href' => '/security']],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null);

            $settingsDropdown = collect($menu)->first(fn ($item) => ($item['label'] ?? null) === 'Account');

            expect($settingsDropdown)->not->toBeNull()
                ->and($settingsDropdown)->toHaveKey('children')
                ->and($settingsDropdown['children'])->toHaveCount(2);
        });

        it('skips items returning null from closure', function () {
            $registry = createRegistry();
            $provider = createMockProvider([
                ['group' => 'dashboard', 'priority' => 10, 'item' => fn () => ['label' => 'Visible', 'icon' => 'eye', 'href' => '/visible']],
                ['group' => 'dashboard', 'priority' => 20, 'item' => fn () => null],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null);

            expect($menu)->toHaveCount(1)
                ->and($menu[0]['label'])->toBe('Visible');
        });
    });

    describe('authorization', function () {
        it('skips items requiring admin when user is not admin', function () {
            $registry = createRegistry();
            $provider = createMockProvider([
                ['group' => 'dashboard', 'priority' => 10, 'item' => fn () => ['label' => 'Public', 'icon' => 'globe', 'href' => '/public']],
                ['group' => 'dashboard', 'priority' => 20, 'admin' => true, 'item' => fn () => ['label' => 'Admin Only', 'icon' => 'shield', 'href' => '/admin']],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null, isAdmin: false);

            $labels = array_column($menu, 'label');
            expect($labels)->toContain('Public')
                ->and($labels)->not->toContain('Admin Only');
        });

        it('includes admin items when user is admin', function () {
            $registry = createRegistry();
            $workspace = createMockWorkspace(1, 'system');
            $provider = createMockProvider([
                ['group' => 'admin', 'priority' => 10, 'admin' => true, 'item' => fn () => ['label' => 'Admin Panel', 'icon' => 'crown', 'href' => '/admin']],
            ]);

            $registry->register($provider);
            $menu = $registry->build($workspace, isAdmin: true);

            // Admin group becomes a dropdown
            $adminDropdown = collect($menu)->first(fn ($item) => ($item['label'] ?? null) === 'Admin');

            expect($adminDropdown)->not->toBeNull()
                ->and($adminDropdown['children'])->toHaveCount(1);
        });

        it('respects provider-level permissions', function () {
            $registry = createRegistry();
            $user = createMockUser(1, []);

            // Provider that denies menu viewing
            $provider = createMockProvider(
                items: [['group' => 'dashboard', 'priority' => 10, 'item' => fn () => ['label' => 'Hidden', 'icon' => 'lock', 'href' => '/hidden']]],
                permissions: [],
                canView: false
            );

            $registry->register($provider);
            $menu = $registry->build(null, isAdmin: false, user: $user);

            expect($menu)->toBeEmpty();
        });

        it('respects item-level permissions', function () {
            $registry = createRegistry();
            $user = createMockUser(1, ['view.public']); // Only has public permission

            $provider = createMockProvider([
                [
                    'group' => 'dashboard',
                    'priority' => 10,
                    'permissions' => ['view.public'],
                    'item' => fn () => ['label' => 'Public Page', 'icon' => 'globe', 'href' => '/public'],
                ],
                [
                    'group' => 'dashboard',
                    'priority' => 20,
                    'permissions' => ['view.secret'],
                    'item' => fn () => ['label' => 'Secret Page', 'icon' => 'lock', 'href' => '/secret'],
                ],
            ]);

            $registry->register($provider);
            $menu = $registry->build(null, isAdmin: false, user: $user);

            $labels = array_column($menu, 'label');
            expect($labels)->toContain('Public Page')
                ->and($labels)->not->toContain('Secret Page');
        });
    });
});

// =============================================================================
// MenuItemBuilder Tests
// =============================================================================

describe('MenuItemBuilder', function () {
    describe('basic construction', function () {
        it('creates item with label', function () {
            $builder = MenuItemBuilder::make('Dashboard');

            expect($builder->getLabel())->toBe('Dashboard');
        });

        it('creates item with icon', function () {
            $item = MenuItemBuilder::make('Dashboard')
                ->icon('home')
                ->href('/')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['icon'])->toBe('home');
        });

        it('creates item with href', function () {
            $item = MenuItemBuilder::make('Dashboard')
                ->href('/dashboard')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['href'])->toBe('/dashboard');
        });

        it('defaults href to # when not specified', function () {
            $item = MenuItemBuilder::make('Dashboard')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['href'])->toBe('#');
        });
    });

    describe('groups', function () {
        it('defaults to services group', function () {
            $item = MenuItemBuilder::make('Test')
                ->build();

            expect($item['group'])->toBe('services');
        });

        it('sets group with inGroup()', function () {
            $item = MenuItemBuilder::make('Test')
                ->inGroup('settings')
                ->build();

            expect($item['group'])->toBe('settings');
        });

        it('sets dashboard group with inDashboard()', function () {
            $item = MenuItemBuilder::make('Test')
                ->inDashboard()
                ->build();

            expect($item['group'])->toBe('dashboard');
        });

        it('sets workspaces group with inWorkspaces()', function () {
            $item = MenuItemBuilder::make('Test')
                ->inWorkspaces()
                ->build();

            expect($item['group'])->toBe('workspaces');
        });

        it('sets settings group with inSettings()', function () {
            $item = MenuItemBuilder::make('Test')
                ->inSettings()
                ->build();

            expect($item['group'])->toBe('settings');
        });

        it('sets admin group with inAdmin()', function () {
            $item = MenuItemBuilder::make('Test')
                ->inAdmin()
                ->build();

            expect($item['group'])->toBe('admin');
        });
    });

    describe('priority', function () {
        it('defaults to PRIORITY_NORMAL (50)', function () {
            $item = MenuItemBuilder::make('Test')
                ->build();

            expect($item['priority'])->toBe(AdminMenuProvider::PRIORITY_NORMAL);
        });

        it('sets priority with withPriority()', function () {
            $item = MenuItemBuilder::make('Test')
                ->withPriority(AdminMenuProvider::PRIORITY_HIGH)
                ->build();

            expect($item['priority'])->toBe(AdminMenuProvider::PRIORITY_HIGH);
        });

        it('sets priority with priority() alias', function () {
            $item = MenuItemBuilder::make('Test')
                ->priority(25)
                ->build();

            expect($item['priority'])->toBe(25);
        });

        it('sets highest priority with first()', function () {
            $item = MenuItemBuilder::make('Test')
                ->first()
                ->build();

            expect($item['priority'])->toBe(AdminMenuProvider::PRIORITY_FIRST);
        });

        it('sets high priority with high()', function () {
            $item = MenuItemBuilder::make('Test')
                ->high()
                ->build();

            expect($item['priority'])->toBe(AdminMenuProvider::PRIORITY_HIGH);
        });

        it('sets low priority with low()', function () {
            $item = MenuItemBuilder::make('Test')
                ->low()
                ->build();

            expect($item['priority'])->toBe(AdminMenuProvider::PRIORITY_LOW);
        });

        it('sets lowest priority with last()', function () {
            $item = MenuItemBuilder::make('Test')
                ->last()
                ->build();

            expect($item['priority'])->toBe(AdminMenuProvider::PRIORITY_LAST);
        });
    });

    describe('badges', function () {
        it('sets text badge', function () {
            $item = MenuItemBuilder::make('Messages')
                ->badge('New')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['badge'])->toBe('New');
        });

        it('sets badge with colour', function () {
            $item = MenuItemBuilder::make('Messages')
                ->badge('3', 'red')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['badge'])->toBe(['text' => '3', 'color' => 'red']);
        });

        it('sets numeric badge with badgeCount()', function () {
            $item = MenuItemBuilder::make('Notifications')
                ->badgeCount(42)
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['badge'])->toBe('42');
        });

        it('sets badge config with badgeConfig()', function () {
            $item = MenuItemBuilder::make('Tasks')
                ->badgeConfig(['text' => '5', 'color' => 'amber', 'tooltip' => 'Pending tasks'])
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['badge'])->toBe(['text' => '5', 'color' => 'amber', 'tooltip' => 'Pending tasks']);
        });
    });

    describe('colour', function () {
        it('sets colour theme', function () {
            $item = MenuItemBuilder::make('Settings')
                ->color('blue')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['color'])->toBe('blue');
        });
    });

    describe('authorization', function () {
        it('sets entitlement requirement', function () {
            $item = MenuItemBuilder::make('Commerce')
                ->entitlement('core.srv.commerce')
                ->build();

            expect($item['entitlement'])->toBe('core.srv.commerce');
        });

        it('sets entitlement with requiresEntitlement() alias', function () {
            $item = MenuItemBuilder::make('Bio')
                ->requiresEntitlement('core.srv.bio')
                ->build();

            expect($item['entitlement'])->toBe('core.srv.bio');
        });

        it('sets permissions array', function () {
            $item = MenuItemBuilder::make('Users')
                ->permissions(['users.view', 'users.edit'])
                ->build();

            expect($item['permissions'])->toBe(['users.view', 'users.edit']);
        });

        it('adds single permission', function () {
            $item = MenuItemBuilder::make('Posts')
                ->permission('posts.view')
                ->permission('posts.create')
                ->build();

            expect($item['permissions'])->toBe(['posts.view', 'posts.create']);
        });

        it('sets admin requirement', function () {
            $item = MenuItemBuilder::make('Platform')
                ->requireAdmin()
                ->build();

            expect($item['admin'])->toBeTrue();
        });

        it('sets admin requirement with adminOnly() alias', function () {
            $item = MenuItemBuilder::make('System')
                ->adminOnly()
                ->build();

            expect($item['admin'])->toBeTrue();
        });
    });

    describe('active state', function () {
        it('sets active state explicitly', function () {
            $item = MenuItemBuilder::make('Current Page')
                ->active(true)
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['active'])->toBeTrue();
        });

        it('defaults active to false', function () {
            $item = MenuItemBuilder::make('Other Page')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['active'])->toBeFalse();
        });

        it('evaluates active callback', function () {
            $item = MenuItemBuilder::make('Dynamic')
                ->activeWhen(fn () => true)
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['active'])->toBeTrue();
        });
    });

    describe('children', function () {
        it('sets children array', function () {
            $item = MenuItemBuilder::make('Parent')
                ->children([
                    MenuItemBuilder::child('Child 1', '/child-1'),
                    MenuItemBuilder::child('Child 2', '/child-2'),
                ])
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['children'])->toHaveCount(2);
        });

        it('adds single child', function () {
            $item = MenuItemBuilder::make('Parent')
                ->addChild(MenuItemBuilder::child('Child', '/child'))
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['children'])->toHaveCount(1);
        });

        it('adds separator to children', function () {
            $item = MenuItemBuilder::make('Parent')
                ->addChild(MenuItemBuilder::child('Child 1', '/child-1'))
                ->separator()
                ->addChild(MenuItemBuilder::child('Child 2', '/child-2'))
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['children'])->toHaveCount(3)
                ->and($evaluated['children'][1])->toBe(['separator' => true]);
        });

        it('adds section header to children', function () {
            $item = MenuItemBuilder::make('Parent')
                ->section('Products', 'cube')
                ->addChild(MenuItemBuilder::child('All Products', '/products'))
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['children'][0])->toBe(['section' => 'Products', 'icon' => 'cube']);
        });

        it('adds divider to children', function () {
            $item = MenuItemBuilder::make('Parent')
                ->addChild(MenuItemBuilder::child('Child 1', '/child-1'))
                ->divider('More')
                ->addChild(MenuItemBuilder::child('Child 2', '/child-2'))
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['children'][1])->toBe(['divider' => true, 'label' => 'More']);
        });

        it('creates child item with child() factory', function () {
            $child = MenuItemBuilder::child('Products', '/products')
                ->icon('cube')
                ->active(true)
                ->buildChildItem();

            expect($child['label'])->toBe('Products')
                ->and($child['href'])->toBe('/products')
                ->and($child['icon'])->toBe('cube')
                ->and($child['active'])->toBeTrue();
        });
    });

    describe('service key', function () {
        it('sets service key', function () {
            $item = MenuItemBuilder::make('Commerce')
                ->service('commerce')
                ->build();

            expect($item['service'])->toBe('commerce');
        });
    });

    describe('custom attributes', function () {
        it('sets single custom attribute', function () {
            $item = MenuItemBuilder::make('Test')
                ->with('data-testid', 'menu-item')
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['data-testid'])->toBe('menu-item');
        });

        it('sets multiple custom attributes', function () {
            $item = MenuItemBuilder::make('Test')
                ->withAttributes(['data-foo' => 'bar', 'data-baz' => 'qux'])
                ->build();

            $evaluated = ($item['item'])();
            expect($evaluated['data-foo'])->toBe('bar')
                ->and($evaluated['data-baz'])->toBe('qux');
        });
    });
});

// =============================================================================
// MenuItemGroup Tests
// =============================================================================

describe('MenuItemGroup', function () {
    it('creates separator', function () {
        $separator = MenuItemGroup::separator();

        expect($separator)->toBe(['separator' => true]);
    });

    it('creates header with label only', function () {
        $header = MenuItemGroup::header('Products');

        expect($header)->toBe(['section' => 'Products']);
    });

    it('creates header with icon', function () {
        $header = MenuItemGroup::header('Products', 'cube');

        expect($header)->toBe(['section' => 'Products', 'icon' => 'cube']);
    });

    it('creates header with colour', function () {
        $header = MenuItemGroup::header('Orders', 'receipt', 'blue');

        expect($header)->toBe(['section' => 'Orders', 'icon' => 'receipt', 'color' => 'blue']);
    });

    it('creates header with badge', function () {
        $header = MenuItemGroup::header('Tasks', 'check', null, '5');

        expect($header)->toBe(['section' => 'Tasks', 'icon' => 'check', 'badge' => '5']);
    });

    it('creates divider without label', function () {
        $divider = MenuItemGroup::divider();

        expect($divider)->toBe(['divider' => true]);
    });

    it('creates divider with label', function () {
        $divider = MenuItemGroup::divider('More Options');

        expect($divider)->toBe(['divider' => true, 'label' => 'More Options']);
    });

    it('creates collapsible group', function () {
        $children = [
            ['label' => 'Item 1', 'href' => '/item-1'],
            ['label' => 'Item 2', 'href' => '/item-2'],
        ];

        $collapsible = MenuItemGroup::collapsible('Advanced', $children, 'gear', 'slate', false);

        expect($collapsible['collapsible'])->toBeTrue()
            ->and($collapsible['label'])->toBe('Advanced')
            ->and($collapsible['children'])->toBe($children)
            ->and($collapsible['icon'])->toBe('gear')
            ->and($collapsible['color'])->toBe('slate')
            ->and($collapsible['open'])->toBeFalse();
    });

    it('creates collapsible with state persistence', function () {
        $collapsible = MenuItemGroup::collapsible('Settings', [], null, null, true, 'menu.settings.open');

        expect($collapsible['stateKey'])->toBe('menu.settings.open');
    });

    describe('type detection', function () {
        it('detects separator', function () {
            expect(MenuItemGroup::isSeparator(['separator' => true]))->toBeTrue()
                ->and(MenuItemGroup::isSeparator(['label' => 'Test']))->toBeFalse();
        });

        it('detects header', function () {
            expect(MenuItemGroup::isHeader(['section' => 'Products']))->toBeTrue()
                ->and(MenuItemGroup::isHeader(['label' => 'Test']))->toBeFalse();
        });

        it('detects collapsible', function () {
            expect(MenuItemGroup::isCollapsible(['collapsible' => true]))->toBeTrue()
                ->and(MenuItemGroup::isCollapsible(['label' => 'Test']))->toBeFalse();
        });

        it('detects divider', function () {
            expect(MenuItemGroup::isDivider(['divider' => true]))->toBeTrue()
                ->and(MenuItemGroup::isDivider(['label' => 'Test']))->toBeFalse();
        });

        it('detects structural elements', function () {
            expect(MenuItemGroup::isStructural(['separator' => true]))->toBeTrue()
                ->and(MenuItemGroup::isStructural(['section' => 'Test']))->toBeTrue()
                ->and(MenuItemGroup::isStructural(['divider' => true]))->toBeTrue()
                ->and(MenuItemGroup::isStructural(['collapsible' => true]))->toBeTrue()
                ->and(MenuItemGroup::isStructural(['label' => 'Test']))->toBeFalse();
        });

        it('detects links', function () {
            expect(MenuItemGroup::isLink(['label' => 'Test', 'href' => '/test']))->toBeTrue()
                ->and(MenuItemGroup::isLink(['separator' => true]))->toBeFalse()
                ->and(MenuItemGroup::isLink(['section' => 'Test']))->toBeFalse();
        });
    });
});

// =============================================================================
// IconValidator Tests
// =============================================================================

describe('IconValidator', function () {
    describe('validation', function () {
        it('validates known solid icons', function () {
            $validator = new IconValidator;

            expect($validator->isValid('home'))->toBeTrue()
                ->and($validator->isValid('user'))->toBeTrue()
                ->and($validator->isValid('gear'))->toBeTrue()
                ->and($validator->isValid('cog'))->toBeTrue();
        });

        it('validates known brand icons', function () {
            $validator = new IconValidator;

            expect($validator->isValid('github'))->toBeTrue()
                ->and($validator->isValid('twitter'))->toBeTrue()
                ->and($validator->isValid('facebook'))->toBeTrue();
        });

        it('normalises full FontAwesome class names', function () {
            $validator = new IconValidator;

            expect($validator->isValid('fas fa-home'))->toBeTrue()
                ->and($validator->isValid('fa-solid fa-user'))->toBeTrue()
                ->and($validator->isValid('fab fa-github'))->toBeTrue()
                ->and($validator->isValid('fa-brands fa-twitter'))->toBeTrue();
        });

        it('normalises fa- prefix', function () {
            $validator = new IconValidator;

            expect($validator->normalizeIcon('fa-home'))->toBe('home')
                ->and($validator->normalizeIcon('fa-user'))->toBe('user');
        });

        it('handles case insensitivity', function () {
            $validator = new IconValidator;

            expect($validator->normalizeIcon('HOME'))->toBe('home')
                ->and($validator->normalizeIcon('User'))->toBe('user');
        });

        it('returns errors for empty icon', function () {
            $validator = new IconValidator;
            $errors = $validator->validate('');

            expect($errors)->not->toBeEmpty()
                ->and($errors[0])->toBe('Icon name cannot be empty');
        });

        it('validates multiple icons at once', function () {
            $validator = new IconValidator;
            $validator->setStrictMode(true);

            $results = $validator->validateMany(['home', 'invalid-xyz-icon', 'user']);

            expect($results)->toHaveKey('invalid-xyz-icon')
                ->and($results)->not->toHaveKey('home')
                ->and($results)->not->toHaveKey('user');
        });
    });

    describe('custom icons', function () {
        it('allows adding custom icons', function () {
            $validator = new IconValidator;
            $validator->setStrictMode(true);
            $validator->addCustomIcon('my-custom-icon');

            expect($validator->isValid('my-custom-icon'))->toBeTrue();
        });

        it('allows adding multiple custom icons', function () {
            $validator = new IconValidator;
            $validator->setStrictMode(true);
            $validator->addCustomIcons(['icon-one', 'icon-two']);

            expect($validator->isValid('icon-one'))->toBeTrue()
                ->and($validator->isValid('icon-two'))->toBeTrue();
        });

        it('returns custom icons', function () {
            $validator = new IconValidator;
            $validator->addCustomIcon('custom-test');

            expect($validator->getCustomIcons())->toContain('custom-test');
        });
    });

    describe('icon packs', function () {
        it('allows registering icon packs', function () {
            $validator = new IconValidator;
            $validator->setStrictMode(true);
            $validator->registerIconPack('mypack', ['pack-icon-1', 'pack-icon-2']);

            expect($validator->isValid('pack-icon-1'))->toBeTrue()
                ->and($validator->isValid('pack-icon-2'))->toBeTrue();
        });
    });

    describe('suggestions', function () {
        it('suggests similar icons for typos', function () {
            $validator = new IconValidator;
            $suggestions = $validator->getSuggestions('hone', 3); // typo for 'home'

            expect($suggestions)->toContain('home');
        });

        it('limits number of suggestions', function () {
            $validator = new IconValidator;
            $suggestions = $validator->getSuggestions('us', 3);

            expect(count($suggestions))->toBeLessThanOrEqual(3);
        });
    });

    describe('icon lists', function () {
        it('returns solid icons', function () {
            $validator = new IconValidator;
            $icons = $validator->getSolidIcons();

            expect($icons)->toBeArray()
                ->and($icons)->toContain('home')
                ->and($icons)->toContain('user')
                ->and($icons)->toContain('gear');
        });

        it('returns brand icons', function () {
            $validator = new IconValidator;
            $icons = $validator->getBrandIcons();

            expect($icons)->toBeArray()
                ->and($icons)->toContain('github')
                ->and($icons)->toContain('twitter');
        });
    });

    describe('strict mode', function () {
        it('allows unknown icons in non-strict mode (default)', function () {
            $validator = new IconValidator;
            $validator->setStrictMode(false);

            expect($validator->isValid('completely-unknown-icon'))->toBeTrue();
        });

        it('rejects unknown icons in strict mode', function () {
            $validator = new IconValidator;
            $validator->setStrictMode(true);

            expect($validator->isValid('completely-unknown-icon'))->toBeFalse();
        });
    });
});

// =============================================================================
// Integration Tests
// =============================================================================

describe('Admin Menu System Integration', function () {
    it('builds complete menu with multiple providers using MenuItemBuilder', function () {
        $registry = createRegistry();

        // Provider 1: Dashboard items
        $dashboardItems = [
            MenuItemBuilder::make('Dashboard')
                ->icon('home')
                ->href('/dashboard')
                ->inDashboard()
                ->first()
                ->active(true)
                ->build(),
        ];

        // Provider 2: Service items with badges
        $serviceItems = [
            MenuItemBuilder::make('Commerce')
                ->icon('cart-shopping')
                ->href('/commerce')
                ->inServices()
                ->entitlement('core.srv.commerce')
                ->badge('3', 'red')
                ->children([
                    MenuItemGroup::header('Products', 'cube'),
                    MenuItemBuilder::child('All Products', '/commerce/products')->icon('list'),
                    MenuItemBuilder::child('Categories', '/commerce/categories')->icon('folder'),
                    MenuItemGroup::separator(),
                    MenuItemGroup::header('Orders', 'receipt'),
                    MenuItemBuilder::child('All Orders', '/commerce/orders')->icon('file-lines'),
                ])
                ->build(),
        ];

        // Provider 3: Settings items
        $settingsItems = [
            MenuItemBuilder::make('Profile')
                ->icon('user')
                ->href('/profile')
                ->inSettings()
                ->priority(10)
                ->build(),
            MenuItemBuilder::make('Security')
                ->icon('lock')
                ->href('/security')
                ->inSettings()
                ->priority(20)
                ->permissions(['settings.security'])
                ->build(),
        ];

        $registry->register(createMockProvider($dashboardItems));
        $registry->register(createMockProvider($serviceItems));
        $registry->register(createMockProvider($settingsItems));

        $user = createMockUser(1, ['settings.security']);
        $menu = $registry->build(null, isAdmin: false, user: $user);

        // Verify structure
        expect($menu)->not->toBeEmpty();

        // Dashboard should be first (standalone group)
        expect($menu[0]['label'])->toBe('Dashboard')
            ->and($menu[0]['active'])->toBeTrue();

        // Should have dividers between groups
        $dividers = collect($menu)->filter(fn ($item) => isset($item['divider']));
        expect($dividers)->not->toBeEmpty();

        // Settings should be a dropdown with children
        $settingsDropdown = collect($menu)->first(fn ($item) => ($item['label'] ?? null) === 'Account');
        expect($settingsDropdown)->not->toBeNull()
            ->and($settingsDropdown['children'])->toHaveCount(2);
    });
});
