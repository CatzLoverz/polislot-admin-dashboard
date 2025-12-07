<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMission extends Model
{
    use HasFactory;

    protected $table = 'user_missions';
    protected $primaryKey = 'user_mission_id';
    public $timestamps = true;

    protected $fillable = [
        'user_id',
        'mission_id',
        'user_mission_current_value',
        'user_mission_is_completed',
        'user_mission_completed_at',
    ];

    protected $casts = [
        'user_mission_current_value'  => 'integer',
        'user_mission_is_completed'   => 'boolean',
        'user_mission_completed_at'   => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'mission_id');
    }
}