<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubareaCommentControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    #[Test]
    public function index_returns_200_and_list()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        
        $this->actingAs($user);
        
        $response = $this->getJson("/api/comment?park_subarea_id={$sub->park_subarea_id}");
        
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success'])
                 ->assertJsonStructure(['data' => ['list', 'pagination']]);
    }

    #[Test]
    public function index_returns_validation_error_if_no_id()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson('/api/comment');
        
        $response->assertStatus(422);
    }

    #[Test]
    public function store_comment_berhasil_return_201()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $this->actingAs($user);

        $response = $this->postJson('/api/comment', [
            'park_subarea_id' => $sub->park_subarea_id,
            'subarea_comment_content' => 'Parkiran penuh',
            'subarea_comment_image' => UploadedFile::fake()->image('bukti.jpg')
        ]);

        $response->assertStatus(201)
                 ->assertJson(['status' => 'success', 'message' => 'Komentar terkirim.']);
        
        $this->assertDatabaseHas('subarea_comments', [
            'park_subarea_id' => $sub->park_subarea_id,
            'subarea_comment_content' => 'Parkiran penuh'
        ]);
    }

    #[Test]
    public function update_comment_berhasil_return_200()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        
        // Buat comment existing
        $this->actingAs($user);
        $post = $this->postJson('/api/comment', [
            'park_subarea_id' => $sub->park_subarea_id,
            'subarea_comment_content' => 'Old Content',
            'subarea_comment_image' => UploadedFile::fake()->image('old.jpg')
        ]);
        $commentId = $post->json('data.subarea_comment_id');

        // Update
        $response = $this->putJson("/api/comment/{$commentId}", [
            'subarea_comment_content' => 'New Content'
        ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Komentar berhasil diperbarui.']);
        
        $this->assertDatabaseHas('subarea_comments', [
            'subarea_comment_id' => $commentId,
            'subarea_comment_content' => 'New Content'
        ]);
    }

    #[Test]
    public function destroy_comment_berhasil_return_200()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        
        $this->actingAs($user);
        $post = $this->postJson('/api/comment', [
            'park_subarea_id' => $sub->park_subarea_id,
            'subarea_comment_content' => 'To Delete',
            'subarea_comment_image' => UploadedFile::fake()->image('del.jpg')
        ]);
        $commentId = $post->json('data.subarea_comment_id');

        $response = $this->deleteJson("/api/comment/{$commentId}");

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Komentar berhasil dihapus.']);
        
        $this->assertDatabaseMissing('subarea_comments', ['subarea_comment_id' => $commentId]);
    }

    #[Test]
    public function update_fails_if_not_owner()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        
        $this->actingAs($user1);
        $post = $this->postJson('/api/comment', [
            'park_subarea_id' => $sub->park_subarea_id,
            'subarea_comment_content' => 'User 1 Content',
        ]);
        $commentId = $post->json('data.subarea_comment_id');

        // User 2 tries to update
        $this->actingAs($user2);
        $response = $this->putJson("/api/comment/{$commentId}", [
            'subarea_comment_content' => 'Hacked Content'
        ]);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Anda tidak berhak mengedit komentar ini.']);
    }

    #[Test]
    public function destroy_fails_if_not_owner()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        
        $this->actingAs($user1);
        $post = $this->postJson('/api/comment', [
            'park_subarea_id' => $sub->park_subarea_id,
            'subarea_comment_content' => 'User 1 Delete',
        ]);
        $commentId = $post->json('data.subarea_comment_id');

        // User 2 tries to delete
        $this->actingAs($user2);
        $response = $this->deleteJson("/api/comment/{$commentId}");

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Anda tidak berhak menghapus komentar ini.']);
    }
}
