<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\InfoBoard;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class InfoBoardControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    #[Test]
    public function index_returns_200_and_info_list()
    {
        $user = User::factory()->create();
        InfoBoard::create([
            'user_id' => $user->user_id,
            'info_title' => 'Info',
            'info_content' => 'Content'
        ]);

        $this->actingAs($user);

        $response = $this->getJson('/api/info-board');

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }
}
