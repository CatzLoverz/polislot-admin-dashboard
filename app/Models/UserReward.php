<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserReward extends Model
{
    use HasFactory;

    protected $table = 'user_rewards';
    protected $primaryKey = 'user_reward_id';

    protected $fillable = [
        'user_id',
        'reward_id',
        'voucher_code',
        'redeemed_status',
        'redeemed_at'
    ];

    protected $casts = [
        'redeemed_at' => 'datetime'
    ];

    /**
     * Relasi ke user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke reward
     */
    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id', 'reward_id');
    }
}