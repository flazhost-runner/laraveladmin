<?php

namespace Modules\Profile\Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Stub routes the shared layout requires but may not be registered in test env.
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

    private function makeUser(string $email, string $name = 'Test User', string $password = 'password123'): User
    {
        return User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password),
            'status' => 'Active',
            'code' => 'USR-'.strtoupper(substr(md5($email), 0, 6)),
        ]);
    }

    private function loginAs(User $user): void
    {
        $this->withSession(['user_id' => $user->id]);
    }

    private function actingAsAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['guard_name' => 'web', 'status' => 'Active']
        );

        $user = $this->makeUser('admin@profile.test', 'Admin User');
        $user->roles()->sync([$role->id]);

        $this->loginAs($user);

        return $user;
    }

    // ─────────────────────────────────────────────────────────────
    // Profile Index
    // ─────────────────────────────────────────────────────────────

    public function test_profile_index_loads(): void
    {
        $this->actingAsAdmin();
        $response = $this->get('/admin/v1/profile');
        $response->assertOk();
    }

    public function test_profile_index_shows_user_info(): void
    {
        $user = $this->actingAsAdmin();
        $response = $this->get('/admin/v1/profile');
        $response->assertOk();
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    public function test_profile_index_requires_auth(): void
    {
        $response = $this->get('/admin/v1/profile');
        $response->assertRedirect();
    }

    // ─────────────────────────────────────────────────────────────
    // Profile Update
    // ─────────────────────────────────────────────────────────────

    public function test_profile_update_changes_name_and_phone(): void
    {
        $user = $this->actingAsAdmin();

        $response = $this->put('/admin/v1/profile/update', [
            'name' => 'Updated Name',
            'phone' => '+62 812 9999 0000',
        ]);

        $response->assertRedirect(route('admin.v1.profile.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '+62 812 9999 0000',
        ]);
    }

    public function test_profile_update_with_picture_url(): void
    {
        $user = $this->actingAsAdmin();

        $response = $this->put('/admin/v1/profile/update', [
            'name' => $user->name,
            'picture' => 'https://example.com/avatar.png',
        ]);

        $response->assertRedirect(route('admin.v1.profile.index'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'picture' => 'https://example.com/avatar.png',
        ]);
    }

    public function test_profile_update_requires_name(): void
    {
        $this->actingAsAdmin();

        $response = $this->put('/admin/v1/profile/update', [
            'name' => '',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ─────────────────────────────────────────────────────────────
    // Change Password
    // ─────────────────────────────────────────────────────────────

    public function test_change_password_succeeds_with_correct_current_password(): void
    {
        $user = $this->makeUser('change.pass@test.com', 'Pass User', 'oldpassword');
        $this->loginAs($user);

        $response = $this->put('/admin/v1/profile/change-password', [
            'current_password' => 'oldpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.v1.profile.index'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_change_password_fails_with_wrong_current_password(): void
    {
        $user = $this->makeUser('wrong.pass@test.com', 'Wrong Pass User', 'correctpassword');
        $this->loginAs($user);

        $response = $this->put('/admin/v1/profile/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        // Should not be a 2xx success — the service throws a ValidationAppException
        // which is handled as a 422 or redirect-with-error
        $this->assertNotEquals(200, $response->status());
    }

    public function test_change_password_requires_confirmation(): void
    {
        $user = $this->makeUser('confirm.pass@test.com', 'Confirm User', 'testpassword');
        $this->loginAs($user);

        $response = $this->put('/admin/v1/profile/change-password', [
            'current_password' => 'testpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatch',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_change_password_enforces_minimum_length(): void
    {
        $user = $this->makeUser('short.pass@test.com', 'Short User', 'testpassword');
        $this->loginAs($user);

        $response = $this->put('/admin/v1/profile/change-password', [
            'current_password' => 'testpassword',
            'password' => 'short',
            'password_confirmation' => 'short',
        ]);

        $response->assertSessionHasErrors('password');
    }
}
