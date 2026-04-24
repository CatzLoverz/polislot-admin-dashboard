<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use Illuminate\Http\Request;
use App\Events\IotStreamReceived;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IotStreamController extends Controller
{
    /**
     * Endpoint untuk menerima frame dari perangkat IoT Python
     * dan mem-broadcast-nya via Reverb WebSockets.
     * 
     * Security:
     * 1. MAC Address divalidasi terhadap database (harus terdaftar)
     * 2. Request di-sign dengan HMAC-SHA256 menggunakan API secret
     * 3. Timestamp dalam signature mencegah replay attack (±5 menit)
     */
    public function receiveStream(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'frame'       => 'required|string',
            'timestamp'   => 'required|numeric',
            'signature'   => 'required|string',
        ]);

        $macAddress = $request->mac_address;

        // ============================================================
        // 1. VALIDASI MAC ADDRESS (dengan cache 5 menit untuk performa)
        // ============================================================
        $cacheKey = "iot_device_valid:{$macAddress}";
        $isRegistered = Cache::remember($cacheKey, 300, function () use ($macAddress) {
            return IotDevice::where('device_mac_address', $macAddress)->exists();
        });

        if (!$isRegistered) {
            Log::warning('[API IotStreamController] Rejected: Unregistered MAC Address', [
                'mac' => $macAddress,
                'ip'  => $request->ip(),
            ]);

            // Response 403 — bukan 404, agar device tahu ditolak
            return response()->json([
                'status'  => 'error',
                'message' => 'Device not registered.',
            ], 403);
        }

        // ============================================================
        // 2. VALIDASI HMAC SIGNATURE (mencegah spoofing & replay attack)
        // ============================================================
        $iotSecret = config('services.iot.secret');

        if ($iotSecret) {
            $timestamp = (int) $request->timestamp;
            $now = time();

            // Cek timestamp tidak lebih dari 5 menit lalu atau masa depan
            if (abs($now - $timestamp) > 300) {
                Log::warning('[API IotStreamController] Rejected: Stale timestamp', [
                    'mac'       => $macAddress,
                    'timestamp' => $timestamp,
                    'server'    => $now,
                    'diff'      => abs($now - $timestamp),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Request expired.',
                ], 401);
            }

            // Hitung HMAC: sign(mac_address + timestamp + frame_length) dengan shared secret
            // Tidak menyertakan frame penuh di signature karena terlalu besar
            $frameLength = strlen($request->frame);
            $dataToSign = "{$macAddress}:{$timestamp}:{$frameLength}";
            $expectedSignature = hash_hmac('sha256', $dataToSign, $iotSecret);

            if (!hash_equals($expectedSignature, $request->signature)) {
                Log::warning('[API IotStreamController] Rejected: Invalid HMAC signature', [
                    'mac' => $macAddress,
                    'ip'  => $request->ip(),
                ]);

                return response()->json([
                    'status'  => 'error',
                    'message' => 'Invalid signature.',
                ], 401);
            }
        }

        // ============================================================
        // 3. BROADCAST FRAME
        // ============================================================
        try {
            broadcast(new IotStreamReceived($macAddress, $request->frame));
            
            return response()->json([
                'status'  => 'success',
                'message' => 'Frame broadcasted successfully.',
            ], 200);
        } catch (\Exception $e) {
            Log::error('[API IotStreamController] Error broadcasting stream: ' . $e->getMessage());

            return response()->json([
                'status'  => 'error',
                'message' => 'Failed to broadcast frame.',
            ], 500);
        }
    }
}
