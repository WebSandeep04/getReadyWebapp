<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;
    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_mobile_signup_flow(): void
    {
        $phone = '9876543210';
        $token = \Illuminate\Support\Str::random(32);
        
        // Simulate verified OTP token in cache
        \Illuminate\Support\Facades\Cache::put('signup_verified_' . $phone, $token, now()->addMinutes(15));

        $response = $this->post('/register', [
            'phone' => $phone,
            'verification_token' => $token,
            'city' => 'Mumbai',
            'age' => 25,
            'gender' => 'Men',
            'is_gst' => 0,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect('/');
        
        $this->assertDatabaseHas('users', [
            'phone' => $phone,
            'city' => 'Mumbai',
            'age' => 25,
            'gender' => 'Men',
            'is_gst' => 0,
        ]);
    }

    public function test_mobile_signup_flow_with_gst(): void
    {
        $phone = '9876543211';
        $token = \Illuminate\Support\Str::random(32);
        
        \Illuminate\Support\Facades\Cache::put('signup_verified_' . $phone, $token, now()->addMinutes(15));

        $response = $this->post('/register', [
            'phone' => $phone,
            'verification_token' => $token,
            'city' => 'Delhi',
            'age' => 30,
            'gender' => 'Women',
            'is_gst' => 1,
            'gstin' => '27ABCDE1234F1Z5',
        ]);

        $this->assertAuthenticated();
        
        $user = \App\Models\User::where('phone', $phone)->first();
        $this->assertNotNull($user);
        $this->assertTrue((bool)$user->is_gst);
        $this->assertEquals('27ABCDE1234F1Z5', $user->gst_number);
    }
}
