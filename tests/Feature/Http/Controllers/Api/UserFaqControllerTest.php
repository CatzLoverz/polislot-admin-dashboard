<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\User;
use App\Models\UserFaq;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserFaqControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

    #[Test]
    public function index_returns_200_and_list()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        UserFaq::create(['user_id' => $user->user_id, 'faq_question' => 'Q1', 'faq_answer' => 'A1']);

        $response = $this->getJson('/api/user-faq');
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Data FAQ berhasil diambil.']);
    }

    #[Test]
    public function index_returns_200_when_empty()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/user-faq');
        $response->assertStatus(200)
            ->assertJson(['status' => 'success', 'message' => 'Tidak ada FAQ tersedia.']);
    }
}
