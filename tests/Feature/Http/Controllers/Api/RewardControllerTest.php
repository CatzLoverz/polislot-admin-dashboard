<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RewardControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        // Mock MissionService to prevent side effects
        $this->mock(\App\Services\MissionService::class);
    }

    #[Test]
    public function index_returns_200_and_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        Reward::create(['reward_name' => 'R1', 'reward_point_required' => 10, 'reward_type' => 'Voucher', 'reward_image' => 'a.jpg']);

        $response = $this->getJson('/api/rewards');
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    #[Test]
    public function redeem_returns_201_when_successful()
    {
        $user = User::factory()->create(['current_points' => 100]);
        $reward = Reward::create(['reward_name' => 'R1', 'reward_point_required' => 50, 'reward_type' => 'Voucher', 'reward_image' => 'a.jpg']);
        
        $this->actingAs($user);

        $response = $this->postJson('/api/rewards/redeem', ['reward_id' => $reward->reward_id]);

        $response->assertStatus(201)
                 ->assertJson(['status' => 'success', 'message' => 'Penukaran berhasil!']);
        
        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $user->user_id,
            'reward_id' => $reward->reward_id
        ]);
    }

    #[Test]
    public function redeem_returns_422_if_points_insufficient()
    {
        $user = User::factory()->create(['current_points' => 10]);
        $reward = Reward::create(['reward_name' => 'R1', 'reward_point_required' => 50, 'reward_type' => 'Voucher', 'reward_image' => 'a.jpg']);
        
        $this->actingAs($user);

        $response = $this->postJson('/api/rewards/redeem', ['reward_id' => $reward->reward_id]);

        $response->assertStatus(422)
                 ->assertJson(['message' => 'Poin Anda tidak mencukupi untuk reward ini.']);
    }

    #[Test]
    public function redeem_returns_422_if_reward_id_missing()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/rewards/redeem', []);
        
        $response->assertStatus(422);
    }
}
