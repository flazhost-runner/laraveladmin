<?php

namespace Modules\Access\app\Interfaces;

use App\Models\User;

interface IUserService
{
    public function index(array $filter): array;

    public function store(array $data, string $actorId): User;

    public function edit(string $id): User;

    public function update(string $id, array $data, string $actorId): User;

    public function delete(string $id): void;

    public function deleteSelected(array $ids): int;
}
