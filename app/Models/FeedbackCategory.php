<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeedbackCategory extends Model
{
    protected $table = 'feedback_categories';
    protected $primaryKey = 'fbk_category_id';
    public $timestamps = true;
    
    protected $fillable = [
        'fbk_category_name',
    ];

    /**
     * Relasi ke feedback yang terkait dengan kategori ini.
     *
     * @return HasMany
     */
    public function feedbacks(): HasMany
    {
        return $this->hasMany(Feedback::class, 'fbk_category_id', 'fbk_category_id');
    }
}
