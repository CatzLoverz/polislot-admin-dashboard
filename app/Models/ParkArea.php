<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkArea extends Model
{
    protected $table = 'park_areas';
    protected $primaryKey = 'park_area_id';
    
    protected $fillable = [
        'park_area_name',
        'park_area_code',
        'park_area_data',
    ];

    protected $casts = [
        'park_area_data' => 'array',
    ];

    public function parkSubarea()
    {
        return $this->hasMany(ParkSubarea::class, 'park_area_id', 'park_area_id');
    }
}