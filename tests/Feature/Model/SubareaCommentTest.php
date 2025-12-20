<?php

namespace Tests\Feature\Models;

use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\SubareaComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SubareaCommentTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function comment_dapat_dibuat()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $comment = SubareaComment::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'user_id' => $user->user_id,
            'subarea_comment_content' => 'Full banget',
            'subarea_comment_image' => 'img.jpg',
        ]);

        $this->assertDatabaseHas('subarea_comments', [
            'subarea_comment_content' => 'Full banget',
            'user_id' => $user->user_id,
        ]);
    }

    #[Test]
    public function comment_dapat_dibaca()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $comment = SubareaComment::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'user_id' => $user->user_id,
            'subarea_comment_content' => 'Read',
        ]);

        $found = SubareaComment::find($comment->subarea_comment_id);
        $this->assertNotNull($found);
        $this->assertEquals('Read', $found->subarea_comment_content);
    }

    #[Test]
    public function comment_dapat_diupdate()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $comment = SubareaComment::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'user_id' => $user->user_id,
            'subarea_comment_content' => 'Old',
        ]);

        $comment->update(['subarea_comment_content' => 'New']);

        $this->assertDatabaseHas('subarea_comments', [
            'subarea_comment_id' => $comment->subarea_comment_id,
            'subarea_comment_content' => 'New',
        ]);
    }

    #[Test]
    public function comment_dapat_dihapus()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $comment = SubareaComment::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'user_id' => $user->user_id,
            'subarea_comment_content' => 'Delete',
        ]);

        $comment->delete();

        $this->assertDatabaseMissing('subarea_comments', ['subarea_comment_id' => $comment->subarea_comment_id]);
    }
}
