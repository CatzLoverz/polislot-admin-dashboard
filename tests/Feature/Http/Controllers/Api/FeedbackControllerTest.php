<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\FeedbackCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedbackControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mock(\App\Services\MissionService::class);
    }

    #[Test]
    public function store_returns_201_when_successful()
    {
        $user = User::factory()->create();
        $cat = FeedbackCategory::create(['fbk_category_name' => 'General']);
        
        $this->actingAs($user);

        $response = $this->postJson('/api/feedback', [
            'category' => $cat->fbk_category_id,
            'title' => 'Test Feedback',
            'description' => 'Description'
        ]);

        $response->assertStatus(201)
                 ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('feedbacks', [
            'feedback_title' => 'Test Feedback'
        ]);
    }

    #[Test]
    public function store_returns_422_if_validation_fails()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/feedback', []);
        
        $response->assertStatus(422)
                 ->assertJsonStructure(['status', 'errors']);
    }
}
