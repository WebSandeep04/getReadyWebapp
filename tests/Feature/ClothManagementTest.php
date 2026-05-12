<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ClothManagementTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
    public function test_user_can_view_listed_clothes(): void
    {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)->get(route('listed.clothes'));

        $response->assertStatus(200);
    }
}
