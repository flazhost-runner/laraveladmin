<?php

use Database\Seeders\DatabaseSeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Seeds the initial admin user, role, and default settings so that a
     * plain `php artisan migrate --force` produces a fully usable database
     * (admin@admin.com / 12345678) without requiring a separate
     * `php artisan db:seed` step — matching NodeAdmin/GoAdmin behavior.
     *
     * DatabaseSeeder (and the AdminSeeder it calls) is idempotent
     * (updateOrCreate + guarded pivot attach), so re-running the seeder
     * later via `php artisan db:seed --force` remains safe.
     */
    public function up(): void
    {
        Artisan::call('db:seed', [
            '--class' => DatabaseSeeder::class,
            '--force' => true,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * Intentionally a no-op: seeded data is baseline application data and
     * should not be destroyed on rollback.
     */
    public function down(): void
    {
        // no-op
    }
};
