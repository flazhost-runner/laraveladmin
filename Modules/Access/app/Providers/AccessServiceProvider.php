<?php

namespace Modules\Access\app\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Access\app\Interfaces\IPermissionService;
use Modules\Access\app\Interfaces\IRoleService;
use Modules\Access\app\Interfaces\IUserService;
use Modules\Access\app\Services\PermissionService;
use Modules\Access\app\Services\RoleService;
use Modules\Access\app\Services\UserService;

class AccessServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            IUserService::class,
            UserService::class,
        );
        $this->app->bind(
            IRoleService::class,
            RoleService::class,
        );
        $this->app->bind(
            IPermissionService::class,
            PermissionService::class,
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Access', 'database/migrations'));
        $this->loadViewsFrom(module_path('Access', 'resources/views'), 'access-module');
        $this->loadRoutesFrom(module_path('Access', 'routes/web.php'));
        $this->loadRoutesFrom(module_path('Access', 'routes/api.php'));
    }
}
