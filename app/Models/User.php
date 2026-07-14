<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

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
        'reset_token',
        'failed_attempts',
        'locked_until',
        'current_points',
        'lifetime_points',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'      => 'datetime',
            'locked_until'           => 'datetime',
            'otp_expires_at'         => 'datetime',
            'password'               => 'hashed',
        ];
    }

    /**
     * Relasi ke Info Board.
     */
    public function infoBoard(): HasMany
    {
        return $this->hasMany(InfoBoard::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User Mission.
     */
    public function userMission(): HasMany
    {
        return $this->hasMany(UserMission::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User Reward.
     */
    public function userReward(): HasMany
    {
        return $this->hasMany(UserReward::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User History.
     */
    public function userHistory(): HasMany
    {
        return $this->hasMany(UserHistory::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke User Validation.
     */
    public function userValidation(): HasMany
    {
        return $this->hasMany(UserValidation::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke Subarea Comment.
     */
    public function subareaComment(): HasMany
    {
        return $this->hasMany(SubareaComment::class, 'user_id', 'user_id');
    }
}
