<?php

namespace Tests\Feature\Models;

use App\Models\Feedback;
use App\Models\FeedbackCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FeedbackTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function feedback_dapat_dibuat()
    {
        $user = User::factory()->create();
        $category = FeedbackCategory::create(['fbk_category_name' => 'General']);

        $feedback = Feedback::create([
            'fbk_category_id' => $category->fbk_category_id,
            'feedback_title' => 'Test Feedback',
            'feedback_description' => 'Ini adalah feedback test',
        ]);

        $this->assertDatabaseHas('feedbacks', [
            'feedback_id' => $feedback->feedback_id,
            'feedback_title' => 'Test Feedback',
        ]);
    }

    #[Test]
    public function feedback_dapat_dibaca()
    {
        $user = User::factory()->create();
        $category = FeedbackCategory::create(['fbk_category_name' => 'Bug']);
        $feedback = Feedback::create([
            'fbk_category_id' => $category->fbk_category_id,
            'feedback_title' => 'Read Test',
            'feedback_description' => 'Reading...'
        ]);

        $found = Feedback::find($feedback->feedback_id);
        $this->assertNotNull($found);
        $this->assertEquals('Read Test', $found->feedback_title);
    }

    #[Test]
    public function feedback_dapat_diupdate()
    {
        $user = User::factory()->create();
        $category = FeedbackCategory::create(['fbk_category_name' => 'Feature']);
        $feedback = Feedback::create([
            'fbk_category_id' => $category->fbk_category_id,
            'feedback_title' => 'Old Title',
            'feedback_description' => 'Old Desc'
        ]);

        $feedback->update(['feedback_title' => 'New Title']);

        $this->assertDatabaseHas('feedbacks', [
            'feedback_id' => $feedback->feedback_id,
            'feedback_title' => 'New Title'
        ]);
    }

    #[Test]
    public function feedback_dapat_dihapus()
    {
        $user = User::factory()->create();
        $category = FeedbackCategory::create(['fbk_category_name' => 'Delete']);
        $feedback = Feedback::create([
            'fbk_category_id' => $category->fbk_category_id,
            'feedback_title' => 'To Delete',
            'feedback_description' => '...'
        ]);

        $feedback->delete();

        $this->assertDatabaseMissing('feedbacks', ['feedback_id' => $feedback->feedback_id]);
    }
}
