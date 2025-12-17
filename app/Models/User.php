<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Storage;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

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

    
    public function infoBoard()
    {
        return $this->hasMany(InfoBoard::class, 'user_id', 'user_id');
    }

    public function userMission()
    {
        return $this->hasMany(UserMission::class, 'user_id', 'user_id');
    }

    public function userReward()
    {
        return $this->hasMany(UserReward::class, 'user_id', 'user_id');
    }

    public function userHistory()
    {
        return $this->hasMany(UserHistory::class, 'user_id', 'user_id');
    }

    public function userValidation()
    {
        return $this->hasMany(UserValidation::class, 'user_id', 'user_id');
    }

    public function subareaComment()
    {
        return $this->hasMany(SubareaComment::class, 'user_id', 'user_id');
    }
}