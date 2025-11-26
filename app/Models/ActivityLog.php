<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';
    protected $primaryKey = 'log_id';
    public $timestamps = false; // Hanya ada created_at

    protected $fillable = [
        'user_id',
        'activity_type',
        'points_awarded',
        'description',
        'metadata',
    ];

    protected $casts = [
        'points_awarded' => 'integer',
        'metadata' => 'array', // Otomatis encode/decode JSON
        'created_at' => 'datetime',
    ];

    /**
     * Relasi ke User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Scope untuk filter berdasarkan activity type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Scope untuk aktivitas yang memberikan poin.
     */
    public function scopePointsEarned($query)
    {
        return $query->where('points_awarded', '>', 0);
    }

    /**
     * Scope untuk aktivitas pengeluaran poin.
     */
    public function scopePointsSpent($query)
    {
        return $query->where('points_awarded', '<', 0);
    }

    /**
     * Scope untuk filter berdasarkan tanggal.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }
}