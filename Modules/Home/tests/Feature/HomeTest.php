<?php

namespace Modules\Home\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_root_loads(): void
    {
        $response = $this->get('/');
        // The home page returns 200 with the landing HTML
        $response->assertStatus(200);
    }

    public function test_home_index_loads(): void
    {
        $response = $this->get('/home');
        $response->assertStatus(200);
    }
}
