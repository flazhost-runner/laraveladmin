<?php

namespace Modules\Components\app\Providers;

use Illuminate\Support\ServiceProvider;

class ComponentsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // No service bindings — static showcase module
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(module_path('Components', 'database/migrations'));
        $this->loadViewsFrom(module_path('Components', 'resources/views'), 'components-module');
        $this->loadRoutesFrom(module_path('Components', 'routes/web.php'));
        $this->loadRoutesFrom(module_path('Components', 'routes/api.php'));
    }
}
