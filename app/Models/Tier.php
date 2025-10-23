<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tier extends Model
{
    use HasFactory;

    protected $table = 'tiers';
    protected $primaryKey = 'tier_id';

    protected $fillable = [
        'tier_name',
        'min_points',
        'color_theme',
        'icon',
    ];

    // Relasi ke user_tiers
    public function userTiers()
    {
        return $this->hasMany(UserTier::class, 'tier_id');
    }
}
