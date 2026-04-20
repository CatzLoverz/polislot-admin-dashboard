<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IotCapture extends Model
{
    use HasFactory;

    protected $table = 'iot_captures';
    protected $primaryKey = 'capture_id';

    protected $fillable = [
        'device_id',
        'capture_image_path',
        'capture_is_trained',
        'capture_ai_status',
    ];

    protected $casts = [
        'capture_is_trained' => 'boolean',
    ];

    public function device()
    {
        return $this->belongsTo(IotDevice::class, 'device_id', 'device_id');
    }
}
