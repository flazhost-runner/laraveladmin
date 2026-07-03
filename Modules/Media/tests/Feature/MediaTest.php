<?php

namespace Modules\Media\Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * 1x1 transparent PNG (valid binary) agar deteksi mime finfo akurat tanpa butuh ekstensi GD.
     */
    private const PNG_1X1 = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

    private function actingAsAdmin(): User
    {
        $role = Role::firstOrCreate(
            ['name' => 'Administrator'],
            ['guard_name' => 'web', 'status' => 'Active']
        );

        $user = User::create([
            'name' => 'Test Admin',
            'email' => 'admin@media.test',
            'password' => bcrypt('password'),
            'status' => 'Active',
            'code' => 'USR-MED01',
        ]);
        $user->roles()->sync([$role->id]);

        $this->withSession(['user_id' => $user->id]);

        return $user;
    }

    public function test_media_list_requires_auth(): void
    {
        $response = $this->get('/admin/v1/media/list');
        $response->assertRedirect();
    }

    public function test_media_list_returns_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/sample.png', base64_decode(self::PNG_1X1));

        $this->actingAsAdmin();
        $response = $this->getJson('/admin/v1/media/list');

        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.0.key', 'media/sample.png')
            ->assertJsonPath('data.0.name', 'sample.png');
    }

    public function test_media_upload_stores_image(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->createWithContent('photo.png', base64_decode(self::PNG_1X1));
        $response = $this->post('/admin/v1/media/upload', ['file' => $file]);

        $response->assertOk()->assertJsonPath('status', true);

        $key = $response->json('data.key');
        $this->assertNotEmpty($key);
        Storage::disk('public')->assertExists($key);
    }

    public function test_media_upload_rejects_missing_file(): void
    {
        $this->actingAsAdmin();

        $this->post('/admin/v1/media/upload')
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_media_upload_rejects_non_image(): void
    {
        Storage::fake('public');
        $this->actingAsAdmin();

        $file = UploadedFile::fake()->createWithContent('doc.txt', 'plain text, bukan gambar');

        $this->post('/admin/v1/media/upload', ['file' => $file])
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }

    public function test_media_delete_removes_file(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('media/to-delete.png', base64_decode(self::PNG_1X1));

        $this->actingAsAdmin();
        $this->post('/admin/v1/media/delete', ['key' => 'media/to-delete.png'])
            ->assertOk()
            ->assertJsonPath('status', true);

        Storage::disk('public')->assertMissing('media/to-delete.png');
    }

    public function test_media_delete_requires_key(): void
    {
        $this->actingAsAdmin();

        $this->post('/admin/v1/media/delete')
            ->assertStatus(422)
            ->assertJsonPath('status', false);
    }
}
