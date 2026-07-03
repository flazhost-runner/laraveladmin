<?php

namespace Modules\Setting\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Setting\app\Interfaces\IFeCatalogService;
use Modules\Setting\app\Interfaces\ISettingService;
use Modules\Setting\app\Services\FeCatalogService;
use Modules\Setting\app\Services\SettingService;

class SettingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IFeCatalogService::class,
            FeCatalogService::class,
        );
        $this->app->bind(
            ISettingService::class,
            SettingService::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Setting', 'database/migrations'));
        $this->loadViewsFrom(module_path('Setting', 'resources/views'), 'setting-module');
        $this->loadRoutesFrom(module_path('Setting', 'routes/web.php'));
        $this->loadRoutesFrom(module_path('Setting', 'routes/api.php'));
    }
}
