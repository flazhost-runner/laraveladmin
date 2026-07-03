<?php

namespace Modules\Auth\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\app\Interfaces\IAuthService;
use Modules\Auth\app\Services\AuthService;

class AuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IAuthService::class,
            AuthService::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Auth', 'database/migrations'));
        $this->loadViewsFrom(module_path('Auth', 'resources/views'), 'auth-module');
        $this->loadRoutesFrom(module_path('Auth', 'routes/web.php'));
        $this->loadRoutesFrom(module_path('Auth', 'routes/api.php'));
    }
}
