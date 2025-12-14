<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParkAmenity extends Model
{
    protected $table = 'park_amenities';
    protected $primaryKey = 'park_amenity_id';
    
    protected $fillable = [
        'park_subarea_id',
        'park_amenity_name',
    ];

    public function parkSubarea()
    {
        return $this->belongsTo(ParkSubarea::class, 'park_subarea_id', 'park_subarea_id');
    }
}