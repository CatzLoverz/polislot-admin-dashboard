<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $primaryKey = 'feedback_id';

    // Jika tidak pakai updated_at default (boleh null)
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'category',
        'feedback_type',
        'title',
        'description',
    ];

    /**
     * Relasi: Feedback dimiliki oleh User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
