<?php

namespace Modules\Dashboard\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Dashboard\app\Interfaces\IDashboardService;
use Modules\Dashboard\app\Services\DashboardService;

class DashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IDashboardService::class,
            DashboardService::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Dashboard', 'database/migrations'));
        $this->loadViewsFrom(module_path('Dashboard', 'resources/views'), 'dashboard-module');
        $this->loadRoutesFrom(module_path('Dashboard', 'routes/web.php'));
        $this->loadRoutesFrom(module_path('Dashboard', 'routes/api.php'));
    }
}
