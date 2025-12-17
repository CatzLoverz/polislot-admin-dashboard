<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkSubarea extends Model
{
    protected $table = 'park_subareas';
    protected $primaryKey = 'park_subarea_id';
    
    protected $fillable = [
        'park_area_id',
        'park_subarea_name',
        'park_subarea_polygon',
    ];

    protected $casts = [
        'park_subarea_polygon' => 'array',
    ];

    public function parkArea()
    {
        return $this->belongsTo(ParkArea::class, 'park_area_id', 'park_area_id');
    }

    public function parkAmenity()
    {
        return $this->hasMany(ParkAmenity::class, 'park_subarea_id', 'park_subarea_id');
    }

    public function userValidation()
    {
        return $this->hasMany(UserValidation::class, 'park_subarea_id', 'park_subarea_id');
    }

    public function subareaComment()
    {
        return $this->hasMany(SubareaComment::class, 'park_subarea_id', 'park_subarea_id');
    }
}