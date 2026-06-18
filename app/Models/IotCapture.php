<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IotCapture extends Model
{
    use HasFactory;

    protected $table = 'iot_captures';

    protected $primaryKey = 'capture_id';

    protected $fillable = [
        'user_validation_id',
        'device_id',
        'capture_image_path',
        'capture_is_trained',
        'capture_ai_status',
    ];

    protected $casts = [
        'capture_is_trained' => 'boolean',
    ];

    /**
     * Relasi ke perangkat IOT (IotDevice).
     */
    public function device(): BelongsTo
    {
        return $this->belongsTo(IotDevice::class, 'device_id', 'device_id');
    }

    /**
     * Relasi ke validasi pengguna (UserValidation).
     */
    public function userValidation(): BelongsTo
    {
        return $this->belongsTo(UserValidation::class, 'user_validation_id', 'user_validation_id');
    }
}
