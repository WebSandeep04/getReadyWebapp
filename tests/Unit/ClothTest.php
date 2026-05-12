<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Cloth;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ClothTest extends TestCase
{
    use RefreshDatabase;

    public function test_cloth_belongs_to_user()
    {
        $user = User::factory()->create();
        $cloth = Cloth::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $cloth->user);
        $this->assertEquals($user->id, $cloth->user->id);
    }

    public function test_cloth_has_availability_status()
    {
        $cloth = Cloth::factory()->create(['is_available' => true]);
        $this->assertTrue($cloth->is_available);

        $cloth->update(['is_available' => false]);
        $this->assertFalse($cloth->fresh()->is_available);
    }
}
