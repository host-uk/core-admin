<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Tests;

use Core\Admin\Search\Concerns\HasSearchProvider;
use Core\Admin\Search\Contracts\SearchProvider;
use Core\Admin\Search\SearchProviderRegistry;
use Core\Admin\Search\SearchResult;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class SearchProviderRegistryTest extends TestCase
{
    protected SearchProviderRegistry $registry;

    protected function setUp(): void
    {
        parent::setUp();
        $this->registry = new SearchProviderRegistry;
    }

    public function test_can_register_provider(): void
    {
        $provider = $this->createMockProvider('test', 'Test', 'document');

        $this->registry->register($provider);

        $this->assertCount(1, $this->registry->providers());
    }

    public function test_can_register_many_providers(): void
    {
        $providers = [
            $this->createMockProvider('pages', 'Pages', 'document'),
            $this->createMockProvider('users', 'Users', 'user'),
        ];

        $this->registry->registerMany($providers);

        $this->assertCount(2, $this->registry->providers());
    }

    public function test_fuzzy_match_direct_substring(): void
    {
        $this->assertTrue($this->registry->fuzzyMatch('dash', 'Dashboard'));
        $this->assertTrue($this->registry->fuzzyMatch('board', 'Dashboard'));
        $this->assertTrue($this->registry->fuzzyMatch('settings', 'Account Settings'));
    }

    public function test_fuzzy_match_case_insensitive(): void
    {
        $this->assertTrue($this->registry->fuzzyMatch('DASH', 'dashboard'));
        $this->assertTrue($this->registry->fuzzyMatch('Dashboard', 'DASHBOARD'));
    }

    public function test_fuzzy_match_word_start(): void
    {
        // "gs" should match "Global Search" (G + S)
        $this->assertTrue($this->registry->fuzzyMatch('gs', 'Global Search'));

        // "ps" should match "Post Settings"
        $this->assertTrue($this->registry->fuzzyMatch('ps', 'Post Settings'));

        // "ul" should match "Usage Limits"
        $this->assertTrue($this->registry->fuzzyMatch('ul', 'Usage Limits'));
    }

    public function test_fuzzy_match_abbreviation(): void
    {
        // Characters appear in order
        $this->assertTrue($this->registry->fuzzyMatch('dbd', 'dashboard'));
        $this->assertTrue($this->registry->fuzzyMatch('gsr', 'global search results'));
    }

    public function test_fuzzy_match_empty_query_returns_false(): void
    {
        $this->assertFalse($this->registry->fuzzyMatch('', 'Dashboard'));
        $this->assertFalse($this->registry->fuzzyMatch('   ', 'Dashboard'));
    }

    public function test_fuzzy_match_no_match(): void
    {
        $this->assertFalse($this->registry->fuzzyMatch('xyz', 'Dashboard'));
        $this->assertFalse($this->registry->fuzzyMatch('zzz', 'Settings'));
    }

    public function test_relevance_score_exact_match(): void
    {
        $score = $this->registry->relevanceScore('dashboard', 'dashboard');
        $this->assertEquals(100, $score);
    }

    public function test_relevance_score_starts_with(): void
    {
        $score = $this->registry->relevanceScore('dash', 'dashboard');
        $this->assertEquals(90, $score);
    }

    public function test_relevance_score_contains(): void
    {
        $score = $this->registry->relevanceScore('board', 'dashboard');
        $this->assertEquals(70, $score);
    }

    public function test_relevance_score_word_start(): void
    {
        $score = $this->registry->relevanceScore('gs', 'global search');
        $this->assertEquals(60, $score);
    }

    public function test_relevance_score_no_match(): void
    {
        $score = $this->registry->relevanceScore('xyz', 'dashboard');
        $this->assertEquals(0, $score);
    }

    public function test_search_returns_grouped_results(): void
    {
        $provider = $this->createMockProvider('pages', 'Pages', 'document', [
            new SearchResult('1', 'Dashboard', '/hub', 'pages', 'house', 'Overview'),
            new SearchResult('2', 'Settings', '/hub/settings', 'pages', 'gear', 'Preferences'),
        ]);

        $this->registry->register($provider);

        $results = $this->registry->search('dash', null, null);

        $this->assertArrayHasKey('pages', $results);
        $this->assertEquals('Pages', $results['pages']['label']);
        $this->assertEquals('document', $results['pages']['icon']);
        $this->assertCount(2, $results['pages']['results']);
    }

    public function test_search_respects_provider_availability(): void
    {
        $availableProvider = $this->createMockProvider('pages', 'Pages', 'document', [], true);
        $unavailableProvider = $this->createMockProvider('admin', 'Admin', 'shield', [], false);

        $this->registry->register($availableProvider);
        $this->registry->register($unavailableProvider);

        $available = $this->registry->availableProviders(null, null);

        $this->assertCount(1, $available);
    }

    public function test_flatten_results(): void
    {
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
                ],
            ],
        ];

        $flat = $this->registry->flattenResults($grouped);

        $this->assertCount(3, $flat);
        $this->assertEquals('Dashboard', $flat[0]['title']);
        $this->assertEquals('Settings', $flat[1]['title']);
        $this->assertEquals('Admin', $flat[2]['title']);
    }

    /**
     * Create a mock search provider.
     */
    protected function createMockProvider(
        string $type,
        string $label,
        string $icon,
        array $results = [],
        bool $available = true
    ): SearchProvider {
        return new class($type, $label, $icon, $results, $available) implements SearchProvider
        {
            use HasSearchProvider;

            public function __construct(
                protected string $type,
                protected string $label,
                protected string $icon,
                protected array $results,
                protected bool $available
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
                return $result['url'] ?? '#';
            }

            public function isAvailable(?object $user, ?object $workspace): bool
            {
                return $this->available;
            }
        };
    }
}
