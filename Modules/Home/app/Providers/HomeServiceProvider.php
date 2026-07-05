<?php

namespace Modules\Home\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Home\app\Interfaces\IFeTemplateService;
use Modules\Home\app\Interfaces\IHomeService;
use Modules\Home\app\Services\FeTemplateService;
use Modules\Home\app\Services\HomeService;

class HomeServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IHomeService::class,
            HomeService::class,
        );
        $this->app->bind(
            IFeTemplateService::class,
            FeTemplateService::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Home', 'database/migrations'));
        $this->loadViewsFrom(module_path('Home', 'resources/views'), 'home-module');
        $this->loadRoutesFrom(module_path('Home', 'routes/web.php'));
        $this->loadRoutesFrom(module_path('Home', 'routes/api.php'));
    }
}
