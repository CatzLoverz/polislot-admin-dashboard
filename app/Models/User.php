<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = true;

    protected $fillable = [
        'email',
        'password',
        'role',
        'name',
        'avatar',
        'otp_code',        
        'otp_expires_at',
        'failed_attempts',
        'locked_until',
        'current_points',
        'lifetime_points'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'locked_until'      => 'datetime',
            'password' => 'hashed',
        ];
    }

    
    /**
     * Relasi ke Info Board.
     *
     * @return HasMany
     */
    public function infoBoard(): HasMany
    {
        return $this->hasMany(InfoBoard::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User Mission.
     *
     * @return HasMany
     */
    public function userMission(): HasMany
    {
        return $this->hasMany(UserMission::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User Reward.
     *
     * @return HasMany
     */
    public function userReward(): HasMany
    {
        return $this->hasMany(UserReward::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User History.
     *
     * @return HasMany
     */
    public function userHistory(): HasMany
    {
        return $this->hasMany(UserHistory::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User Validation.
     *
     * @return HasMany
     */
    public function userValidation(): HasMany
    {
        return $this->hasMany(UserValidation::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke Subarea Comment.
     *
     * @return HasMany
     */
    public function subareaComment(): HasMany
    {
        return $this->hasMany(SubareaComment::class, 'user_id', 'user_id');
    }
}