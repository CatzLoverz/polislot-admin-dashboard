<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMission extends Model
{
    use HasFactory;

    protected $table = 'user_missions';
    protected $primaryKey = 'user_mission_id';
    public $timestamps = false; // Tidak ada created_at & updated_at

    protected $fillable = [
        'user_id',
        'mission_id',
        'progress_value',
        'streak_count',
        'last_completed_date',
        'is_completed',
        'is_claimed',
        'completed_at',
        'claimed_at',
    ];

    protected $casts = [
        'progress_value' => 'integer',
        'streak_count' => 'integer',
        'is_completed' => 'boolean',
        'is_claimed' => 'boolean',
        'last_completed_date' => 'date',
        'completed_at' => 'datetime',
        'claimed_at' => 'datetime',
    ];

    /**
     * Relasi ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke Mission.
     */
    public function mission()
    {
        return $this->belongsTo(Mission::class, 'mission_id', 'mission_id');
    }

    /**
     * Scope untuk mission yang sudah completed.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Scope untuk mission yang belum diklaim.
     */
    public function scopeUnclaimed($query)
    {
        return $query->where('is_completed', true)
                     ->where('is_claimed', false);
    }

    /**
     * Scope untuk mission yang sudah diklaim.
     */
    public function scopeClaimed($query)
    {
        return $query->where('is_claimed', true);
    }

    /**
     * Check apakah mission sudah completed.
     */
    public function isCompleted()
    {
        return $this->is_completed;
    }

    /**
     * Check apakah reward sudah diklaim.
     */
    public function isClaimed()
    {
        return $this->is_claimed;
    }

    /**
     * Check apakah bisa diklaim (completed tapi belum claimed).
     */
    public function canClaim()
    {
        return $this->is_completed && !$this->is_claimed;
    }

    /**
     * Get progress percentage.
     */
    public function getProgressPercentage()
    {
        if (!$this->mission) {
            return 0;
        }

        return round(($this->progress_value / $this->mission->target_value) * 100, 2);
    }
}