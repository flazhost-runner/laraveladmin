<?php

namespace Modules\Access\app\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundAppException;
use App\Models\Permission;
use Illuminate\Routing\RouteCollection;
use Modules\Access\app\Interfaces\IPermissionService;

class PermissionService implements IPermissionService
{
    public function index(array $filter): array
    {
        $q = Permission::orderBy('name');

        if (! empty($filter['q_name'])) {
            [$col, , $val] = array_values(ci_like('name', $filter['q_name']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_method'])) {
            $q->where('method', $filter['q_method']);
        }
        if (! empty($filter['q_status'])) {
            $q->where('status', $filter['q_status']);
        }
        if (! empty($filter['q_guard'])) {
            $q->where('guard_name', $filter['q_guard']);
        }
        if (! empty($filter['q_desc'])) {
            [$col, , $val] = array_values(ci_like('desc', $filter['q_desc']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }

        $perPage = max(1, (int) ($filter['q_page_size'] ?? 10));
        $page = max(1, (int) ($filter['q_page'] ?? 1));
        $total = (clone $q)->count();
        $items = $q->skip(($page - 1) * $perPage)->take($perPage)->get();

        return paginate($items->toArray(), $total, $perPage, $page);
    }

    public function store(array $data, string $actorId): Permission
    {
        if (Permission::where('name', $data['name'])->where('method', $data['method'])->exists()) {
            throw new ConflictException('Permission with this name and method already exists');
        }
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return Permission::create($data);
    }

    public function edit(string $id): Permission
    {
        $permission = Permission::find($id);
        if (! $permission) {
            throw new NotFoundAppException('Permission not found');
        }

        return $permission;
    }

    public function update(string $id, array $data, string $actorId): Permission
    {
        $permission = Permission::find($id);
        if (! $permission) {
            throw new NotFoundAppException('Permission not found');
        }
        $data['updated_by'] = $actorId;
        $permission->update($data);

        return $permission->fresh();
    }

    public function delete(string $id): void
    {
        $permission = Permission::find($id);
        if (! $permission) {
            throw new NotFoundAppException('Permission not found');
        }
        $permission->delete();
    }

    public function deleteSelected(array $ids): int
    {
        return Permission::whereIn('id', $ids)->delete();
    }

    public function syncFromRoutes(): int
    {
        /** @var RouteCollection $routeCollection */
        $routeCollection = app('router')->getRoutes();
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

        return $count;
    }
}
