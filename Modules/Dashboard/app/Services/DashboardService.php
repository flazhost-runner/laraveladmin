<?php

namespace Modules\Dashboard\app\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Modules\Dashboard\app\Interfaces\IDashboardService;

class DashboardService implements IDashboardService
{
    public function getStats(): array
    {
        return [
            'users' => User::count(),
            'roles' => Role::count(),
            'permissions' => Permission::count(),
            'active_users' => User::where('status', 'Active')->count(),
        ];
    }

    public function getRecentActivities(): array
    {
        return [
            ['type' => 'Login',  'message' => 'Admin logged in',                   'time' => '2 minutes ago'],
            ['type' => 'Create', 'message' => 'New user account created',           'time' => '15 minutes ago'],
            ['type' => 'Update', 'message' => 'Role permissions updated',           'time' => '1 hour ago'],
            ['type' => 'Delete', 'message' => 'Inactive user removed',             'time' => '3 hours ago'],
            ['type' => 'Login',  'message' => 'User johndoe@example.com logged in', 'time' => '5 hours ago'],
        ];
    }
}
