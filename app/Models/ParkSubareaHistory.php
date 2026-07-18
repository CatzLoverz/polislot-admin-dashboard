<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParkSubareaHistory extends Model
{
    use HasFactory;

    protected $table = 'park_subarea_histories';

    protected $fillable = [
        'park_subarea_id',
        'current_count',
        'max_slots',
        'status',
    ];

    protected $casts = [
        'current_count' => 'integer',
        'max_slots' => 'integer',
    ];

    /**
     * Relasi ke ParkSubarea.
     *
     * @return BelongsTo
     */
    public function parkSubarea(): BelongsTo
    {
        return $this->belongsTo(ParkSubarea::class, 'park_subarea_id', 'park_subarea_id');
    }
}
