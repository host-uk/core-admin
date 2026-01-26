<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();           // 'bio', 'social' - matches module's service key
            $table->string('module', 50);                   // 'WebPage', 'Social' - source module name
            $table->string('name', 100);                    // 'Bio' - display name
            $table->string('tagline', 200)->nullable();     // 'Link-in-bio pages' - short marketing tagline
            $table->text('description')->nullable();        // Marketing description
            $table->string('icon', 50)->nullable();         // Font Awesome icon name
            $table->string('color', 20)->nullable();        // Tailwind color name
            $table->string('marketing_domain', 100)->nullable();  // 'lthn.test', 'social.host.test'
            $table->string('marketing_url', 255)->nullable();     // Full marketing page URL override
            $table->string('docs_url', 255)->nullable();          // Documentation URL
            $table->boolean('is_enabled')->default(true);         // Global enable/disable
            $table->boolean('is_public')->default(true);          // Show in public service catalogue
            $table->boolean('is_featured')->default(false);       // Feature in marketing
            $table->string('entitlement_code', 50)->nullable();   // 'core.srv.bio' - links to entitlement system
            $table->integer('sort_order')->default(50);
            $table->json('metadata')->nullable();           // Extensible for future needs
            $table->timestamps();

            $table->index('is_enabled');
            $table->index('is_public');
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_services');
    }
};
