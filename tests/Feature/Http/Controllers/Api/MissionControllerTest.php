<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Mission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MissionControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    #[Test]
    public function index_returns_200_and_mission_data()
    {
        $user = User::factory()->create();
        Mission::create(['mission_title' => 'M1', 'mission_threshold' => 10, 'mission_is_active' => true]);

        $this->actingAs($user);

        $response = $this->getJson('/api/missions');

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success'])
                 ->assertJsonStructure(['data' => ['missions', 'stats', 'leaderboard']]);
    }
}
