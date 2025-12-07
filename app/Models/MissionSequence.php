<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionSequence extends Model
{
    use HasFactory;

    protected $table = 'mission_sequences';
    protected $primaryKey = 'mission_sequence_id';

    protected $fillable = [
        'mission_id',
        'mission_days_required',
        'mission_is_consecutive',
        'mission_reset_time',
    ];

    protected $casts = [
        'mission_days_required'  => 'integer',
        'mission_is_consecutive' => 'boolean',
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'mission_id');
    }
}