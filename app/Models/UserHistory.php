<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserHistory extends Model
{
    use HasFactory;

    protected $table = 'user_histories';
    protected $primaryKey = 'user_history_id';

    protected $fillable = [
        'user_id',
        'user_history_type',
        'user_history_name',
        'user_history_points',
        'user_history_is_negative',
    ];

    protected $casts = [
        'user_history_points' => 'integer',
        'user_history_is_negative' => 'boolean',
    ];

    const TYPE_MISSION = 'mission';
    const TYPE_VALIDATION = 'validation';
    const TYPE_REDEEM = 'redeem';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}