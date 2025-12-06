<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';
    protected $primaryKey = 'feedback_id';
    public $timestamps = true;

    protected $fillable = [
        'fbk_category_id',
        'feedback_title',
        'feedback_description',
    ];

    public function feedbackCategory(): BelongsTo
    {
        return $this->belongsTo(FeedbackCategory::class, 'fbk_category_id', 'fbk_category_id');
    }
}
