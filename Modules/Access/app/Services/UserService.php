<?php

namespace Modules\Access\app\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\NotFoundAppException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Access\app\Interfaces\IUserService;

class UserService implements IUserService
{
    public function index(array $filter): array
    {
        $q = User::with('roles')->orderBy('created_at', 'desc');

        if (! empty($filter['q_code'])) {
            [$col, , $val] = array_values(ci_like('code', $filter['q_code']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_name'])) {
            [$col, , $val] = array_values(ci_like('name', $filter['q_name']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_phone'])) {
            [$col, , $val] = array_values(ci_like('phone', $filter['q_phone']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_email'])) {
            [$col, , $val] = array_values(ci_like('email', $filter['q_email']));
            $q->whereRaw("LOWER($col) LIKE ?", [$val]);
        }
        if (! empty($filter['q_status'])) {
            $q->where('status', $filter['q_status']);
        }
        if (! empty($filter['q_role'])) {
            $q->whereHas('roles', fn ($r) => $r->where('roles.id', $filter['q_role']));
        }

        $perPage = max(1, (int) ($filter['q_page_size'] ?? 10));
        $page = max(1, (int) ($filter['page'] ?? 1));
        $total = (clone $q)->count();
        $items = $q->skip(($page - 1) * $perPage)->take($perPage)->get();

        return paginate($items->toArray(), $total, $perPage, $page);
    }

    public function store(array $data, string $actorId): User
    {
        if (User::where('email', $data['email'])->exists()) {
            throw new ConflictException('Email already exists');
        }
        $roleIds = $data['roles'] ?? [];
        unset($data['roles'], $data['password_confirmation']);
        $data['password'] = Hash::make($data['password']);
        $data['created_by'] = $actorId;
        $data['updated_by'] = $actorId;
        $user = User::create($data);
        if ($roleIds) {
            $user->roles()->sync($roleIds);
        }

        return $user->fresh('roles');
    }

    public function edit(string $id): User
    {
        $user = User::with('roles')->find($id);
        if (! $user) {
            throw new NotFoundAppException('User not found');
        }

        return $user;
    }

    public function update(string $id, array $data, string $actorId): User
    {
        $user = User::find($id);
        if (! $user) {
            throw new NotFoundAppException('User not found');
        }
        if (! empty($data['email']) && $data['email'] !== $user->email) {
            if (User::where('email', $data['email'])->where('id', '!=', $id)->exists()) {
                throw new ConflictException('Email already used');
            }
        }
        $roleIds = $data['roles'] ?? null;
        unset($data['roles'], $data['password_confirmation']);
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        }
        $data['updated_by'] = $actorId;
        $user->update($data);
        if ($roleIds !== null) {
            $user->roles()->sync($roleIds);
        }

        return $user->fresh('roles');
    }

    public function delete(string $id): void
    {
        $user = User::find($id);
        if (! $user) {
            throw new NotFoundAppException('User not found');
        }
        $user->delete();
    }

    public function deleteSelected(array $ids): int
    {
        return User::whereIn('id', $ids)->delete();
    }
}
