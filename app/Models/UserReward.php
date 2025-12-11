<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReward extends Model
{
    protected $table = 'user_rewards';
    protected $primaryKey = 'user_reward_id';

    protected $fillable = [
        'user_id',
        'reward_id',
        'user_reward_code',
        'user_reward_status'
    ];

    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reward()
    {
        return $this->belongsTo(Reward::class, 'reward_id', 'reward_id');
    }
}