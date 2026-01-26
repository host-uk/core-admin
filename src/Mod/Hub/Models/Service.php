<?php

declare(strict_types=1);

namespace Core\Mod\Hub\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = 'platform_services';

    protected $fillable = [
        'code',
        'module',
        'name',
        'tagline',
        'description',
        'icon',
        'color',
        'marketing_domain',
        'website_class',
        'marketing_url',
        'docs_url',
        'is_enabled',
        'is_public',
        'is_featured',
        'entitlement_code',
        'sort_order',
        'metadata',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_public' => 'boolean',
        'is_featured' => 'boolean',
        'metadata' => 'array',
        'sort_order' => 'integer',
    ];

    /**
     * Scope: only enabled services.
     */
    public function scopeEnabled(Builder $query): Builder
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope: only public services (visible in catalogue).
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope: only featured services.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: order by sort_order, then name.
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Scope: services with a marketing domain configured.
     */
    public function scopeWithMarketingDomain(Builder $query): Builder
    {
        return $query->whereNotNull('marketing_domain')
            ->whereNotNull('website_class');
    }

    /**
     * Find a service by its code.
     */
    public static function findByCode(string $code): ?self
    {
        return self::where('code', $code)->first();
    }

    /**
     * Get domain → website_class mappings for enabled services.
     *
     * Used by DomainResolver for routing marketing domains.
     *
     * @return array<string, string> domain => website_class
     */
    public static function getDomainMappings(): array
    {
        return self::enabled()
            ->withMarketingDomain()
            ->pluck('website_class', 'marketing_domain')
            ->toArray();
    }

    /**
     * Get the marketing URL, falling back to marketing_domain if no override set.
     */
    public function getMarketingUrlAttribute(?string $value): ?string
    {
        if ($value) {
            return $value;
        }

        if ($this->marketing_domain) {
            $scheme = app()->environment('local') ? 'http' : 'https';

            return "{$scheme}://{$this->marketing_domain}";
        }

        return null;
    }

    /**
     * Check if a specific metadata key exists.
     */
    public function hasMeta(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    /**
     * Get a specific metadata value.
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Set a metadata value.
     */
    public function setMeta(string $key, mixed $value): void
    {
        $metadata = $this->metadata ?? [];
        $metadata[$key] = $value;
        $this->metadata = $metadata;
    }
}
