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

    /**
     * Dapatkan status perangkat dengan sinkronisasi ke Reverb WebSocket Server.
     * Karena Reverb tidak mendukung outbound webhooks secara bawaan, metode ini
     * melakukan query langsung ke HTTP API Reverb untuk memverifikasi apakah perangkat
     * terhubung ke presence channel.
     */
    public static function getStatus(string $mac): string
    {
        $status = Cache::get("iot_status_{$mac}", 'offline');

        if ($status === 'online') {
            try {
                $cleanMac = str_replace(':', '', strtolower($mac));
                $channelName = "presence-iot.device.{$cleanMac}";

                // Ambil instance Pusher dari Reverb Broadcaster
                $pusher = \Illuminate\Support\Facades\Broadcast::connection('reverb')->getPusher();
                $response = $pusher->get("/channels/{$channelName}/users");

                if ($response && $response['status'] == 200 && isset($response['result']['users'])) {
                    $users = $response['result']['users'];
                    $isDevicePresent = false;
                    
                    foreach ($users as $user) {
                        if (isset($user['id']) && str_replace(':', '', strtolower($user['id'])) === $cleanMac) {
                            $isDevicePresent = true;
                            break;
                        }
                    }

                    if (!$isDevicePresent) {
                        Log::info("Reverb sync: Device {$mac} not found in presence channel. Setting to offline.");
                        
                        // Update cache
                        Cache::forever("iot_status_{$mac}", 'offline');
                        $status = 'offline';

                        // Broadcast status offline
                        broadcast(new \App\Events\IotDeviceStatusChanged($mac, 'offline'));

                        // Reset database subarea count to 0
                        $device = static::where('device_mac_address', $mac)->first();
                        if ($device && $device->subarea) {
                            $subarea = $device->subarea;
                            $subarea->current_count = 0;
                            $subarea->save();

                            // Broadcast count updated to 0
                            broadcast(new \App\Events\IotCountUpdated($mac, 0));
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log warning dan gunakan status dari cache sebagai fallback jika Reverb bermasalah/offline
                Log::warning("Reverb sync failed for device {$mac}: " . $e->getMessage());
            }
        }

        return $status;
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
