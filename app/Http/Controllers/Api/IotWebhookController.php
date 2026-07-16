<?php

namespace App\Http\Controllers\Api;

use App\Events\IotCommandSent;
use App\Events\IotCountUpdated;
use App\Events\IotDeviceStatusChanged;
use App\Events\SubareaStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class IotWebhookController extends Controller
{
    /**
     * Handle Webhooks from Reverb/Pusher.
     * Digunakan untuk mendeteksi kapan IoT Device join/leave presence channel.
     *
     * @param  Request $request
     * @return JsonResponse
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Verifikasi Webhook Signature
        // Untuk Reverb/Pusher, signature dikirim di header X-Reverb-Signature atau X-Pusher-Signature
        $secret = config('broadcasting.connections.reverb.secret');
        $signature = $request->header('X-Reverb-Signature') ?: $request->header('X-Pusher-Signature');

        if ($secret && $signature) {
            $expectedSignature = hash_hmac('sha256', $request->getContent(), $secret);
            if (! hash_equals($expectedSignature, $signature)) {
                Log::warning('Rejected: Invalid Webhook Signature');

                return response()->json(['error' => 'Invalid signature.'], 401);
            }
        } elseif ($secret && ! $signature) {
            Log::warning('Rejected: Missing Webhook Signature');

            return response()->json(['error' => 'Missing signature.'], 401);
        }

        // Log::info('received', $request->all());

        $events = $request->input('events', []);

        foreach ($events as $event) {
            $name = $event['name'] ?? '';
            $channel = $event['channel'] ?? '';
            $userId = $event['user_id'] ?? '';

            // Format channel: presence-iot.device.{macNoColons}
            if (strpos($channel, 'presence-iot.device.') === 0) {
                // Ekstrak MAC dari nama channel
                $macNoColons = str_replace('presence-iot.device.', '', $channel);

                // Kembalikan format MAC dengan titik dua (agar konsisten dengan MQTT)
                // Contoh: 001A2B3C4D5E -> 00:1A:2B:3C:4D:5E
                $mac = implode(':', str_split($macNoColons, 2));

                // Hanya update status jika member yang join/leave adalah device IoT itu sendiri
                // (Mencegah admin browser session meng-override status device)
                $cleanUserId = str_replace(':', '', strtolower($userId));
                $cleanMac = strtolower($macNoColons);

                if ($cleanUserId === $cleanMac) {
                    if ($name === 'member_added') {
                        $this->updateStatus($mac, 'online');
                    } elseif ($name === 'member_removed') {
                        $this->updateStatus($mac, 'offline');
                    }
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Update status di cache dan broadcast ke UI.
     *
     * @param  string $mac MAC address perangkat
     * @param  string $status Status baru ('online'/'offline')
     * @return void
     */
    private function updateStatus(string $mac, string $status)
    {
        Log::info("Device {$mac} is now {$status}");

        // Simpan ke Cache (sama seperti MqttListenerCommand)
        Cache::forever("iot_status_{$mac}", $status);
        if ($status === 'online') {
            Cache::forever("iot_connection_type_{$mac}", 'ws');
        }

        // Broadcast perubahan status agar UI (Web & nantinya Mobile) terupdate
        broadcast(new IotDeviceStatusChanged($mac, $status));

        // Reset count to 0 in database if device goes offline
        if ($status === 'offline') {
            try {
                DB::transaction(function () use ($mac) {
                    $device = IotDevice::where('device_mac_address', $mac)->lockForUpdate()->first();
                    if ($device && $device->subarea) {
                        $subarea = $device->subarea;
                        $subarea->current_count = 0;
                        $subarea->save();

                        // Broadcast count updated to 0
                        broadcast(new IotCountUpdated($mac, 0));

                        DB::afterCommit(function () use ($subarea) {
                            // Broadcast subarea status updated
                            broadcast(new SubareaStatusUpdated($subarea));
                        });

                        Log::info("Device {$mac} went offline. Reset subarea count to 0.");
                    }
                });
            } catch (Exception $e) {
                Log::error('Gagal reset count saat device offline: '.$e->getMessage());
            }
        }

        // Auto-push config if device connects and becomes online
        if ($status === 'online') {
            $device = IotDevice::where('device_mac_address', $mac)->first();
            if ($device && $device->subarea) {
                $subarea = $device->subarea;

                // Broadcast subarea status updated
                broadcast(new SubareaStatusUpdated($subarea));

                $payloadData = [
                    'action' => 'update_config',
                    'max_slots' => (int) $subarea->max_slots,
                    'detection_polygon' => $subarea->detection_polygon ?? [],
                    'threshold_banyak' => (float) ($subarea->threshold_banyak ?? 30.0),
                    'threshold_terbatas' => (float) ($subarea->threshold_terbatas ?? 80.0),
                    'timestamp' => time(),
                ];

                try {
                    $key32 = substr(hash('sha256', config('services.iot.secret'), true), 0, 32);
                    $payloadData['signature'] = hash_hmac('sha256', json_encode($payloadData, JSON_UNESCAPED_SLASHES), $key32);

                    // Broadcast via Reverb WS
                    broadcast(new IotCommandSent($mac, 'update_config', $payloadData, $payloadData['signature']));
                    Log::info("Auto-pushed config to device {$mac} on connection.");
                } catch (Exception $e) {
                    Log::error('Failed to auto-push config: '.$e->getMessage());
                }
            }
        }
    }
}
