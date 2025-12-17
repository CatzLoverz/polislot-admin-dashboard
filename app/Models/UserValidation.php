<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserValidation extends Model
{
    protected $table = 'user_validations';
    protected $primaryKey = 'user_validation_id';
    
    protected $fillable = [
        'user_id',
        'validation_id',
        'park_subarea_id',
        'user_validation_content' // 'banyak', 'terbatas', 'penuh'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function parkSubarea()
    {
        return $this->belongsTo(ParkSubarea::class, 'park_subarea_id', 'park_subarea_id');
    }
}