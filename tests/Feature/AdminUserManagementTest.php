<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_admin_can_fetch_users(): void
    {
        // Assuming there isn't strict admin middleware yet, or just auth
        // We'll simulate a logged in user first
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('user.fetch'));

        $response->assertStatus(200);
    }
}
