<?php

namespace App\Models;
use Exception;

use App\Events\IotDeviceStatusChanged;
use App\Events\IotCountUpdated;
use App\Events\SubareaStatusUpdated;
use App\Models\IotCapture;
use App\Models\ParkSubarea;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pusher\ApiErrorException;

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
     * Dapatkan status perangkat (READ-ONLY).
     * 
     * Method ini HANYA membaca status dari cache tanpa side-effects.
     * Aman dipanggil dari controller/views tanpa memicu broadcast events.
     * Untuk sinkronisasi aktif dengan Reverb/MQTT, gunakan syncStatus().
     */
    public static function getStatus(string $mac): string
    {
        return Cache::get("iot_status_{$mac}", 'offline');
    }

    /**
     * Sinkronisasi status perangkat dengan verifikasi aktual ke Reverb/MQTT.
     * 
     * Method ini melakukan side-effects: update cache, broadcast events, 
     * dan reset database jika device ternyata sudah offline.
     * HANYA panggil dari background processes (MqttListenerCommand, scheduled tasks).
     * 
     * @return string Status aktual setelah sinkronisasi ('online' atau 'offline')
     */
    public static function syncStatus(string $mac): string
    {
        $status = Cache::get("iot_status_{$mac}", 'offline');
        $connectionType = Cache::get("iot_connection_type_{$mac}", 'ws');

        if ($status !== 'online') {
            return $status;
        }

        $shouldGoOffline = false;

        if ($connectionType === 'ws') {
            $shouldGoOffline = static::checkReverbPresence($mac);
        } elseif ($connectionType === 'mqtt') {
            $lastSeen = Cache::get("iot_last_seen_{$mac}");
            if (!$lastSeen || (time() - $lastSeen) > 60) {
                Log::info("MQTT sync: Device {$mac} inactive for more than 60 seconds. Setting to offline.");
                $shouldGoOffline = true;
            }
        }

        if ($shouldGoOffline) {
            static::markDeviceOffline($mac);
            $status = 'offline';
        }

        return $status;
    }

    /**
     * Verifikasi keberadaan device di Reverb presence channel.
     * 
     * @return bool True jika device HARUS di-set offline (tidak ditemukan di channel)
     */
    private static function checkReverbPresence(string $mac): bool
    {
        try {
            $cleanMac = str_replace(':', '', $mac);
            $channelName = "presence-iot.device.{$cleanMac}";

            $pusher = Broadcast::connection('reverb')->getPusher();
            $response = $pusher->get("/channels/{$channelName}/users", [], true);

            if (is_array($response) && isset($response['users'])) {
                $users = $response['users'];
                foreach ($users as $user) {
                    if (isset($user['id']) && str_replace(':', '', strtolower($user['id'])) === strtolower($cleanMac)) {
                        return false; // Device ditemukan, JANGAN set offline
                    }
                }
                Log::info("Reverb sync: Device {$mac} not found in presence channel. Setting to offline.");
                return true; // Device TIDAK ditemukan
            }
        } catch (ApiErrorException $e) {
            if ($e->getCode() === 404) {
                Log::info("Reverb sync: Channel not found (404) for device {$mac}. Setting to offline.");
                return true; // Channel tidak ada = device offline
            }
            Log::warning("Reverb sync failed (API error) for device {$mac}", [
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        } catch (Exception $e) {
            Log::warning("Reverb sync failed for device {$mac}", [
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
        }

        return false; // Jika Reverb error, fallback ke status cache (jangan force offline)
    }

    /**
     * Tandai device sebagai offline: update cache, broadcast events, reset database.
     * Semua side-effects terisolasi di method ini.
     */
    private static function markDeviceOffline(string $mac): void
    {
        // Update cache
        Cache::forever("iot_status_{$mac}", 'offline');

        // Broadcast status offline
        broadcast(new IotDeviceStatusChanged($mac, 'offline'));

        // Reset database subarea count to 0
        $device = static::where('device_mac_address', $mac)->first();
        if ($device && $device->subarea) {
            $subarea = $device->subarea;
            $subarea->current_count = 0;
            $subarea->save();

            broadcast(new IotCountUpdated($mac, 0));
            broadcast(new SubareaStatusUpdated($subarea));
        }
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
