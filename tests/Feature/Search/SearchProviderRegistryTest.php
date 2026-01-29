<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

use Core\Admin\Search\Concerns\HasSearchProvider;
use Core\Admin\Search\Contracts\SearchProvider;
use Core\Admin\Search\SearchProviderRegistry;
use Core\Admin\Search\SearchResult;
use Illuminate\Support\Collection;

/**
 * Tests for the search provider registry.
 *
 * These tests verify the complete search system including:
 * - SearchProviderRegistry with multiple providers
 * - Search execution and result aggregation
 * - Fuzzy matching and relevance scoring
 * - Provider availability filtering
 * - Result flattening for keyboard navigation
 */

// =============================================================================
// Helper Functions
// =============================================================================

/**
 * Create a mock search provider for testing.
 *
 * @param  string  $type  The search type identifier
 * @param  string  $label  The display label
 * @param  string  $icon  The icon name
 * @param  array<SearchResult|array>  $results  Results to return from search
 * @param  bool  $available  Whether provider is available
 * @param  int  $priority  Provider priority (lower = higher priority)
 */
function createMockSearchProvider(
    string $type,
    string $label,
    string $icon,
    array $results = [],
    bool $available = true,
    int $priority = 50
): SearchProvider {
    return new class($type, $label, $icon, $results, $available, $priority) implements SearchProvider
    {
        use HasSearchProvider;

        public function __construct(
            protected string $type,
            protected string $label,
            protected string $icon,
            protected array $results,
            protected bool $available,
            protected int $priority
        ) {}

        public function searchType(): string
        {
            return $this->type;
        }

        public function searchLabel(): string
        {
            return $this->label;
        }

        public function searchIcon(): string
        {
            return $this->icon;
        }

        public function search(string $query, int $limit = 5): Collection
        {
            return collect($this->results)->take($limit);
        }

        public function getUrl(mixed $result): string
        {
            if ($result instanceof SearchResult) {
                return $result->url;
            }

            return $result['url'] ?? '#';
        }

        public function searchPriority(): int
        {
            return $this->priority;
        }

        public function isAvailable(?object $user, ?object $workspace): bool
        {
            return $this->available;
        }
    };
}

/**
 * Create a mock user object for testing.
 */
function createMockSearchUser(int $id = 1): object
{
    return new class($id)
    {
        public function __construct(public int $id) {}
    };
}

/**
 * Create a mock workspace object for testing.
 */
function createMockSearchWorkspace(int $id = 1, string $slug = 'test-workspace'): object
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
function createSearchRegistry(): SearchProviderRegistry
{
    return new SearchProviderRegistry;
}

// =============================================================================
// Provider Registration Tests
// =============================================================================

describe('SearchProviderRegistry', function () {
    describe('provider registration', function () {
        it('returns empty array when no providers registered', function () {
            $registry = createSearchRegistry();

            expect($registry->providers())->toBeArray()
                ->and($registry->providers())->toBeEmpty();
        });

        it('registers single provider', function () {
            $registry = createSearchRegistry();
            $provider = createMockSearchProvider('pages', 'Pages', 'document');

            $registry->register($provider);

            expect($registry->providers())->toHaveCount(1);
        });

        it('registers multiple providers individually', function () {
            $registry = createSearchRegistry();

            $provider1 = createMockSearchProvider('pages', 'Pages', 'document');
            $provider2 = createMockSearchProvider('users', 'Users', 'user');

            $registry->register($provider1);
            $registry->register($provider2);

            expect($registry->providers())->toHaveCount(2);
        });

        it('registers multiple providers at once with registerMany', function () {
            $registry = createSearchRegistry();

            $providers = [
                createMockSearchProvider('pages', 'Pages', 'document'),
                createMockSearchProvider('users', 'Users', 'user'),
                createMockSearchProvider('posts', 'Posts', 'newspaper'),
            ];

            $registry->registerMany($providers);

            expect($registry->providers())->toHaveCount(3);
        });
    });

    // =============================================================================
    // Provider Availability Tests
    // =============================================================================

    describe('provider availability', function () {
        it('returns all providers when all are available', function () {
            $registry = createSearchRegistry();

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', [], true));
            $registry->register(createMockSearchProvider('users', 'Users', 'user', [], true));

            $available = $registry->availableProviders(null, null);

            expect($available)->toHaveCount(2);
        });

        it('filters out unavailable providers', function () {
            $registry = createSearchRegistry();

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', [], true));
            $registry->register(createMockSearchProvider('admin', 'Admin', 'shield', [], false));

            $available = $registry->availableProviders(null, null);

            expect($available)->toHaveCount(1);
        });

        it('returns empty collection when no providers are available', function () {
            $registry = createSearchRegistry();

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', [], false));
            $registry->register(createMockSearchProvider('users', 'Users', 'user', [], false));

            $available = $registry->availableProviders(null, null);

            expect($available)->toBeEmpty();
        });

        it('sorts available providers by priority', function () {
            $registry = createSearchRegistry();

            $registry->register(createMockSearchProvider('low', 'Low Priority', 'down', [], true, 100));
            $registry->register(createMockSearchProvider('high', 'High Priority', 'up', [], true, 10));
            $registry->register(createMockSearchProvider('medium', 'Medium Priority', 'minus', [], true, 50));

            $available = $registry->availableProviders(null, null);
            $types = $available->map(fn ($p) => $p->searchType())->values()->all();

            expect($types)->toBe(['high', 'medium', 'low']);
        });

        it('passes user and workspace to provider availability check', function () {
            $registry = createSearchRegistry();
            $user = createMockSearchUser(1);
            $workspace = createMockSearchWorkspace(1, 'test');

            // Create a provider that checks user/workspace
            $provider = new class implements SearchProvider
            {
                use HasSearchProvider;

                public ?object $receivedUser = null;

                public ?object $receivedWorkspace = null;

                public function searchType(): string
                {
                    return 'test';
                }

                public function searchLabel(): string
                {
                    return 'Test';
                }

                public function searchIcon(): string
                {
                    return 'test';
                }

                public function search(string $query, int $limit = 5): Collection
                {
                    return collect();
                }

                public function getUrl(mixed $result): string
                {
                    return '#';
                }

                public function isAvailable(?object $user, ?object $workspace): bool
                {
                    $this->receivedUser = $user;
                    $this->receivedWorkspace = $workspace;

                    return true;
                }
            };

            $registry->register($provider);
            $registry->availableProviders($user, $workspace);

            expect($provider->receivedUser)->toBe($user)
                ->and($provider->receivedWorkspace)->toBe($workspace);
        });
    });

    // =============================================================================
    // Search Execution Tests
    // =============================================================================

    describe('search execution', function () {
        it('returns empty array when no providers registered', function () {
            $registry = createSearchRegistry();

            $results = $registry->search('test', null, null);

            expect($results)->toBeArray()
                ->and($results)->toBeEmpty();
        });

        it('returns empty array when no providers are available', function () {
            $registry = createSearchRegistry();
            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', [], false));

            $results = $registry->search('test', null, null);

            expect($results)->toBeEmpty();
        });

        it('returns grouped results by search type', function () {
            $registry = createSearchRegistry();

            $pageResults = [
                new SearchResult('1', 'Dashboard', '/hub', 'pages', 'house', 'Overview'),
                new SearchResult('2', 'Settings', '/hub/settings', 'pages', 'gear', 'Preferences'),
            ];

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', $pageResults));

            $results = $registry->search('test', null, null);

            expect($results)->toHaveKey('pages')
                ->and($results['pages']['label'])->toBe('Pages')
                ->and($results['pages']['icon'])->toBe('document')
                ->and($results['pages']['results'])->toHaveCount(2);
        });

        it('aggregates results from multiple providers', function () {
            $registry = createSearchRegistry();

            $pageResults = [
                new SearchResult('1', 'Dashboard', '/hub', 'pages', 'house'),
            ];
            $userResults = [
                new SearchResult('2', 'John Doe', '/users/1', 'users', 'user'),
            ];

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', $pageResults));
            $registry->register(createMockSearchProvider('users', 'Users', 'user', $userResults));

            $results = $registry->search('test', null, null);

            expect($results)->toHaveKey('pages')
                ->and($results)->toHaveKey('users');
        });

        it('respects limit per provider', function () {
            $registry = createSearchRegistry();

            $manyResults = [];
            for ($i = 1; $i <= 10; $i++) {
                $manyResults[] = new SearchResult((string) $i, "Result {$i}", "/result/{$i}", 'pages', 'document');
            }

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', $manyResults));

            $results = $registry->search('test', null, null, limitPerProvider: 3);

            expect($results['pages']['results'])->toHaveCount(3);
        });

        it('handles SearchResult objects correctly', function () {
            $registry = createSearchRegistry();

            $results = [
                new SearchResult(
                    id: 'test-1',
                    title: 'Test Result',
                    url: '/test',
                    type: 'pages',
                    icon: 'custom-icon',
                    subtitle: 'A test result',
                    meta: ['key' => 'value']
                ),
            ];

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', $results));

            $searchResults = $registry->search('test', null, null);

            $firstResult = $searchResults['pages']['results'][0];
            expect($firstResult['id'])->toBe('test-1')
                ->and($firstResult['title'])->toBe('Test Result')
                ->and($firstResult['url'])->toBe('/test')
                ->and($firstResult['subtitle'])->toBe('A test result')
                ->and($firstResult['meta'])->toBe(['key' => 'value']);
        });

        it('handles array results correctly', function () {
            $registry = createSearchRegistry();

            $results = [
                [
                    'id' => 'arr-1',
                    'title' => 'Array Result',
                    'url' => '/array',
                    'subtitle' => 'From array',
                ],
            ];

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', $results));

            $searchResults = $registry->search('test', null, null);

            $firstResult = $searchResults['pages']['results'][0];
            expect($firstResult['id'])->toBe('arr-1')
                ->and($firstResult['title'])->toBe('Array Result')
                ->and($firstResult['type'])->toBe('pages')
                ->and($firstResult['icon'])->toBe('document');
        });

        it('handles model-like objects with id and title properties', function () {
            $registry = createSearchRegistry();

            $modelResult = new class
            {
                public string $id = 'model-1';

                public string $title = 'Model Title';

                public string $description = 'Model Description';
            };

            // Create a provider that returns model objects
            $provider = new class($modelResult) implements SearchProvider
            {
                use HasSearchProvider;

                public function __construct(private object $model) {}

                public function searchType(): string
                {
                    return 'models';
                }

                public function searchLabel(): string
                {
                    return 'Models';
                }

                public function searchIcon(): string
                {
                    return 'cube';
                }

                public function search(string $query, int $limit = 5): Collection
                {
                    return collect([$this->model]);
                }

                public function getUrl(mixed $result): string
                {
                    return '/models/'.$result->id;
                }
            };

            $registry->register($provider);

            $searchResults = $registry->search('test', null, null);

            $firstResult = $searchResults['models']['results'][0];
            expect($firstResult['id'])->toBe('model-1')
                ->and($firstResult['title'])->toBe('Model Title')
                ->and($firstResult['subtitle'])->toBe('Model Description')
                ->and($firstResult['url'])->toBe('/models/model-1');
        });

        it('skips providers with empty results', function () {
            $registry = createSearchRegistry();

            $registry->register(createMockSearchProvider('pages', 'Pages', 'document', []));
            $registry->register(createMockSearchProvider('users', 'Users', 'user', [
                new SearchResult('1', 'User', '/user', 'users', 'user'),
            ]));

            $results = $registry->search('test', null, null);

            expect($results)->not->toHaveKey('pages')
                ->and($results)->toHaveKey('users');
        });
    });

    // =============================================================================
    // Result Flattening Tests
    // =============================================================================

    describe('result flattening', function () {
        it('flattens empty grouped results to empty array', function () {
            $registry = createSearchRegistry();

            $flat = $registry->flattenResults([]);

            expect($flat)->toBeArray()
                ->and($flat)->toBeEmpty();
        });

        it('flattens single group results', function () {
            $registry = createSearchRegistry();

            $grouped = [
                'pages' => [
                    'label' => 'Pages',
                    'icon' => 'document',
                    'results' => [
                        ['id' => '1', 'title' => 'Dashboard'],
                        ['id' => '2', 'title' => 'Settings'],
                    ],
                ],
            ];

            $flat = $registry->flattenResults($grouped);

            expect($flat)->toHaveCount(2)
                ->and($flat[0]['title'])->toBe('Dashboard')
                ->and($flat[1]['title'])->toBe('Settings');
        });

        it('flattens multiple group results in order', function () {
            $registry = createSearchRegistry();

            $grouped = [
                'pages' => [
                    'label' => 'Pages',
                    'icon' => 'document',
                    'results' => [
                        ['id' => '1', 'title' => 'Dashboard'],
                        ['id' => '2', 'title' => 'Settings'],
                    ],
                ],
                'users' => [
                    'label' => 'Users',
                    'icon' => 'user',
                    'results' => [
                        ['id' => '3', 'title' => 'Admin'],
                        ['id' => '4', 'title' => 'Editor'],
                    ],
                ],
            ];

            $flat = $registry->flattenResults($grouped);

            expect($flat)->toHaveCount(4)
                ->and($flat[0]['title'])->toBe('Dashboard')
                ->and($flat[1]['title'])->toBe('Settings')
                ->and($flat[2]['title'])->toBe('Admin')
                ->and($flat[3]['title'])->toBe('Editor');
        });

        it('preserves all result properties when flattening', function () {
            $registry = createSearchRegistry();

            $grouped = [
                'pages' => [
                    'label' => 'Pages',
                    'icon' => 'document',
                    'results' => [
                        [
                            'id' => '1',
                            'title' => 'Dashboard',
                            'subtitle' => 'Overview',
                            'url' => '/hub',
                            'type' => 'pages',
                            'icon' => 'house',
                            'meta' => ['featured' => true],
                        ],
                    ],
                ],
            ];

            $flat = $registry->flattenResults($grouped);

            expect($flat[0])->toBe([
                'id' => '1',
                'title' => 'Dashboard',
                'subtitle' => 'Overview',
                'url' => '/hub',
                'type' => 'pages',
                'icon' => 'house',
                'meta' => ['featured' => true],
            ]);
        });
    });

    // =============================================================================
    // Fuzzy Matching Tests
    // =============================================================================

    describe('fuzzy matching', function () {
        it('matches direct substring', function () {
            $registry = createSearchRegistry();

            expect($registry->fuzzyMatch('dash', 'Dashboard'))->toBeTrue()
                ->and($registry->fuzzyMatch('board', 'Dashboard'))->toBeTrue()
                ->and($registry->fuzzyMatch('settings', 'Account Settings'))->toBeTrue();
        });

        it('matches case insensitively', function () {
            $registry = createSearchRegistry();

            expect($registry->fuzzyMatch('DASH', 'dashboard'))->toBeTrue()
                ->and($registry->fuzzyMatch('Dashboard', 'DASHBOARD'))->toBeTrue()
                ->and($registry->fuzzyMatch('sEtTiNgS', 'Settings'))->toBeTrue();
        });

        it('matches word-start abbreviations', function () {
            $registry = createSearchRegistry();

            // "gs" matches "Global Search" (G + S)
            expect($registry->fuzzyMatch('gs', 'Global Search'))->toBeTrue();

            // "ps" matches "Post Settings"
            expect($registry->fuzzyMatch('ps', 'Post Settings'))->toBeTrue();

            // "ul" matches "Usage Limits"
            expect($registry->fuzzyMatch('ul', 'Usage Limits'))->toBeTrue();
        });

        it('matches character-by-character abbreviations', function () {
            $registry = createSearchRegistry();

            // Characters appear in order
            expect($registry->fuzzyMatch('dbd', 'dashboard'))->toBeTrue()
                ->and($registry->fuzzyMatch('gsr', 'global search results'))->toBeTrue();
        });

        it('returns false for empty query', function () {
            $registry = createSearchRegistry();

            expect($registry->fuzzyMatch('', 'Dashboard'))->toBeFalse()
                ->and($registry->fuzzyMatch('   ', 'Dashboard'))->toBeFalse();
        });

        it('returns false for non-matching query', function () {
            $registry = createSearchRegistry();

            expect($registry->fuzzyMatch('xyz', 'Dashboard'))->toBeFalse()
                ->and($registry->fuzzyMatch('zzz', 'Settings'))->toBeFalse();
        });

        it('trims whitespace from query and target', function () {
            $registry = createSearchRegistry();

            expect($registry->fuzzyMatch('  dash  ', '  Dashboard  '))->toBeTrue();
        });
    });

    // =============================================================================
    // Relevance Scoring Tests
    // =============================================================================

    describe('relevance scoring', function () {
        it('scores exact match highest (100)', function () {
            $registry = createSearchRegistry();

            $score = $registry->relevanceScore('dashboard', 'dashboard');

            expect($score)->toBe(100);
        });

        it('scores starts-with second highest (90)', function () {
            $registry = createSearchRegistry();

            $score = $registry->relevanceScore('dash', 'dashboard');

            expect($score)->toBe(90);
        });

        it('scores whole-word match third highest (80)', function () {
            $registry = createSearchRegistry();

            $score = $registry->relevanceScore('search', 'global search results');

            expect($score)->toBe(80);
        });

        it('scores substring match fourth (70)', function () {
            $registry = createSearchRegistry();

            $score = $registry->relevanceScore('board', 'dashboard');

            expect($score)->toBe(70);
        });

        it('scores word-start match fifth (60)', function () {
            $registry = createSearchRegistry();

            $score = $registry->relevanceScore('gs', 'global search');

            expect($score)->toBe(60);
        });

        it('scores fuzzy match lowest (40)', function () {
            $registry = createSearchRegistry();

            // "gsr" fuzzy matches "global search results" but not as word-start
            $score = $registry->relevanceScore('gsr', 'global search results');

            expect($score)->toBe(40);
        });

        it('returns zero for no match', function () {
            $registry = createSearchRegistry();

            $score = $registry->relevanceScore('xyz', 'dashboard');

            expect($score)->toBe(0);
        });

        it('returns zero for empty query', function () {
            $registry = createSearchRegistry();

            expect($registry->relevanceScore('', 'dashboard'))->toBe(0)
                ->and($registry->relevanceScore('dash', ''))->toBe(0);
        });

        it('handles case insensitivity in scoring', function () {
            $registry = createSearchRegistry();

            expect($registry->relevanceScore('DASHBOARD', 'dashboard'))->toBe(100)
                ->and($registry->relevanceScore('Dashboard', 'DASHBOARD'))->toBe(100);
        });
    });
});

// =============================================================================
// SearchResult Tests
// =============================================================================

describe('SearchResult', function () {
    describe('construction', function () {
        it('creates result with all properties', function () {
            $result = new SearchResult(
                id: '123',
                title: 'Dashboard',
                url: '/hub',
                type: 'pages',
                icon: 'house',
                subtitle: 'Overview and quick actions',
                meta: ['key' => 'value'],
            );

            expect($result->id)->toBe('123')
                ->and($result->title)->toBe('Dashboard')
                ->and($result->url)->toBe('/hub')
                ->and($result->type)->toBe('pages')
                ->and($result->icon)->toBe('house')
                ->and($result->subtitle)->toBe('Overview and quick actions')
                ->and($result->meta)->toBe(['key' => 'value']);
        });

        it('allows null subtitle', function () {
            $result = new SearchResult(
                id: '1',
                title: 'Test',
                url: '/test',
                type: 'test',
                icon: 'test',
            );

            expect($result->subtitle)->toBeNull();
        });

        it('defaults meta to empty array', function () {
            $result = new SearchResult(
                id: '1',
                title: 'Test',
                url: '/test',
                type: 'test',
                icon: 'test',
            );

            expect($result->meta)->toBe([]);
        });
    });

    describe('fromArray factory', function () {
        it('creates result from complete array', function () {
            $data = [
                'id' => '456',
                'title' => 'Settings',
                'url' => '/hub/settings',
                'type' => 'pages',
                'icon' => 'gear',
                'subtitle' => 'Account settings',
                'meta' => ['order' => 2],
            ];

            $result = SearchResult::fromArray($data);

            expect($result->id)->toBe('456')
                ->and($result->title)->toBe('Settings')
                ->and($result->url)->toBe('/hub/settings')
                ->and($result->type)->toBe('pages')
                ->and($result->icon)->toBe('gear')
                ->and($result->subtitle)->toBe('Account settings')
                ->and($result->meta)->toBe(['order' => 2]);
        });

        it('generates ID when missing', function () {
            $result = SearchResult::fromArray(['title' => 'Test']);

            expect($result->id)->not->toBeEmpty();
        });

        it('uses sensible defaults for missing fields', function () {
            $result = SearchResult::fromArray(['title' => 'Minimal']);

            expect($result->title)->toBe('Minimal')
                ->and($result->url)->toBe('#')
                ->and($result->type)->toBe('unknown')
                ->and($result->icon)->toBe('document')
                ->and($result->subtitle)->toBeNull()
                ->and($result->meta)->toBe([]);
        });
    });

    describe('toArray conversion', function () {
        it('converts to array with all properties', function () {
            $result = new SearchResult(
                id: '789',
                title: 'Test',
                url: '/test',
                type: 'test',
                icon: 'test-icon',
                subtitle: 'Test subtitle',
                meta: ['foo' => 'bar'],
            );

            $array = $result->toArray();

            expect($array)->toBe([
                'id' => '789',
                'title' => 'Test',
                'subtitle' => 'Test subtitle',
                'url' => '/test',
                'type' => 'test',
                'icon' => 'test-icon',
                'meta' => ['foo' => 'bar'],
            ]);
        });
    });

    describe('JSON serialisation', function () {
        it('serialises to JSON correctly', function () {
            $result = new SearchResult(
                id: '1',
                title: 'JSON Test',
                url: '/json',
                type: 'json',
                icon: 'code',
            );

            $json = json_encode($result);
            $decoded = json_decode($json, true);

            expect($decoded['id'])->toBe('1')
                ->and($decoded['title'])->toBe('JSON Test')
                ->and($decoded['url'])->toBe('/json');
        });
    });

    describe('withTypeAndIcon', function () {
        it('creates new instance with updated type and icon', function () {
            $original = new SearchResult(
                id: '1',
                title: 'Test',
                url: '/test',
                type: 'old-type',
                icon: 'document',
            );

            $modified = $original->withTypeAndIcon('new-type', 'new-icon');

            // Original should be unchanged (immutable)
            expect($original->type)->toBe('old-type')
                ->and($original->icon)->toBe('document');

            // Modified should have new values
            expect($modified->type)->toBe('new-type')
                ->and($modified->icon)->toBe('new-icon');
        });

        it('preserves custom icon when not using default', function () {
            $original = new SearchResult(
                id: '1',
                title: 'Test',
                url: '/test',
                type: 'old-type',
                icon: 'custom-icon',
            );

            $modified = $original->withTypeAndIcon('new-type', 'fallback-icon');

            // Should keep the custom icon, not use the fallback
            expect($modified->icon)->toBe('custom-icon')
                ->and($modified->type)->toBe('new-type');
        });

        it('preserves all other properties', function () {
            $original = new SearchResult(
                id: 'original-id',
                title: 'Original Title',
                url: '/original',
                type: 'old',
                icon: 'document',
                subtitle: 'Original subtitle',
                meta: ['preserved' => true],
            );

            $modified = $original->withTypeAndIcon('new', 'new-icon');

            expect($modified->id)->toBe('original-id')
                ->and($modified->title)->toBe('Original Title')
                ->and($modified->url)->toBe('/original')
                ->and($modified->subtitle)->toBe('Original subtitle')
                ->and($modified->meta)->toBe(['preserved' => true]);
        });
    });
});

// =============================================================================
// Integration Tests
// =============================================================================

describe('Search System Integration', function () {
    it('performs end-to-end search with multiple providers', function () {
        $registry = createSearchRegistry();

        // Provider 1: Pages (high priority)
        $pageResults = [
            new SearchResult('page-1', 'Dashboard', '/hub', 'pages', 'house', 'Main dashboard'),
            new SearchResult('page-2', 'Dashboard Settings', '/hub/settings', 'pages', 'gear', 'Configure dashboard'),
        ];
        $registry->register(createMockSearchProvider('pages', 'Pages', 'rectangle-stack', $pageResults, true, 10));

        // Provider 2: Users (medium priority)
        $userResults = [
            new SearchResult('user-1', 'Admin Dashboard User', '/users/admin', 'users', 'user', 'Administrator'),
        ];
        $registry->register(createMockSearchProvider('users', 'Users', 'users', $userResults, true, 50));

        // Provider 3: Posts (low priority, unavailable)
        $postResults = [
            new SearchResult('post-1', 'Dashboard Guide', '/posts/guide', 'posts', 'newspaper'),
        ];
        $registry->register(createMockSearchProvider('posts', 'Posts', 'newspaper', $postResults, false, 100));

        // Execute search
        $user = createMockSearchUser(1);
        $workspace = createMockSearchWorkspace(1, 'test');
        $results = $registry->search('dashboard', $user, $workspace);

        // Verify structure
        expect($results)->toHaveKey('pages')
            ->and($results)->toHaveKey('users')
            ->and($results)->not->toHaveKey('posts'); // Unavailable provider excluded

        // Verify pages results
        expect($results['pages']['label'])->toBe('Pages')
            ->and($results['pages']['results'])->toHaveCount(2);

        // Verify users results
        expect($results['users']['label'])->toBe('Users')
            ->and($results['users']['results'])->toHaveCount(1);

        // Flatten for keyboard navigation
        $flat = $registry->flattenResults($results);
        expect($flat)->toHaveCount(3);
    });

    it('supports workspace-scoped search providers', function () {
        $registry = createSearchRegistry();

        // Provider that only works in specific workspace
        $provider = new class implements SearchProvider
        {
            use HasSearchProvider;

            public function searchType(): string
            {
                return 'workspace-items';
            }

            public function searchLabel(): string
            {
                return 'Workspace Items';
            }

            public function searchIcon(): string
            {
                return 'folder';
            }

            public function search(string $query, int $limit = 5): Collection
            {
                return collect([
                    new SearchResult('ws-1', 'Workspace Item', '/item', 'workspace-items', 'folder'),
                ]);
            }

            public function getUrl(mixed $result): string
            {
                return $result->url ?? '#';
            }

            public function isAvailable(?object $user, ?object $workspace): bool
            {
                // Only available when workspace slug is 'allowed-workspace'
                return $workspace !== null && $workspace->slug === 'allowed-workspace';
            }
        };

        $registry->register($provider);

        // Test with disallowed workspace
        $disallowedWorkspace = createMockSearchWorkspace(1, 'other-workspace');
        $results = $registry->search('item', null, $disallowedWorkspace);
        expect($results)->toBeEmpty();

        // Test with allowed workspace
        $allowedWorkspace = createMockSearchWorkspace(2, 'allowed-workspace');
        $results = $registry->search('item', null, $allowedWorkspace);
        expect($results)->toHaveKey('workspace-items');
    });
});
