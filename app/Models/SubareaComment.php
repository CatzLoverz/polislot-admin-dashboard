<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubareaComment extends Model
{
    protected $table = 'subarea_comments';
    protected $primaryKey = 'subarea_comment_id';
    
    protected $fillable = [
        'user_id',
        'park_subarea_id',
        'subarea_comment_content',
        'subarea_comment_image'
    ];

    /**
     * Relasi ke user pembuat komentar.
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke subarea parkir yang dikomentari.
     *
     * @return BelongsTo
     */
    public function parkSubarea(): BelongsTo
    {
        return $this->belongsTo(ParkSubarea::class, 'park_subarea_id', 'park_subarea_id');
    }
}