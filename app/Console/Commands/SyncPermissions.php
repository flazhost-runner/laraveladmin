<?php

namespace App\Console\Commands;

use App\Models\Permission;
use Illuminate\Console\Command;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;

class SyncPermissions extends Command
{
    protected $signature = 'permissions:sync';

    protected $description = 'Sync permissions from named routes (idempotent)';

    public function handle(): int
    {
        /** @var RouteCollection $routeCollection */
        $routeCollection = Route::getRoutes();
        $routes = $routeCollection->getRoutes();
        $count = 0;
        foreach ($routes as $route) {
            $name = $route->getName();
            if (! $name) {
                continue;
            }
            foreach ($route->methods() as $method) {
                if (in_array($method, ['HEAD', 'OPTIONS'])) {
                    continue;
                }
                $guard = str_starts_with($name, 'api.') ? 'api' : 'web';
                Permission::firstOrCreate(
                    ['name' => $name, 'method' => $method, 'guard_name' => $guard],
                    ['status' => 'Active', 'desc' => '']
                );
                $count++;
            }
        }
        $this->info("Synced $count permissions from routes.");

        return self::SUCCESS;
    }
}
