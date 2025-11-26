<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserTier extends Model
{
    use HasFactory;

    protected $table = 'user_tiers';
    protected $primaryKey = 'user_id'; 

    public $incrementing = false; // Karena PK bukan auto-increment
    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'tier_id',
        'lifetime_points',
        'current_points',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Relasi ke Tier
    public function tier()
    {
        return $this->belongsTo(Tier::class, 'tier_id', 'tier_id');
    }
}
