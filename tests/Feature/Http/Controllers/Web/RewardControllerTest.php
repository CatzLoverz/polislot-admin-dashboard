<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RewardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function index_returns_view()
    {
        $response = $this->actingAs($this->admin)->get('/admin/rewards');
        $response->assertStatus(200)->assertViewIs('Contents.Rewards.index');
    }

    #[Test]
    public function store_creates_reward()
    {
        $response = $this->actingAs($this->admin)->from('/admin/rewards')->post('/admin/rewards', [
            'reward_name' => 'Test Reward',
            'reward_description' => 'Desc',
            'reward_type' => 'Voucher',
            'reward_point_required' => 50,
            'reward_stock' => 10,
        ]);
        $response->assertRedirect('/admin/rewards')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseHas('rewards', ['reward_name' => 'Test Reward']);
    }

    #[Test]
    public function update_modifies_reward()
    {
        $reward = Reward::create([
            'reward_name' => 'Old',
            'reward_description' => 'Desc',
            'reward_type' => 'Voucher',
            'reward_point_required' => 50,
            'reward_stock' => 10,
        ]);

        $response = $this->actingAs($this->admin)->from('/admin/rewards')->put("/admin/rewards/{$reward->reward_id}", [
            'reward_name' => 'New',
            'reward_description' => 'Desc',
            'reward_type' => 'Voucher',
            'reward_point_required' => 100,
            'reward_stock' => 20,
        ]);

        $response->assertRedirect('/admin/rewards')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseHas('rewards', ['reward_name' => 'New']);
    }

    #[Test]
    public function destroy_deletes_reward()
    {
        $reward = Reward::create([
            'reward_name' => 'To Delete',
            'reward_description' => 'Desc',
            'reward_type' => 'Voucher',
            'reward_point_required' => 50,
            'reward_stock' => 10,
        ]);

        $response = $this->actingAs($this->admin)->from('/admin/rewards')->delete("/admin/rewards/{$reward->reward_id}");
        $response->assertRedirect('/admin/rewards')->assertSessionHas('swal_success_crud');
        $this->assertDatabaseMissing('rewards', ['reward_id' => $reward->reward_id]);
    }
}
