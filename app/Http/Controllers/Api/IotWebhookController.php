<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Events\IotDeviceStatusChanged;

class IotWebhookController extends Controller
{
    /**
     * Handle Webhooks from Reverb/Pusher.
     * Digunakan untuk mendeteksi kapan IoT Device join/leave presence channel.
     */
    public function handle(Request $request)
    {
        // 1. Verifikasi Webhook Signature (Opsional tapi disarankan)
        // Untuk Reverb, signature dikirim di header X-Reverb-Signature atau X-Pusher-Signature
        $signature = $request->header('X-Reverb-Signature') ?: $request->header('X-Pusher-Signature');
        
        // Log::info('[IotWebhook] received', $request->all());

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

                if ($name === 'member_added') {
                    $this->updateStatus($mac, 'online');
                } elseif ($name === 'member_removed') {
                    $this->updateStatus($mac, 'offline');
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }

    /**
     * Update status di cache dan broadcast ke UI.
     */
    private function updateStatus(string $mac, string $status)
    {
        Log::info("[IotWebhook] Device {$mac} is now {$status}");

        // Simpan ke Cache (sama seperti MqttListenerCommand)
        Cache::forever("iot_status_{$mac}", $status);

        // Broadcast perubahan status agar UI (Web & nantinya Mobile) terupdate
        broadcast(new IotDeviceStatusChanged($mac, $status));

        // Auto-push config if device connects and becomes online
        if ($status === 'online') {
            $device = \App\Models\IotDevice::where('device_mac_address', $mac)->first();
            if ($device && $device->subarea) {
                $subarea = $device->subarea;
                $payloadData = [
                    'action'             => 'update_config',
                    'max_slots'          => (int) $subarea->max_slots,
                    'detection_polygon'  => $subarea->detection_polygon ?? [],
                    'threshold_banyak'   => (float) ($subarea->threshold_banyak ?? 30.0),
                    'threshold_terbatas' => (float) ($subarea->threshold_terbatas ?? 80.0),
                    'timestamp'          => time(),
                ];

                try {
                    $key32 = substr(hash('sha256', env('IOT_API_SECRET'), true), 0, 32);
                    $payloadData['signature'] = hash_hmac('sha256', json_encode($payloadData, JSON_UNESCAPED_SLASHES), $key32);

                    // Broadcast via Reverb WS
                    broadcast(new \App\Events\IotCommandSent($mac, 'update_config', $payloadData, $payloadData['signature']));
                    Log::info("[IotWebhook] Auto-pushed config to device {$mac} on connection.");
                } catch (\Exception $e) {
                    Log::error("[IotWebhook] Failed to auto-push config: " . $e->getMessage());
                }
            }
        }
    }
}
