<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Tests;

use Core\Admin\Search\SearchResult;
use PHPUnit\Framework\TestCase;

class SearchResultTest extends TestCase
{
    public function test_can_create_search_result(): void
    {
        $result = new SearchResult(
            id: '123',
            title: 'Dashboard',
            url: '/hub',
            type: 'pages',
            icon: 'house',
            subtitle: 'Overview and quick actions',
            meta: ['key' => 'value'],
        );

        $this->assertEquals('123', $result->id);
        $this->assertEquals('Dashboard', $result->title);
        $this->assertEquals('/hub', $result->url);
        $this->assertEquals('pages', $result->type);
        $this->assertEquals('house', $result->icon);
        $this->assertEquals('Overview and quick actions', $result->subtitle);
        $this->assertEquals(['key' => 'value'], $result->meta);
    }

    public function test_can_create_from_array(): void
    {
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

        $this->assertEquals('456', $result->id);
        $this->assertEquals('Settings', $result->title);
        $this->assertEquals('/hub/settings', $result->url);
        $this->assertEquals('pages', $result->type);
        $this->assertEquals('gear', $result->icon);
        $this->assertEquals('Account settings', $result->subtitle);
        $this->assertEquals(['order' => 2], $result->meta);
    }

    public function test_from_array_with_missing_fields(): void
    {
        $data = [
            'title' => 'Minimal',
        ];

        $result = SearchResult::fromArray($data);

        $this->assertNotEmpty($result->id); // Should generate an ID
        $this->assertEquals('Minimal', $result->title);
        $this->assertEquals('#', $result->url);
        $this->assertEquals('unknown', $result->type);
        $this->assertEquals('document', $result->icon);
        $this->assertNull($result->subtitle);
        $this->assertEquals([], $result->meta);
    }

    public function test_to_array(): void
    {
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

        $this->assertEquals([
            'id' => '789',
            'title' => 'Test',
            'subtitle' => 'Test subtitle',
            'url' => '/test',
            'type' => 'test',
            'icon' => 'test-icon',
            'meta' => ['foo' => 'bar'],
        ], $array);
    }

    public function test_json_serialize(): void
    {
        $result = new SearchResult(
            id: '1',
            title: 'JSON Test',
            url: '/json',
            type: 'json',
            icon: 'code',
        );

        $json = json_encode($result);
        $decoded = json_decode($json, true);

        $this->assertEquals('1', $decoded['id']);
        $this->assertEquals('JSON Test', $decoded['title']);
        $this->assertEquals('/json', $decoded['url']);
    }

    public function test_with_type_and_icon(): void
    {
        $original = new SearchResult(
            id: '1',
            title: 'Test',
            url: '/test',
            type: 'old-type',
            icon: 'document', // Default icon
        );

        $modified = $original->withTypeAndIcon('new-type', 'new-icon');

        // Original should be unchanged (immutable)
        $this->assertEquals('old-type', $original->type);
        $this->assertEquals('document', $original->icon);

        // Modified should have new values
        $this->assertEquals('new-type', $modified->type);
        $this->assertEquals('new-icon', $modified->icon);

        // Other properties should be preserved
        $this->assertEquals('1', $modified->id);
        $this->assertEquals('Test', $modified->title);
        $this->assertEquals('/test', $modified->url);
    }

    public function test_with_type_and_icon_preserves_custom_icon(): void
    {
        $original = new SearchResult(
            id: '1',
            title: 'Test',
            url: '/test',
            type: 'old-type',
            icon: 'custom-icon', // Not the default
        );

        $modified = $original->withTypeAndIcon('new-type', 'fallback-icon');

        // Should keep the custom icon, not use the fallback
        $this->assertEquals('custom-icon', $modified->icon);
        $this->assertEquals('new-type', $modified->type);
    }
}
