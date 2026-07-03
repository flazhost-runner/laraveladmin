<?php

namespace Modules\Access\Tests\Feature;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub routes that the shared layout (sidebar) requires but may not be
        // registered until later phases, so blade templates don't throw.
        if (! Route::has('admin.v1.dashboard.index')) {
            Route::get('/admin/v1/dashboard', fn () => 'stub')->name('admin.v1.dashboard.index');
        }
        if (! Route::has('admin.v1.components.index')) {
            Route::get('/admin/v1/components', fn () => 'stub')->name('admin.v1.components.index');
        }
        if (! Route::has('admin.v1.setting.index')) {
            Route::get('/admin/v1/setting', fn () => 'stub')->name('admin.v1.setting.index');
        }
    }

    /** Create a User with explicit data (avoids faker unique() exhaustion). */
    private function makeUser(string $email, string $name = 'Test User'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'status' => 'Active',
            'code' => 'USR-'.strtoupper(substr(md5($email), 0, 6)),
        ]);
    }

    private function actingAsAdmin(): User
    {
        // Create Administrator role so Authorize middleware bypasses permission check
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['status' => 'Active']
        );

        $user = $this->makeUser('admin@test.example', 'Test Admin');
        $user->roles()->sync([$role->id]);

        $this->withSession(['user_id' => $user->id]);

        return $user;
    }

    // ===========================
    // USER TESTS
    // ===========================

    public function test_user_index_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/access/user');
        $response->assertOk();
    }

    public function test_user_create_page_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/access/user/create');
        $response->assertOk();
    }

    public function test_user_store_creates_user(): void
    {
        $this->actingAsAdmin();
        $response = $this->post('/admin/v1/access/user/store', [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'status' => 'Active',
        ]);
        $response->assertRedirect('/admin/v1/access/user');
        $this->assertDatabaseHas('users', ['email' => 'testuser@example.com']);
    }

    public function test_user_edit_page_loads(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser('edit.target@example.com');
        $response = $this->get("/admin/v1/access/user/{$user->id}/edit");
        $response->assertOk();
    }

    public function test_user_update_works(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser('update.target@example.com', 'Old Name');

        $response = $this->put("/admin/v1/access/user/{$user->id}/update", [
            'name' => 'New Name',
            'email' => $user->email,
            'status' => 'Active',
        ]);
        $response->assertRedirect('/admin/v1/access/user');
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    }

    public function test_user_delete_removes_user(): void
    {
        $this->actingAsAdmin();
        $user = $this->makeUser('delete.target@example.com');

        $response = $this->delete("/admin/v1/access/user/{$user->id}/delete");
        $response->assertRedirect('/admin/v1/access/user');
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_user_delete_selected_removes_users(): void
    {
        $this->actingAsAdmin();
        $u1 = $this->makeUser('delete.sel1@example.com');
        $u2 = $this->makeUser('delete.sel2@example.com');

        $response = $this->post('/admin/v1/access/user/delete_selected', [
            'selected' => [$u1->id, $u2->id],
        ]);
        $response->assertRedirect('/admin/v1/access/user');
        $this->assertDatabaseMissing('users', ['id' => $u1->id]);
        $this->assertDatabaseMissing('users', ['id' => $u2->id]);
    }

    // ===========================
    // ROLE TESTS
    // ===========================

    public function test_role_index_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/access/role');
        $response->assertOk();
    }

    public function test_role_create_page_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/access/role/create');
        $response->assertOk();
    }

    public function test_role_store_creates_role(): void
    {
        $this->actingAsAdmin();
        $response = $this->post('/admin/v1/access/role/store', [
            'name' => 'Test Role',
            'status' => 'Active',
            'desc' => 'A test role',
        ]);
        $response->assertRedirect('/admin/v1/access/role');
        $this->assertDatabaseHas('roles', ['name' => 'Test Role']);
    }

    public function test_role_edit_page_loads(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'Edit Role', 'status' => 'Active']);
        $response = $this->get("/admin/v1/access/role/{$role->id}/edit");
        $response->assertOk();
    }

    public function test_role_delete_removes_role(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'Delete Role', 'status' => 'Active']);

        $response = $this->delete("/admin/v1/access/role/{$role->id}/delete");
        $response->assertRedirect('/admin/v1/access/role');
        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
    }

    // ===========================
    // PERMISSION TESTS
    // ===========================

    public function test_permission_index_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/access/permission');
        $response->assertOk();
    }

    public function test_permission_create_page_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/access/permission/create');
        $response->assertOk();
    }

    public function test_permission_store_creates_permission(): void
    {
        $this->actingAsAdmin();
        $response = $this->post('/admin/v1/access/permission/store', [
            'name' => 'test.permission.index',
            'method' => 'GET',
            'guard_name' => 'web',
            'status' => 'Active',
            'desc' => 'Test permission',
        ]);
        $response->assertRedirect('/admin/v1/access/permission');
        $this->assertDatabaseHas('permissions', ['name' => 'test.permission.index', 'method' => 'GET']);
    }

    public function test_permission_edit_page_loads(): void
    {
        $this->actingAsAdmin();
        $perm = Permission::create(['name' => 'edit.test', 'method' => 'GET', 'guard_name' => 'web', 'status' => 'Active']);
        $response = $this->get("/admin/v1/access/permission/{$perm->id}/edit");
        $response->assertOk();
    }

    public function test_permission_delete_removes_permission(): void
    {
        $this->actingAsAdmin();
        $perm = Permission::create(['name' => 'delete.test', 'method' => 'GET', 'guard_name' => 'web', 'status' => 'Active']);

        $response = $this->delete("/admin/v1/access/permission/{$perm->id}/delete");
        $response->assertRedirect('/admin/v1/access/permission');
        $this->assertDatabaseMissing('permissions', ['id' => $perm->id]);
    }

    // ===========================
    // ROLE PERMISSION TESTS
    // ===========================

    public function test_role_permission_page_loads(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'Perm Role', 'status' => 'Active']);
        $response = $this->get("/admin/v1/access/role/{$role->id}/permission");
        $response->assertOk();
    }

    public function test_role_permission_assign_and_unassign(): void
    {
        $this->actingAsAdmin();
        $role = Role::create(['name' => 'Assign Role', 'status' => 'Active']);
        $perm = Permission::create(['name' => 'assign.test', 'method' => 'GET', 'guard_name' => 'web', 'status' => 'Active']);

        $this->get("/admin/v1/access/role/{$role->id}/permission/{$perm->id}/assign")
            ->assertRedirect();

        $this->assertDatabaseHas('roles_permissions', ['role_id' => $role->id, 'permission_id' => $perm->id]);

        $this->get("/admin/v1/access/role/{$role->id}/permission/{$perm->id}/unassign")
            ->assertRedirect();

        $this->assertDatabaseMissing('roles_permissions', ['role_id' => $role->id, 'permission_id' => $perm->id]);
    }
}
