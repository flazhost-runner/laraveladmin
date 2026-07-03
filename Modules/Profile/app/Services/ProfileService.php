<?php

namespace Modules\Profile\app\Services;

use App\Exceptions\NotFoundAppException;
use App\Exceptions\ValidationAppException;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Modules\Profile\app\Interfaces\IProfileService;

class ProfileService implements IProfileService
{
    public function get(string $userId): User
    {
        $user = User::find($userId);
        if (! $user) {
            throw new NotFoundAppException('User not found');
        }

        return $user;
    }

    public function update(string $userId, array $data): User
    {
        $user = User::find($userId);
        if (! $user) {
            throw new NotFoundAppException('User not found');
        }

        $payload = [
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'updated_by' => $userId,
        ];

        if (! empty($data['picture'])) {
            $payload['picture'] = $data['picture'];
        }

        $user->update($payload);

        return $user->fresh();
    }

    public function changePassword(string $userId, array $data): void
    {
        $user = User::find($userId);
        if (! $user) {
            throw new NotFoundAppException('User not found');
        }

        if (! Hash::check($data['current_password'], $user->password)) {
            throw new ValidationAppException('Current password is incorrect');
        }

        $user->update(['password' => Hash::make($data['password'])]);
    }
}
