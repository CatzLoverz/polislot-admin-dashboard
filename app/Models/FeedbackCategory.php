<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeedbackCategory extends Model
{
    protected $table = 'feedback_categories';
    protected $primaryKey = 'fbk_category_id';
    public $timestamps = true;
    
    protected $fillable = [
        'fbk_category_name',
    ];

    public function feedback()
    {
        return $this->hasMany(Feedback::class, 'fbk_category_id', 'fbk_category_id');
    }
}
