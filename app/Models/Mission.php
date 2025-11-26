<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $table = 'missions';
    protected $primaryKey = 'mission_id';
    public $timestamps = false; // Karena hanya ada created_at

    protected $fillable = [
        'mission_name',
        'description',
        'mission_type',
        'target_value',
        'reward_points',
        'period_type',
        'reset_time',
        'start_date',
        'end_date',
        'is_active',
    ];

    protected $casts = [
        'target_value' => 'integer',
        'reward_points' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke user_missions (progress user).
     */
    public function userMissions()
    {
        return $this->hasMany(UserMission::class, 'mission_id', 'mission_id');
    }

    /**
     * Scope untuk mission yang aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where('start_date', '<=', now())
                     ->where('end_date', '>=', now());
    }

    /**
     * Scope untuk filter berdasarkan tipe mission.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('mission_type', $type);
    }

    /**
     * Scope untuk mission berdasarkan period type.
     */
    public function scopeByPeriod($query, $periodType)
    {
        return $query->where('period_type', $periodType);
    }

    /**
     * Check apakah mission sedang aktif.
     */
    public function isActive()
    {
        return $this->is_active 
            && now()->between($this->start_date, $this->end_date);
    }

    /**
     * Get progress percentage untuk user tertentu.
     */
    public function getProgressForUser($userId)
    {
        $userMission = $this->userMissions()
            ->where('user_id', $userId)
            ->first();

        if (!$userMission) {
            return 0;
        }

        return round(($userMission->progress_value / $this->target_value) * 100, 2);
    }
}