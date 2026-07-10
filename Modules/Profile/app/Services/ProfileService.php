<?php

namespace Modules\Profile\app\Services;

use App\Exceptions\NotFoundAppException;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
            'code' => $data['code'],
            'name' => $data['name'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'timezone' => $data['timezone'] ?? $user->timezone,
            'status' => $data['status'],
            'updated_by' => $userId,
        ];

        // Password inline di form profil (opsional) — paritas NodeAdmin.
        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        // Avatar = FILE upload; kunci deterministik per user (menimpa foto lama) —
        // paritas NodeAdmin UserService.updateProfile (modules/access/user/<id>.webp).
        if (($data['picture'] ?? null) instanceof UploadedFile) {
            $payload['picture'] = storeImage($data['picture'], 'modules/access/user/'.$userId);
        }

        $user->update($payload);

        return $user->fresh();
    }
}
