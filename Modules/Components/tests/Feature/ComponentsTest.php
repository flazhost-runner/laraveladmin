<?php

namespace Modules\Components\Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ComponentsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub routes the shared layout requires but may not be registered in test env.
        if (! Route::has('admin.v1.dashboard.index')) {
            Route::get('/admin/v1/dashboard', fn () => 'stub')->name('admin.v1.dashboard.index');
        }
        if (! Route::has('admin.v1.setting.index')) {
            Route::get('/admin/v1/setting', fn () => 'stub')->name('admin.v1.setting.index');
        }
    }

    private function actingAsAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['guard_name' => 'web', 'status' => 'Active']
        );

        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@components.test',
            'password' => bcrypt('password'),
            'status' => 'Active',
            'code' => 'USR-COMP01',
        ]);
        $user->roles()->sync([$role->id]);

        $this->withSession(['user_id' => $user->id]);

        return $user;
    }

    public function test_components_index_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/components');
        $response->assertOk();
    }

    public function test_components_index_contains_showcase_sections(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/components');
        $response->assertOk();
        $response->assertSee('UI Components');
        $response->assertSee('Stat Card + Counter');
        $response->assertSee('Chart (Chart.js, warna ikut tema)');
        $response->assertSee('Badge & Status', false);
        $response->assertSee('Alert (flash)');
        $response->assertSee('Button & Dropdown Action', false);
        $response->assertSee('Popup (Modal / Toast / Confirm)');
        $response->assertSee('Form (CRUD)');
        $response->assertSee('Rich Text Editor (Trumbowyg + File Manager)');
        $response->assertSee('Data Table + Pagination');
    }

    public function test_components_route_requires_auth(): void
    {
        $response = $this->get('/admin/v1/components');
        // Unauthenticated → redirect to login
        $response->assertRedirect();
    }
}
