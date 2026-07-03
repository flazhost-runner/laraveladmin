<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    /**
     * Idempotent seeder — safe to run multiple times.
     */
    public function run(): void
    {
        // 1. Create Administrator role
        $role = Role::updateOrCreate(
            ['name' => 'Administrator', 'guard_name' => 'web'],
            ['status' => 'Active']
        );

        // 2. Create admin user
        $user = User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'code' => '0000000001',
                'name' => 'Administrator',
                'phone' => '12345678910',
                'email_verified_at' => now(),
                'password' => Hash::make('12345678'),
                'status' => 'Active',
                'timezone' => 'Asia/Jakarta',
                'blocked' => false,
                'blocked_reason' => '',
            ]
        );

        // 3. Attach user to Administrator role (pivot: users_roles)
        if (! $user->roles()->where('roles.id', $role->id)->exists()) {
            $user->roles()->attach($role->id);
        }

        // 4. Create default settings
        Setting::updateOrCreate(
            ['id' => $this->getOrCreateSettingId()],
            [
                'name' => 'LaravelAdmin',
                'initial' => 'LA',
                'theme' => 'blue',
                'fe_template' => 'agency-consulting-002-creative-agency',
            ]
        );
    }

    /**
     * Retrieve existing setting ID or prepare a new one.
     */
    private function getOrCreateSettingId(): string
    {
        $existing = Setting::first();
        if ($existing) {
            return $existing->id;
        }

        return Str::uuid()->toString();
    }
}
