<?php

namespace Tests\Feature\Models;

use App\Models\Reward;
use App\Models\User;
use App\Models\UserReward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserRewardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_reward_dapat_dibuat()
    {
        $user = User::factory()->create();
        $reward = Reward::create([
            'reward_name' => 'Voucher',
            'reward_type' => 'Voucher',
            'reward_point_required' => 10,
            'reward_image' => 'im.jpg',
        ]);

        $userReward = UserReward::create([
            'user_id' => $user->user_id,
            'reward_id' => $reward->reward_id,
            'user_reward_code' => 'CODE123',
            'user_reward_status' => 'pending',
        ]);

        $this->assertDatabaseHas('user_rewards', [
            'user_id' => $user->user_id,
            'user_reward_code' => 'CODE123',
        ]);
    }

    #[Test]
    public function user_reward_dapat_dibaca()
    {
        $user = User::factory()->create();
        $reward = Reward::create(['reward_name' => 'Voucher', 'reward_type' => 'Voucher', 'reward_point_required' => 10, 'reward_image' => 'i.jpg']);
        $ur = UserReward::create(['user_id' => $user->user_id, 'reward_id' => $reward->reward_id, 'user_reward_code' => 'R1', 'user_reward_status' => 'pending']);

        $found = UserReward::find($ur->user_reward_id);
        $this->assertNotNull($found);
        $this->assertEquals('R1', $found->user_reward_code);
    }

    #[Test]
    public function user_reward_dapat_diupdate()
    {
        $user = User::factory()->create();
        $reward = Reward::create(['reward_name' => 'Voucher', 'reward_type' => 'Voucher', 'reward_point_required' => 10, 'reward_image' => 'i.jpg']);
        $ur = UserReward::create(['user_id' => $user->user_id, 'reward_id' => $reward->reward_id, 'user_reward_code' => 'R1', 'user_reward_status' => 'pending']);

        $ur->update(['user_reward_status' => 'accepted']);

        $this->assertDatabaseHas('user_rewards', [
            'user_reward_id' => $ur->user_reward_id,
            'user_reward_status' => 'accepted',
        ]);
    }

    #[Test]
    public function user_reward_dapat_dihapus()
    {
        $user = User::factory()->create();
        $reward = Reward::create(['reward_name' => 'Voucher', 'reward_type' => 'Voucher', 'reward_point_required' => 10, 'reward_image' => 'i.jpg']);
        $ur = UserReward::create(['user_id' => $user->user_id, 'reward_id' => $reward->reward_id, 'user_reward_code' => 'DEL', 'user_reward_status' => 'pending']);

        $ur->delete();

        $this->assertDatabaseMissing('user_rewards', ['user_reward_id' => $ur->user_reward_id]);
    }
}
