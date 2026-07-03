<?php

namespace Modules\Dashboard\Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    private function actingAsAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['guard_name' => 'web', 'status' => 'Active']
        );

        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.example',
            'password' => bcrypt('password'),
            'status' => 'Active',
            'code' => 'USR-ADMIN1',
        ]);
        $user->roles()->sync([$role->id]);

        $this->withSession(['user_id' => $user->id]);

        return $user;
    }

    public function test_dashboard_returns_200_for_authenticated_user(): void
    {
        $this->actingAsAdmin();

        $response = $this->get('/admin/v1/dashboard');

        $response->assertOk();
    }
}
