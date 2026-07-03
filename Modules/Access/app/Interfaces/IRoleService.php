<?php

namespace Modules\Access\app\Interfaces;

use App\Models\Role;

interface IRoleService
{
    public function index(array $filter): array;

    public function store(array $data, string $actorId): Role;

    public function edit(string $id): Role;

    public function update(string $id, array $data, string $actorId): Role;

    public function delete(string $id): void;

    public function deleteSelected(array $ids): int;

    public function assignPermission(string $roleId, string $permissionId): void;

    public function unassignPermission(string $roleId, string $permissionId): void;

    public function listPermissions(string $roleId, array $filter = []): array;

    public function assignSelected(string $roleId, array $permissionIds): void;

    public function unassignSelected(string $roleId, array $permissionIds): void;
}
