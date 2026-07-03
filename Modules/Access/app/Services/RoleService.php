<?php

namespace Modules\Access\app\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundAppException;
use App\Models\Permission;
use App\Models\Role;
use Modules\Access\app\Interfaces\IRoleService;

class RoleService implements IRoleService
{
    public function index(array $filter): array
    {
        $q = Role::orderBy('created_at', 'desc');

        if (! empty($filter['q_name'])) {
            [$col, , $val] = array_values(ci_like('name', $filter['q_name']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_status'])) {
            $q->where('status', $filter['q_status']);
        }
        if (! empty($filter['q_desc'])) {
            [$col, , $val] = array_values(ci_like('desc', $filter['q_desc']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }

        $perPage = max(1, (int) ($filter['q_page_size'] ?? 10));
        $page = max(1, (int) ($filter['page'] ?? 1));
        $total = (clone $q)->count();
        $items = $q->skip(($page - 1) * $perPage)->take($perPage)->get();

        return paginate($items->toArray(), $total, $perPage, $page);
    }

    public function store(array $data, string $actorId): Role
    {
        if (Role::where('name', $data['name'])->exists()) {
            throw new ConflictException('Role name already exists');
        }
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;

        return Role::create($data);
    }

    public function edit(string $id): Role
    {
        $role = Role::find($id);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }

        return $role;
    }

    public function update(string $id, array $data, string $actorId): Role
    {
        $role = Role::find($id);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }
        if (! empty($data['name']) && $data['name'] !== $role->name) {
            if (Role::where('name', $data['name'])->where('id', '!=', $id)->exists()) {
                throw new ConflictException('Role name already used');
            }
        }
        $data['updated_by'] = $actorId;
        $role->update($data);

        return $role->fresh();
    }

    public function delete(string $id): void
    {
        $role = Role::find($id);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }
        $role->delete();
    }

    public function deleteSelected(array $ids): int
    {
        return Role::whereIn('id', $ids)->delete();
    }

    public function listPermissions(string $roleId, array $filter = []): array
    {
        $role = Role::find($roleId);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }

        $assignedIds = $role->permissions()->pluck('permissions.id')->toArray();

        $q = Permission::orderBy('name');

        if (! empty($filter['q_name'])) {
            [$col, , $val] = array_values(ci_like('name', $filter['q_name']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_status'])) {
            $q->where('status', $filter['q_status']);
        }
        if (! empty($filter['q_desc'])) {
            [$col, , $val] = array_values(ci_like('desc', $filter['q_desc']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_method'])) {
            $q->where('method', $filter['q_method']);
        }

        $perPage = max(1, (int) ($filter['q_page_size'] ?? 20));
        $page = max(1, (int) ($filter['page'] ?? 1));
        $total = (clone $q)->count();
        $items = $q->skip(($page - 1) * $perPage)->take($perPage)->get();

        $result = paginate($items->toArray(), $total, $perPage, $page);
        $result['assigned_ids'] = $assignedIds;
        $result['role'] = $role->toArray();

        return $result;
    }

    public function assignPermission(string $roleId, string $permissionId): void
    {
        $role = Role::find($roleId);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }
        $permission = Permission::find($permissionId);
        if (! $permission) {
            throw new NotFoundAppException('Permission not found');
        }
        $role->permissions()->syncWithoutDetaching([$permissionId]);
    }

    public function unassignPermission(string $roleId, string $permissionId): void
    {
        $role = Role::find($roleId);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }
        $role->permissions()->detach($permissionId);
    }

    public function assignSelected(string $roleId, array $permissionIds): void
    {
        $role = Role::find($roleId);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }
        $role->permissions()->syncWithoutDetaching($permissionIds);
    }

    public function unassignSelected(string $roleId, array $permissionIds): void
    {
        $role = Role::find($roleId);
        if (! $role) {
            throw new NotFoundAppException('Role not found');
        }
        $role->permissions()->detach($permissionIds);
    }
}
