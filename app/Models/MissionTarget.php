<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionTarget extends Model
{
    use HasFactory;

    protected $table = 'mission_targets';
    protected $primaryKey = 'mission_target_id';

    protected $fillable = [
        'mission_id',
        'mission_target_amount',
    ];

    protected $casts = [
        'mission_target_amount' => 'integer',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'mission_id');
    }
}