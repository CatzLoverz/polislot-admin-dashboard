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
        'VALIDATION_ACTION' => 'Melakukan Validasi Parkir',
        'LOGIN_ACTION'      => 'Membuka Aplikasi (Login)',
        'PROFILE_UPDATE'    => 'Memperbarui Profil (Avatar)',
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
        'mission_type',
        'mission_reset_cycle',
        'mission_metric_code',
        'mission_threshold',
        'mission_is_consecutive',
        'mission_is_active',
    ];

    protected $casts = [
        'mission_points' => 'integer',
        'mission_threshold' => 'integer',
        'mission_is_consecutive' => 'boolean',
        'mission_is_active' => 'boolean',
    ];

    public function userMissions()
    {
        return $this->hasMany(UserMission::class, 'mission_id', 'mission_id');
    }
}