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
        Schema::table('platform_services', function (Blueprint $table) {
            // Mod class to handle marketing_domain routing
            // e.g., 'Mod\LtHn\Boot' for lthn.test
            $table->string('website_class', 150)->nullable()->after('marketing_domain');

            $table->index('marketing_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('platform_services', function (Blueprint $table) {
            $table->dropIndex(['marketing_domain']);
            $table->dropColumn('website_class');
        });
    }
};
