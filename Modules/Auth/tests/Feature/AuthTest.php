<?php

namespace Modules\Auth\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_loads(): void
    {
        $response = $this->get('/auth/login');
        $response->assertOk();
    }

    public function test_valid_login_redirects_to_dashboard(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $response = $this->post('/auth/login', ['email' => $user->email, 'password' => 'password123']);
        $response->assertRedirect('/admin/v1/dashboard');
    }

    public function test_invalid_login_returns_error(): void
    {
        $response = $this->post('/auth/login', ['email' => 'no@one.test', 'password' => 'wrongpass']);
        $response->assertSessionHas('error');
    }

    public function test_api_login_returns_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $response = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password123']);
        $response->assertOk()
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['status', 'message', 'data' => ['access_token', 'token_type', 'user']]);
    }

    public function test_api_logout_blacklists_token(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);
        $loginRes = $this->postJson('/api/v1/auth/login', ['email' => $user->email, 'password' => 'password123']);
        $token = $loginRes->json('data.access_token');
        $this->postJson('/api/v1/auth/logout', [], ['Authorization' => 'Bearer '.$token])
            ->assertOk();
        $this->getJson('/api/v1/auth/me', ['Authorization' => 'Bearer '.$token])
            ->assertStatus(401);
    }
}
