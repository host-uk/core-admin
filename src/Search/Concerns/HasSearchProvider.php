<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Concerns;

/**
 * Trait providing default implementations for SearchProvider methods.
 *
 * Use this trait to reduce boilerplate when implementing SearchProvider.
 */
trait HasSearchProvider
{
    /**
     * Get the priority for ordering in search results.
     */
    public function searchPriority(): int
    {
        return 50;
    }

    /**
     * Check if this provider should be active for the current context.
     *
     * Default implementation returns true (always available).
     *
     * @param  object|null  $user  The authenticated user
     * @param  object|null  $workspace  The current workspace context
     */
    public function isAvailable(?object $user, ?object $workspace): bool
    {
        return true;
    }

    /**
     * Escape LIKE wildcard characters for safe SQL queries.
     */
    protected function escapeLikeWildcards(string $value): string
    {
        return str_replace(['%', '_'], ['\\%', '\\_'], $value);
    }
}
