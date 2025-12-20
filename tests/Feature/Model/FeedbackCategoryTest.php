<?php

namespace Tests\Feature\Models;

use App\Models\FeedbackCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedbackCategoryTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function kategori_feedback_dapat_dibuat()
    {
        $category = FeedbackCategory::create(['fbk_category_name' => 'UI/UX']);

        $this->assertDatabaseHas('feedback_categories', [
            'fbk_category_name' => 'UI/UX'
        ]);
    }

    #[Test]
    public function kategori_feedback_dapat_dibaca()
    {
        $category = FeedbackCategory::create(['fbk_category_name' => 'Read Test']);
        
        $found = FeedbackCategory::find($category->fbk_category_id);
        
        $this->assertNotNull($found);
        $this->assertEquals('Read Test', $found->fbk_category_name);
    }

    #[Test]
    public function kategori_feedback_dapat_diupdate()
    {
        $category = FeedbackCategory::create(['fbk_category_name' => 'Old Name']);
        $category->update(['fbk_category_name' => 'New Name']);

        $this->assertDatabaseHas('feedback_categories', [
            'fbk_category_id' => $category->fbk_category_id,
            'fbk_category_name' => 'New Name'
        ]);
    }

    #[Test]
    public function kategori_feedback_dapat_dihapus()
    {
        $category = FeedbackCategory::create(['fbk_category_name' => 'To Delete']);
        $category->delete();

        $this->assertDatabaseMissing('feedback_categories', ['fbk_category_id' => $category->fbk_category_id]);
    }
}
