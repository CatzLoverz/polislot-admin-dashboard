<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotDevice extends Model
{
    use HasFactory;

    protected $table = 'iot_devices';
    protected $primaryKey = 'device_id';

    protected $fillable = [
        'park_subarea_id',
        'device_url',
        'device_mac_address',
    ];

    public function subarea()
    {
        return $this->belongsTo(ParkSubarea::class, 'park_subarea_id', 'park_subarea_id');
    }

    public function captures()
    {
        return $this->hasMany(IotCapture::class, 'device_id', 'device_id');
    }
}
