<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    /**
     * Relasi ke pengguna yang menukarkan reward.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Relasi ke reward yang ditukarkan.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class, 'reward_id', 'reward_id');
    }
}