<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mission extends Model
{
    use HasFactory;

    protected $table = 'missions';
    protected $primaryKey = 'mission_id';
    public $timestamps = true;

    // DEFINISI KONSTANTA METRIC (Agar konsisten di Controller & View)
    public const METRICS = [
        'VALIDATION_STREAK' => 'Validasi Berturut-turut (Sequence)',
        'VALIDATION_TOTAL'  => 'Total Validasi (Target)',
        'PROFILE_UPDATE'    => 'Lengkapi Profil (Target)',
        'LOGIN_APP'         => 'Login Aplikasi (Sequence)',
    ];

    public const METRIC_TYPES = [
        'TARGET' => [
            'VALIDATION_TOTAL', 
            'PROFILE_UPDATE', 
        ],
        'SEQUENCE' => [
            'VALIDATION_STREAK', 
            'LOGIN_APP'
        ],
    ];

    protected $fillable = [
        'mission_title',
        'mission_description',
        'mission_points',
        'mission_type',        // ENUM: TARGET, SEQUENCE
        'mission_metric_code', // ENUM: VALIDATION_STREAK, dll
        'mission_is_active',
        'mission_start_date',
        'mission_end_date',
    ];

    protected $casts = [
        'mission_points'     => 'integer',
        'mission_is_active'  => 'boolean',
        'mission_start_date' => 'datetime',
        'mission_end_date'   => 'datetime',
    ];

    // --- RELASI ---

    public function missionTarget(): HasOne
    {
        return $this->hasOne(MissionTarget::class, 'mission_id', 'mission_id');
    }

    public function missionSequence(): HasOne
    {
        return $this->hasOne(MissionSequence::class, 'mission_id', 'mission_id');
    }

    public function userMissions(): HasMany
    {
        return $this->hasMany(UserMission::class, 'mission_id', 'mission_id');
    }
}