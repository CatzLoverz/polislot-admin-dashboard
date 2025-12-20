<?php

namespace Tests\Feature\Models;

use App\Models\Reward;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RewardTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function reward_dapat_dibuat()
    {
        $reward = Reward::create([
            'reward_name' => 'Voucher 50K',
            'reward_type' => 'Voucher',
            'reward_point_required' => 50,
            'reward_image' => 'img.jpg'
        ]);

        $this->assertDatabaseHas('rewards', [
            'reward_name' => 'Voucher 50K',
            'reward_point_required' => 50
        ]);
    }

    #[Test]
    public function reward_dapat_diupdate()
    {
        $reward = Reward::create([
            'reward_name' => 'Old',
            'reward_type' => 'Barang',
            'reward_point_required' => 10,
            'reward_image' => 'img.jpg'
        ]);

        $reward->update(['reward_point_required' => 20]);

        $this->assertDatabaseHas('rewards', [
            'reward_id' => $reward->reward_id,
            'reward_point_required' => 20
        ]);
    }

    #[Test]
    public function reward_dapat_dihapus()
    {
        $reward = Reward::create([
            'reward_name' => 'Del',
            'reward_type' => 'Voucher',
            'reward_point_required' => 5,
            'reward_image' => 'img.jpg'
        ]);
        $reward->delete();
        $this->assertDatabaseMissing('rewards', ['reward_id' => $reward->reward_id]);
    }
}
