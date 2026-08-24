<?php

namespace App\Models;

use App\Events\IotCountUpdated;
use App\Events\IotDeviceStatusChanged;
use App\Events\SubareaStatusUpdated;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Pusher\ApiErrorException;

class IotDevice extends Model
{
    use HasFactory;

    /**
     * Helper: Normalisasi MAC Address ke format clean lowercase tanpa titik dua (misal: "001a2b3c4d5e").
     */
    public static function normalizeMac(string $mac): string
    {
        return str_replace(':', '', strtolower(trim($mac)));
    }

    /**
     * Helper: Format MAC Address ke format standar uppercase dengan titik dua (misal: "00:1A:2B:3C:4D:5E").
     */
    public static function formatMac(string $mac): string
    {
        $clean = static::normalizeMac($mac);
        if (strlen($clean) !== 12) {
            return strtoupper(trim($mac));
        }

        return strtoupper(implode(':', str_split($clean, 2)));
    }

    /**
     * Boot the model.
     * Menghapus cache validasi MAC address saat device dihapus dari database,
     * agar stream langsung ditolak tanpa menunggu cache expire.
     */
    protected static function booted(): void
    {
        // Saat device dihapus → invalidasi cache MAC address
        static::deleted(function (IotDevice $device) {
            $cleanMac = static::normalizeMac($device->device_mac_address);
            Cache::forget("iot_device_valid:{$cleanMac}");
            Cache::forget("iot_device_valid:{$device->device_mac_address}");
            Log::info('Cache invalidated on delete', [
                'mac' => $device->device_mac_address,
                'clean_mac' => $cleanMac,
            ]);
        });

        // Saat MAC address diubah → invalidasi cache MAC lama
        static::updating(function (IotDevice $device) {
            if ($device->isDirty('device_mac_address')) {
                $oldMac = $device->getOriginal('device_mac_address');
                $oldCleanMac = static::normalizeMac($oldMac);
                Cache::forget("iot_device_valid:{$oldCleanMac}");
                Cache::forget("iot_device_valid:{$oldMac}");
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
        $cleanMac = static::normalizeMac($mac);

        return Cache::get("iot_status_{$cleanMac}", Cache::get("iot_status_{$mac}", 'offline'));
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
        $cleanMac = static::normalizeMac($mac);
        $status = static::getStatus($mac);
        $connectionType = Cache::get("iot_connection_type_{$cleanMac}", Cache::get("iot_connection_type_{$mac}", 'ws'));

        if ($status !== 'online') {
            return $status;
        }

        $shouldGoOffline = false;

        if ($connectionType === 'ws') {
            $shouldGoOffline = static::checkReverbPresence($mac);
        } elseif ($connectionType === 'mqtt') {
            $lastSeen = Cache::get("iot_last_seen_{$cleanMac}", Cache::get("iot_last_seen_{$mac}"));
            if (! $lastSeen || (time() - $lastSeen) > 60) {
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
            $cleanMac = static::normalizeMac($mac);
            $channelName = "presence-iot.device.{$cleanMac}";

            $pusher = Broadcast::connection('reverb')->getPusher();
            $response = $pusher->get("/channels/{$channelName}/users", [], true);

            if (is_array($response) && isset($response['users'])) {
                $users = $response['users'];
                foreach ($users as $user) {
                    if (isset($user['id']) && static::normalizeMac((string) $user['id']) === $cleanMac) {
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
    public static function markDeviceOffline(string $mac): void
    {
        $cleanMac = static::normalizeMac($mac);
        $formattedMac = static::formatMac($mac);

        // Update cache (simpan di kedua key untuk kompatibilitas)
        Cache::forever("iot_status_{$cleanMac}", 'offline');
        Cache::forever("iot_status_{$mac}", 'offline');
        Cache::forever("iot_status_{$formattedMac}", 'offline');

        // Broadcast status offline (menggunakan cleanMac agar event listener Reverb tepat)
        broadcast(new IotDeviceStatusChanged($cleanMac, 'offline'));
        if ($formattedMac !== $cleanMac) {
            broadcast(new IotDeviceStatusChanged($formattedMac, 'offline'));
        }

        // Reset database subarea count to 0
        $device = static::where('device_mac_address', $formattedMac)
            ->orWhere('device_mac_address', $mac)
            ->orWhere('device_mac_address', $cleanMac)
            ->first();

        if ($device && $device->subarea) {
            $subarea = $device->subarea;
            $subarea->current_count = 0;
            $subarea->save();

            broadcast(new IotCountUpdated($cleanMac, 0));
            broadcast(new SubareaStatusUpdated($subarea));
        }
    }

    protected $table = 'iot_devices';

    protected $primaryKey = 'device_id';

    protected $fillable = [
        'park_subarea_id',
        'device_mac_address',
    ];

    /**
     * Relasi ke ParkSubarea tempat device ini terpasang.
     */
    public function subarea(): BelongsTo
    {
        return $this->belongsTo(ParkSubarea::class, 'park_subarea_id', 'park_subarea_id');
    }

    /**
     * Relasi ke IotCapture (history jepretan kamera device ini).
     */
    public function captures(): HasMany
    {
        return $this->hasMany(IotCapture::class, 'device_id', 'device_id');
    }
}
