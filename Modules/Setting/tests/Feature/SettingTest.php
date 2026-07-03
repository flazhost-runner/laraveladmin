<?php

namespace Modules\Setting\Tests\Feature;

use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub routes that shared layout may need but aren't registered in isolation
        if (! Route::has('admin.v1.dashboard.index')) {
            Route::get('/admin/v1/dashboard', fn () => 'stub')->name('admin.v1.dashboard.index');
        }
        if (! Route::has('admin.v1.components.index')) {
            Route::get('/admin/v1/components', fn () => 'stub')->name('admin.v1.components.index');
        }
    }

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
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['guard_name' => 'web', 'status' => 'Active']
        );

        $user = $this->makeUser('admin@setting-test.example', 'Admin Setting');
        $user->roles()->sync([$role->id]);

        $this->withSession(['user_id' => $user->id]);

        return $user;
    }

    public function test_setting_index_loads_for_authenticated_user(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/setting');
        $response->assertOk();
    }

    public function test_setting_index_redirects_unauthenticated_user(): void
    {
        $response = $this->get('/admin/v1/setting');
        $response->assertRedirect();
    }

    public function test_setting_update_saves_name_and_theme(): void
    {
        $this->actingAsAdmin();

        // Seed a settings row first (using forceFill to bypass guarded)
        $s = new Setting;
        $s->forceFill(['name' => 'OldName', 'theme' => 'blue', 'fe_template' => 'agency-consulting-002-creative-agency']);
        $s->save();

        $response = $this->put('/admin/v1/setting/update', [
            'name' => 'NewName',
            'theme' => 'green',
            'fe_template' => 'agency-consulting-002-creative-agency',
        ]);

        $response->assertRedirect(route('admin.v1.setting.index'));
        $this->assertDatabaseHas('settings', ['name' => 'NewName', 'theme' => 'green']);
    }

    public function test_setting_update_changes_fe_template(): void
    {
        $this->actingAsAdmin();

        $s = new Setting;
        $s->forceFill(['name' => 'TestSite', 'theme' => 'blue', 'fe_template' => 'agency-consulting-002-creative-agency']);
        $s->save();

        $response = $this->put('/admin/v1/setting/update', [
            'name' => 'TestSite',
            'theme' => 'blue',
            'fe_template' => 'portfolio-001-personal-portfolio',
        ]);

        $response->assertRedirect(route('admin.v1.setting.index'));
        $this->assertDatabaseHas('settings', ['fe_template' => 'portfolio-001-personal-portfolio']);
    }
}
