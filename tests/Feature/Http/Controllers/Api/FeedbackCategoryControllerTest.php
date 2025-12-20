<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\FeedbackCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedbackCategoryControllerTest extends TestCase
{
    use \Illuminate\Foundation\Testing\WithoutMiddleware;
    use RefreshDatabase;

    #[Test]
    public function index_returns_200_and_list()
    {
        $user = User::factory()->create();
        FeedbackCategory::create(['fbk_category_name' => 'General']);
        FeedbackCategory::create(['fbk_category_name' => 'Bug']);

        $this->actingAs($user);

        $response = $this->getJson('/api/feedback-categories');

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonCount(2, 'data');
    }
}
