<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InfoBoard extends Model
{
    protected $table = 'info_boards';
    protected $primaryKey = 'info_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'info_title',
        'info_content'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

}
