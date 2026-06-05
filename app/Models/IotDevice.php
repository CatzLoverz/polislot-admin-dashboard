<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IotDevice extends Model
{
    use HasFactory;

    /**
     * Boot the model.
     * Menghapus cache validasi MAC address saat device dihapus dari database,
     * agar stream langsung ditolak tanpa menunggu cache expire.
     */
    protected static function booted(): void
    {
        // Saat device dihapus → invalidasi cache MAC address
        static::deleted(function (IotDevice $device) {
            $cacheKey = "iot_device_valid:{$device->device_mac_address}";
            Cache::forget($cacheKey);
            Log::info('Cache invalidated on delete', [
                'mac' => $device->device_mac_address,
                'cache_key' => $cacheKey,
            ]);
        });

        // Saat MAC address diubah → invalidasi cache MAC lama
        static::updating(function (IotDevice $device) {
            if ($device->isDirty('device_mac_address')) {
                $oldMac = $device->getOriginal('device_mac_address');
                $cacheKey = "iot_device_valid:{$oldMac}";
                Cache::forget($cacheKey);
                Log::info('Cache invalidated on MAC change', [
                    'old_mac' => $oldMac,
                    'new_mac' => $device->device_mac_address,
                ]);
            }
        });
    }

    protected $table = 'iot_devices';
    protected $primaryKey = 'device_id';

    protected $fillable = [
        'park_subarea_id',
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
