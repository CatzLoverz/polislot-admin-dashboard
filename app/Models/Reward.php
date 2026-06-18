<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reward extends Model
{
    protected $table = 'rewards';

    protected $primaryKey = 'reward_id';

    protected $fillable = [
        'reward_type',
        'reward_name',
        'reward_point_required',
        'reward_image',
    ];

    protected $casts = [
        'reward_point_required' => 'integer',
    ];

    // Helper untuk Konstanta (Dropdown Filter)
    const TYPES = ['Voucher', 'Barang'];

    /**
     * Relasi ke reward yang ditukarkan oleh pengguna (UserReward).
     */
    public function userRewards(): HasMany
    {
        return $this->hasMany(UserReward::class, 'reward_id', 'reward_id');
    }
}
