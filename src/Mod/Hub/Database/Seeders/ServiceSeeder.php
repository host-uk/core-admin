<?php

declare(strict_types=1);

namespace Core\Mod\Hub\Database\Seeders;

use Core\Service\Contracts\ServiceDefinition;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Core\Mod\Hub\Models\Service;

/**
 * Seeds platform services from service definitions.
 *
 * Iterates all Service classes with definition() and creates/updates
 * corresponding entries in the platform_services table.
 *
 * Run with: php artisan db:seed --class="\\Core\Mod\\Hub\\Database\\Seeders\\ServiceSeeder"
 */
class ServiceSeeder extends Seeder
{
    /**
     * List of service classes that provide service definitions.
     *
     * @var array<class-string<ServiceDefinition>>
     */
    protected array $services = [
        \Service\Hub\Boot::class, // Internal service
        \Service\Bio\Boot::class,
        \Service\Social\Boot::class,
        \Service\Analytics\Boot::class,
        \Service\Trust\Boot::class,
        \Service\Notify\Boot::class,
        \Service\Support\Boot::class,
        \Service\Commerce\Boot::class,
        \Service\Agentic\Boot::class,
    ];

    public function run(): void
    {
        if (! Schema::hasTable('platform_services')) {
            $this->command?->warn('platform_services table does not exist. Run migrations first.');

            return;
        }

        $seeded = 0;
        $updated = 0;

        foreach ($this->services as $serviceClass) {
            if (! class_exists($serviceClass)) {
                $this->command?->warn("Service class not found: {$serviceClass}");

                continue;
            }

            if (! method_exists($serviceClass, 'definition')) {
                $this->command?->warn("Service {$serviceClass} does not have definition()");

                continue;
            }

            $definition = $serviceClass::definition();

            if (! $definition) {
                continue;
            }

            $existing = Service::where('code', $definition['code'])->first();

            if ($existing) {
                // Sync core fields from definition (code is source of truth)
                $existing->update([
                    'module' => $definition['module'],
                    'name' => $definition['name'],
                    'tagline' => $definition['tagline'] ?? null,
                    'description' => $definition['description'] ?? null,
                    'icon' => $definition['icon'] ?? null,
                    'color' => $definition['color'] ?? null,
                    'entitlement_code' => $definition['entitlement_code'] ?? null,
                    'sort_order' => $definition['sort_order'] ?? 50,
                    // Domain routing - only set if not already configured (admin can override)
                    'marketing_domain' => $existing->marketing_domain ?? ($definition['marketing_domain'] ?? null),
                    'website_class' => $existing->website_class ?? ($definition['website_class'] ?? null),
                ]);
                $updated++;
            } else {
                Service::create([
                    'code' => $definition['code'],
                    'module' => $definition['module'],
                    'name' => $definition['name'],
                    'tagline' => $definition['tagline'] ?? null,
                    'description' => $definition['description'] ?? null,
                    'icon' => $definition['icon'] ?? null,
                    'color' => $definition['color'] ?? null,
                    'marketing_domain' => $definition['marketing_domain'] ?? null,
                    'website_class' => $definition['website_class'] ?? null,
                    'entitlement_code' => $definition['entitlement_code'] ?? null,
                    'sort_order' => $definition['sort_order'] ?? 50,
                    'is_enabled' => true,
                    'is_public' => true,
                    'is_featured' => false,
                ]);
                $seeded++;
            }
        }

        $this->command?->info("Services seeded: {$seeded} created, {$updated} updated.");
    }
}
