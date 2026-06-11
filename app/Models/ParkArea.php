<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Relasi ke subarea parkir yang ada di area ini.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function parkSubarea(): HasMany
    {
        return $this->hasMany(ParkSubarea::class, 'park_area_id', 'park_area_id');
    }
}