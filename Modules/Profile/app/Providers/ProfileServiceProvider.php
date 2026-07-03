<?php

namespace Modules\Profile\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Profile\app\Interfaces\IProfileService;
use Modules\Profile\app\Services\ProfileService;

class ProfileServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IProfileService::class,
            ProfileService::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Profile', 'database/migrations'));
        $this->loadViewsFrom(module_path('Profile', 'resources/views'), 'profile-module');
        $this->loadRoutesFrom(module_path('Profile', 'routes/web.php'));
        $this->loadRoutesFrom(module_path('Profile', 'routes/api.php'));
    }
}
