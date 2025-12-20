<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class HistoryControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    #[Test]
    public function index_returns_200_and_history_list()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        
        UserHistory::create([
            'user_id' => $user->user_id,
            'user_history_type' => 'mission',
            'user_history_name' => 'Test History',
            'user_history_points' => 10,
            'user_history_is_negative' => false
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }
}
