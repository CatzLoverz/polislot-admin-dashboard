<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use App\Events\IotDeviceStatusChanged;
use App\Events\SubareaStatusUpdated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IotWsAuthController extends Controller
{
    /**
     * Endpoint autentikasi WebSocket untuk perangkat IoT Python.
     * 
     * Python client (pysher) akan memanggil endpoint ini saat join presence channel.
     * Flow:
     * 1. Terima socket_id, channel_name, mac_address, timestamp, signature
     * 2. Verifikasi HMAC-SHA256 signature menggunakan IOT_API_SECRET
     * 3. Validasi MAC address terdaftar di database
     * 4. Return Pusher-compatible auth response
     */
    public function authenticate(Request $request)
    {
        $request->validate([
            'socket_id'    => 'required|string',
            'channel_name' => 'required|string',
            'mac_address'  => 'required|string',
            'timestamp'    => 'required|numeric',
            'signature'    => 'required|string',
        ]);

        $macAddress = $request->mac_address;
        $timestamp  = (int) $request->timestamp;
        $now        = time();

        // ============================================================
        // 1. VALIDASI HMAC SIGNATURE (keamanan setara MQTT)
        // ============================================================
        $iotSecret = config('services.iot.secret');

        if (!$iotSecret) {
            Log::error('IOT_API_SECRET not configured.');
            return response()->json(['error' => 'Server misconfigured.'], 500);
        }

        // Cek timestamp (±5 menit untuk mencegah replay attack)
        if (abs($now - $timestamp) > 300) {
            Log::warning('Rejected: Stale timestamp', [
                'mac'       => $macAddress,
                'timestamp' => $timestamp,
                'server'    => $now,
                'diff'      => abs($now - $timestamp),
            ]);

            return response()->json(['error' => 'Request expired.'], 401);
        }

        // Hitung HMAC: sign(mac_address:timestamp) dengan shared secret
        $dataToSign = "{$macAddress}:{$timestamp}";
        $key32 = substr(hash('sha256', $iotSecret, true), 0, 32);
        $expectedSignature = hash_hmac('sha256', $dataToSign, $key32);

        if (!hash_equals($expectedSignature, $request->signature)) {
            Log::warning('Rejected: Invalid HMAC signature', [
                'mac' => $macAddress,
                'ip'  => $request->ip(),
            ]);

            return response()->json(['error' => 'Invalid signature.'], 401);
        }

        // ============================================================
        // 2. VALIDASI MAC ADDRESS (cache-aware, sama seperti IotStreamController)
        // ============================================================
        $cacheKey = "iot_device_valid:{$macAddress}";
        $isRegistered = Cache::get($cacheKey);

        if ($isRegistered === null) {
            $isRegistered = IotDevice::where('device_mac_address', $macAddress)->exists();
            if ($isRegistered) {
                Cache::put($cacheKey, true, 60);
            }
        }

        if (!$isRegistered) {
            Log::warning('Rejected: Unregistered MAC Address', [
                'mac' => $macAddress,
                'ip'  => $request->ip(),
            ]);

            return response()->json(['error' => 'Device not registered.'], 403);
        }

        // ============================================================
        // 3. GENERATE PUSHER AUTH RESPONSE
        // ============================================================
        $channelName = $request->channel_name;
        $socketId    = $request->socket_id;

        // Data member untuk presence channel
        $channelData = json_encode([
            'user_id'   => $macAddress,
            'user_info' => [
                'type' => 'iot_device',
                'mac'  => $macAddress,
            ],
        ]);

        // Format Pusher auth: "socket_id:channel_name:channel_data"
        $stringToSign = "{$socketId}:{$channelName}:{$channelData}";
        $reverbSecret = config('broadcasting.connections.reverb.secret');
        $reverbKey    = config('broadcasting.connections.reverb.key');
        $authSignature = hash_hmac('sha256', $stringToSign, $reverbSecret);

        Log::info('Device authenticated successfully', [
            'mac'     => $macAddress,
            'channel' => $channelName,
        ]);

        // ============================================================
        // 4. UPDATE STATUS CACHE (Instant Online)
        // ============================================================
        Cache::forever("iot_status_{$macAddress}", 'online');
        Cache::forever("iot_connection_type_{$macAddress}", 'ws');
        broadcast(new IotDeviceStatusChanged($macAddress, 'online'));

        $device = IotDevice::where('device_mac_address', $macAddress)->first();
        if ($device && $device->subarea) {
            broadcast(new SubareaStatusUpdated($device->subarea));
        }

        return response()->json([
            'auth'         => "{$reverbKey}:{$authSignature}",
            'channel_data' => $channelData,
        ]);
    }
}
