<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mission extends Model
{
    use HasFactory;

    protected $primaryKey = 'mission_id';
    
    // Konstanta untuk UI
    public const METRICS = [
        'VALIDATION_STREAK' => 'Validasi (Harian/Streak)',
        'VALIDATION_TOTAL'  => 'Validasi (Total Akumulasi)',
        'PROFILE_UPDATE'    => 'Update Profil',
        'LOGIN_APP'         => 'Login Aplikasi',
    ];

    public const CYCLES = [
        'NONE'    => 'Sekali Saja (Tidak Reset)',
        'DAILY'   => 'Harian (Reset 00:00)',
        'WEEKLY'  => 'Mingguan (Reset Senin)',
        'MONTHLY' => 'Bulanan (Reset tgl 1)',
    ];

    protected $fillable = [
        'mission_title',
        'mission_description',
        'mission_points',
        'mission_type',        // TARGET / SEQUENCE
        'mission_reset_cycle', // DAILY / WEEKLY...
        'mission_metric_code', // LOGIN / VALIDATION...
        'mission_threshold',   // Angka Target / Hari
        'mission_is_consecutive', // Boolean
        'mission_is_active',
    ];

    protected $casts = [
        'mission_points' => 'integer',
        'mission_threshold' => 'integer',
        'mission_is_consecutive' => 'boolean',
        'mission_is_active' => 'boolean',
    ];

    // Relasi ke Tracker User
    public function userMissions()
    {
        return $this->hasMany(UserMission::class, 'mission_id', 'mission_id');
    }
}