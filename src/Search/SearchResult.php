<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

/**
 * Data transfer object for search results.
 *
 * Represents a single search result from a SearchProvider. Implements
 * Arrayable and JsonSerializable for easy serialization to Livewire
 * and JavaScript.
 */
final class SearchResult implements Arrayable, JsonSerializable
{
    /**
     * Create a new search result instance.
     *
     * @param  string  $id  Unique identifier for the result
     * @param  string  $title  Primary display text
     * @param  string  $url  Navigation URL
     * @param  string  $type  The search type (from provider)
     * @param  string  $icon  Icon name for display
     * @param  string|null  $subtitle  Secondary display text
     * @param  array  $meta  Additional metadata
     */
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $url,
        public readonly string $type,
        public readonly string $icon,
        public readonly ?string $subtitle = null,
        public readonly array $meta = [],
    ) {}

    /**
     * Create a SearchResult from an array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? uniqid()),
            title: (string) ($data['title'] ?? ''),
            url: (string) ($data['url'] ?? '#'),
            type: (string) ($data['type'] ?? 'unknown'),
            icon: (string) ($data['icon'] ?? 'document'),
            subtitle: $data['subtitle'] ?? null,
            meta: $data['meta'] ?? [],
        );
    }

    /**
     * Create a SearchResult with a new type and icon.
     *
     * Used by the registry to set type/icon from the provider.
     */
    public function withTypeAndIcon(string $type, string $icon): static
    {
        return new static(
            id: $this->id,
            title: $this->title,
            url: $this->url,
            type: $type,
            icon: $this->icon !== 'document' ? $this->icon : $icon,
            subtitle: $this->subtitle,
            meta: $this->meta,
        );
    }

    /**
     * Convert the result to an array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'url' => $this->url,
            'type' => $this->type,
            'icon' => $this->icon,
            'meta' => $this->meta,
        ];
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
