<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reward extends Model
{
    use HasFactory;

    protected $table = 'rewards';
    protected $primaryKey = 'reward_id';

    protected $fillable = [
        'reward_name',
        'description',
        'points_required',
        'reward_type',
        'reward_image'
    ];

    /**
     * Relasi ke user_rewards
     */
    public function userRewards()
    {
        return $this->hasMany(UserReward::class, 'reward_id', 'reward_id');
    }
}