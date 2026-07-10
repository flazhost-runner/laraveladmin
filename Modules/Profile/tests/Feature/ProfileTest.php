<?php

namespace Modules\Profile\Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
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

    /** Payload lengkap sesuai kontrak form NodeAdmin (code/name/email/status wajib). */
    private function validPayload(User $user, array $overrides = []): array
    {
        return array_merge([
            'code' => $user->code,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'timezone' => 'Asia/Jakarta',
            'status' => 'Active',
        ], $overrides);
    }

    public function test_profile_update_changes_name_and_phone(): void
    {
        $user = $this->actingAsAdmin();

        $response = $this->put('/admin/v1/profile/update', $this->validPayload($user, [
            'name' => 'Updated Name',
            'phone' => '+62 812 9999 0000',
        ]));

        // Paritas NodeAdmin: sukses redirect ke dashboard.
        $response->assertRedirect(route('admin.v1.dashboard.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
            'phone' => '+62 812 9999 0000',
            'timezone' => 'Asia/Jakarta',
        ]);
    }

    public function test_profile_update_uploads_picture_file(): void
    {
        Storage::fake('public');
        $user = $this->actingAsAdmin();

        $response = $this->put('/admin/v1/profile/update', $this->validPayload($user, [
            'picture' => UploadedFile::fake()->image('avatar.png', 100, 100),
        ]));

        $response->assertRedirect(route('admin.v1.dashboard.index'));

        // Kunci deterministik per user + konversi webp (paritas NodeAdmin fileService).
        $expectedKey = 'modules/access/user/'.$user->id.'.webp';
        $this->assertDatabaseHas('users', ['id' => $user->id, 'picture' => $expectedKey]);
        Storage::disk('public')->assertExists($expectedKey);
    }

    public function test_profile_update_rejects_picture_url_string(): void
    {
        $user = $this->actingAsAdmin();

        $response = $this->put('/admin/v1/profile/update', $this->validPayload($user, [
            'picture' => 'https://example.com/avatar.png',
        ]));

        // picture kini FILE upload — string URL harus ditolak validasi.
        $response->assertSessionHasErrors('picture');
    }

    public function test_profile_update_requires_name_code_email_status(): void
    {
        $this->actingAsAdmin();

        $response = $this->put('/admin/v1/profile/update', [
            'name' => '',
            'code' => '',
            'email' => '',
            'status' => '',
        ]);

        $response->assertSessionHasErrors(['name', 'code', 'email', 'status']);
    }

    // ─────────────────────────────────────────────────────────────
    // Password inline di form update (paritas NodeAdmin)
    // ─────────────────────────────────────────────────────────────

    public function test_profile_update_changes_password_inline(): void
    {
        $user = $this->makeUser('change.pass@test.com', 'Pass User', 'oldpassword');
        $this->loginAs($user);

        $response = $this->put('/admin/v1/profile/update', $this->validPayload($user, [
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]));

        $response->assertRedirect(route('admin.v1.dashboard.index'));

        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }

    public function test_profile_update_password_requires_confirmation(): void
    {
        $user = $this->makeUser('confirm.pass@test.com', 'Confirm User', 'testpassword');
        $this->loginAs($user);

        $response = $this->put('/admin/v1/profile/update', $this->validPayload($user, [
            'password' => 'newpassword123',
            'password_confirmation' => 'mismatch',
        ]));

        $response->assertSessionHasErrors('password');
    }

    public function test_profile_update_password_enforces_minimum_length(): void
    {
        $user = $this->makeUser('short.pass@test.com', 'Short User', 'testpassword');
        $this->loginAs($user);

        $response = $this->put('/admin/v1/profile/update', $this->validPayload($user, [
            'password' => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertSessionHasErrors('password');
    }
}
