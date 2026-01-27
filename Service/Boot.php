<?php

declare(strict_types=1);

namespace Core\Service\Admin;

use Core\Service\Contracts\ServiceDefinition;
use Core\Service\ServiceVersion;
use Illuminate\Support\ServiceProvider;

/**
 * Hub Service
 *
 * Core admin panel service layer.
 * Uses Core\Admin as the engine.
 *
 * This is an internal service that powers the admin panel itself.
 * It is not publicly marketed and all users implicitly have access.
 */
class Boot extends ServiceProvider implements ServiceDefinition
{
    /**
     * Get the service definition for seeding platform_services.
     */
    public static function definition(): array
    {
        return [
            'code' => 'hub',
            'module' => 'Hub',
            'name' => 'Hub',
            'tagline' => 'Admin dashboard',
            'description' => 'Central admin panel for managing all Host services.',
            'icon' => 'house',
            'color' => 'slate',
            'marketing_domain' => null, // Internal service, no marketing site
            'website_class' => null,
            'entitlement_code' => 'core.srv.hub',
            'sort_order' => 0, // First in list (internal)
        ];
    }

    /**
     * Admin menu items for this service.
     *
     * Hub doesn't register menu items - it IS the menu.
     */
    public function adminMenuItems(): array
    {
        return [];
    }

    public function menuPermissions(): array
    {
        return [];
    }

    public function canViewMenu(?object $user, ?object $workspace): bool
    {
        return $user !== null;
    }

    public static function version(): ServiceVersion
    {
        return new ServiceVersion(1, 0, 0);
    }

    /**
     * Hub has no external service dependencies.
     */
    public static function dependencies(): array
    {
        return [];
    }
}
