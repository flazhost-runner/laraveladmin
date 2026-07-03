<?php

namespace Modules\Access\app\Interfaces;

use App\Models\Permission;

interface IPermissionService
{
    public function index(array $filter): array;

    public function store(array $data, string $actorId): Permission;

    public function edit(string $id): Permission;

    public function update(string $id, array $data, string $actorId): Permission;

    public function delete(string $id): void;

    public function deleteSelected(array $ids): int;

    public function syncFromRoutes(): int;
}
