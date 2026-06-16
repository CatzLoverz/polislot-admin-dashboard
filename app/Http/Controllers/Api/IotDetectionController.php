<?php

namespace App\Http\Controllers\Api;

use App\Events\IotCountUpdated;
use App\Events\IotDetectionReceived;
use App\Events\IotDeviceStatusChanged;
use App\Events\SubareaStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\IotCapture;
use App\Models\IotDevice;
use App\Models\UserValidation;
use App\Models\Validation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class IotDetectionController extends Controller
{
    /**
     * Helper: Validasi MAC Address terdaftar (cache-aware).
     */
    private function validateMacAddress(string $macAddress): bool
    {
        $cacheKey = "iot_device_valid:{$macAddress}";
        $isRegistered = Cache::get($cacheKey);

        if ($isRegistered === null) {
            $isRegistered = IotDevice::where('device_mac_address', $macAddress)->exists();
            if ($isRegistered) {
                Cache::put($cacheKey, true, 60);
            }
        }

        return (bool) $isRegistered;
    }

    /**
     * Endpoint untuk menerima frame dari perangkat IoT Python
     * dan mem-broadcast-nya via Reverb WebSockets.
     *
     * Security:
     * 1. MAC Address divalidasi terhadap database (harus terdaftar)
     * 2. Request di-sign dengan HMAC-SHA256 menggunakan API secret
     * 3. Timestamp dalam signature mencegah replay attack (±5 menit)
     */
    public function receiveDetection(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'frame' => 'required|string',
            'timestamp' => 'required|numeric',
            'signature' => 'required|string',
        ]);

        $macAddress = $request->mac_address;

        // ============================================================
        // 1. VALIDASI MAC ADDRESS (cache 60 detik, invalidasi via Model Event)
        // ============================================================
        // Cache TTL 60 detik sebagai defense-in-depth.
        // Invalidasi utama terjadi via IotDevice model event (deleted/updating).
        // Hanya cache hasil 'true' — jika device belum terdaftar, selalu cek DB.
        $cacheKey = "iot_device_valid:{$macAddress}";
        $isRegistered = Cache::get($cacheKey);

        if ($isRegistered === null) {
            // Cache miss → cek ke database
            $isRegistered = IotDevice::where('device_mac_address', $macAddress)->exists();

            if ($isRegistered) {
                // Hanya cache jika terdaftar (true) — TTL 60 detik
                Cache::put($cacheKey, true, 60);
            }
        }

        if (! $isRegistered) {
            Log::warning('Rejected: Unregistered MAC Address', [
                'mac' => $macAddress,
                'ip' => $request->ip(),
            ]);

            // Response 403 — agar device tahu ditolak dan bisa stop/retry
            return response()->json([
                'status' => 'error',
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
                Log::warning('Rejected: Stale timestamp', [
                    'mac' => $macAddress,
                    'timestamp' => $timestamp,
                    'server' => $now,
                    'diff' => abs($now - $timestamp),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Request expired.',
                ], 401);
            }

            // Hitung HMAC: sign(mac_address + timestamp + frame_length) dengan shared secret
            // Tidak menyertakan frame penuh di signature karena terlalu besar
            $frameLength = strlen($request->frame);
            $dataToSign = "{$macAddress}:{$timestamp}:{$frameLength}";
            $expectedSignature = hash_hmac('sha256', $dataToSign, $iotSecret);

            if (! hash_equals($expectedSignature, $request->signature)) {
                Log::warning('Rejected: Invalid HMAC signature', [
                    'mac' => $macAddress,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid signature.',
                ], 401);
            }
        }

        // ============================================================
        // 3. BROADCAST FRAME
        // ============================================================
        try {
            broadcast(new IotDetectionReceived($macAddress, $request->frame, false));

            return response()->json([
                'status' => 'success',
                'message' => 'Frame broadcasted successfully.',
            ], 200);
        } catch (Exception $e) {
            Log::error('Error broadcasting stream: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to broadcast frame.',
            ], 500);
        }
    }

    /**
     * Endpoint untuk menerima snapshot terenkripsi dari IoT device.
     * Padanan HTTP dari MQTT topic "polislot/device/{MAC}/snapshot".
     *
     * Flow: Device capture → AES encrypt → HTTP POST → Decrypt → Save DB → Broadcast Reverb
     */
    public function receiveSnapshot(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'timestamp' => 'required|numeric',
            'encrypted_image' => 'required|string',
            'iv' => 'required|string',
            'signature' => 'required|string',
            'save_image' => 'nullable',
            'current_count' => 'nullable|numeric',
        ]);

        $macAddress = $request->mac_address;

        // 1. VALIDASI MAC ADDRESS
        if (! $this->validateMacAddress($macAddress)) {
            Log::warning('Rejected: Unregistered MAC', ['mac' => $macAddress]);

            return response()->json(['status' => 'error', 'message' => 'Device not registered.'], 403);
        }

        // 2. VALIDASI HMAC SIGNATURE
        $iotSecret = config('services.iot.secret');
        $key32 = substr(hash('sha256', $iotSecret, true), 0, 32);

        $payloadToSign = [
            'mac_address' => $macAddress,
            'timestamp' => (int) $request->timestamp,
            'encrypted_image' => $request->encrypted_image,
            'iv' => $request->iv,
        ];

        if ($request->has('current_count')) {
            $payloadToSign['current_count'] = (int) $request->current_count;
        }
        if ($request->has('save_image')) {
            $payloadToSign['save_image'] = filter_var($request->save_image, FILTER_VALIDATE_BOOLEAN);
        }

        $dataToSign = json_encode($payloadToSign, JSON_UNESCAPED_SLASHES);
        $calculatedSignature = hash_hmac('sha256', $dataToSign, $key32);

        if (! hash_equals($calculatedSignature, $request->signature)) {
            Log::warning('Rejected: Invalid HMAC signature', ['mac' => $macAddress]);

            return response()->json(['status' => 'error', 'message' => 'Invalid signature.'], 401);
        }

        // Auto-promote: Jika device mengirim data snapshot yang valid,
        // artinya device hidup — set ke online jika belum
        $status = Cache::get("iot_status_{$macAddress}", 'offline');
        if ($status !== 'online') {
            Log::info("🔄 Auto-promote (WS): Device {$macAddress} mengirim snapshot tapi status={$status}. Mempromosikan ke ONLINE.");
            Cache::forever("iot_status_{$macAddress}", 'online');
            Cache::forever("iot_connection_type_{$macAddress}", 'ws');
            broadcast(new IotDeviceStatusChanged($macAddress, 'online'));
        }

        // 3. DEKRIPSI AES-256-CBC
        $iv = base64_decode($request->iv);
        $encryptedImage = base64_decode($request->encrypted_image);
        $decryptedImageBytes = openssl_decrypt($encryptedImage, 'aes-256-cbc', $key32, OPENSSL_RAW_DATA, $iv);

        if ($decryptedImageBytes === false) {
            Log::error('Failed to decrypt image', ['mac' => $macAddress]);

            return response()->json(['status' => 'error', 'message' => 'Decryption failed.'], 400);
        }

        // 4. SIMPAN KE STORAGE + DATABASE (kondisional berdasarkan parameter save_image)
        $device = IotDevice::where('device_mac_address', $macAddress)->first();

        $saveImage = true;
        if ($request->has('save_image')) {
            $saveImage = filter_var($request->save_image, FILTER_VALIDATE_BOOLEAN);
        }

        if ($device && $saveImage) {
            $fileName = 'capture_'.time().'_'.str_replace(':', '', $macAddress).'.jpg';
            $path = 'iot_captures/'.$fileName;

            Storage::disk('public')->put($path, $decryptedImageBytes);

            $subarea = $device->subarea;
            $cvStatus = null;
            if ($subarea && $subarea->max_slots > 0) {
                $count = $request->has('current_count') ? (int) $request->current_count : ($subarea->current_count ?? 0);
                $occupancy = ($count / $subarea->max_slots) * 100;

                if ($occupancy < ($subarea->threshold_banyak ?? 30.0)) {
                    $cvStatus = 'banyak';
                } elseif ($occupancy >= ($subarea->threshold_terbatas ?? 80.0)) {
                    $cvStatus = 'penuh';
                } else {
                    $cvStatus = 'terbatas';
                }
            }

            $capture = IotCapture::create([
                'device_id' => $device->device_id,
                'capture_image_path' => $path,
                'capture_is_trained' => false,
                'capture_ai_status' => $cvStatus,
            ]);

            Log::info('Image saved', ['mac' => $macAddress, 'path' => $path, 'cv_status' => $cvStatus]);

            // Save the current count if present in payload
            if ($request->has('current_count') && $subarea) {
                $subarea->current_count = (int) $request->current_count;
                $subarea->save();
                broadcast(new IotCountUpdated($macAddress, $request->current_count));
                DB::afterCommit(function () use ($subarea) {
                    broadcast(new SubareaStatusUpdated($subarea));
                });
            }

            // Check if there is a pending validation for this device
            $cleanMac = str_replace(':', '', $macAddress);
            $pendingKey = "pending_validation_{$cleanMac}";
            if (Cache::has($pendingKey)) {
                $pending = Cache::get($pendingKey);
                Cache::forget($pendingKey);

                $subarea = $device->subarea;
                if ($subarea) {
                    // Create UserValidation record
                    $userVal = UserValidation::create([
                        'user_id' => $pending['user_id'],
                        'validation_id' => Validation::first()->validation_id ?? 1,
                        'park_subarea_id' => $subarea->park_subarea_id,
                        'user_validation_content' => $pending['content'],
                    ]);

                    // Associate with capture
                    $capture->user_validation_id = $userVal->user_validation_id;
                    $capture->save();

                    Log::info("Saved manual admin validation from snapshot: subarea={$subarea->park_subarea_name}, content={$pending['content']}");

                    // Evaluate WMA Threshold Shift!
                    $subarea->evaluateThresholdShift();

                    // Broadcast updated status
                    DB::afterCommit(function () use ($subarea) {
                        broadcast(new SubareaStatusUpdated($subarea));
                    });
                }
            }
        }

        // 5. BROADCAST KE WEB UI
        $imageBase64 = 'data:image/jpeg;base64,'.base64_encode($decryptedImageBytes);
        broadcast(new IotDetectionReceived($macAddress, $imageBase64, $saveImage));

        Log::info('Snapshot diterima dan berhasil dibroadcast', ['mac' => $macAddress, 'saved' => $saveImage]);

        return response()->json(['status' => 'success', 'message' => 'Snapshot received and broadcasted.']);
    }

    /**
     * Endpoint untuk menerima hitungan (count) kendaraan dari IoT device.
     */
    public function receiveCount(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'timestamp' => 'required|numeric',
            'count' => 'required|integer|min:0',
            'signature' => 'required|string',
        ]);

        $macAddress = $request->mac_address;

        if (! $this->validateMacAddress($macAddress)) {
            return response()->json(['status' => 'error', 'message' => 'Device not registered.'], 403);
        }

        // Validate HMAC signature
        $iotSecret = config('services.iot.secret');
        $key32 = substr(hash('sha256', $iotSecret, true), 0, 32);

        $payloadToSign = [
            'mac_address' => $macAddress,
            'timestamp' => (int) $request->timestamp,
            'count' => (int) $request->count,
        ];

        $dataToSign = json_encode($payloadToSign, JSON_UNESCAPED_SLASHES);
        $calculatedSignature = hash_hmac('sha256', $dataToSign, $key32);

        if (! hash_equals($calculatedSignature, $request->signature)) {
            Log::warning('Rejected: Invalid HMAC signature', ['mac' => $macAddress]);

            return response()->json(['status' => 'error', 'message' => 'Invalid signature.'], 401);
        }

        // Auto-promote: Jika device mengirim data count yang valid,
        // artinya device hidup — set ke online jika belum
        $status = Cache::get("iot_status_{$macAddress}", 'offline');
        if ($status !== 'online') {
            Log::info("🔄 Auto-promote (WS): Device {$macAddress} mengirim count tapi status={$status}. Mempromosikan ke ONLINE.");
            Cache::forever("iot_status_{$macAddress}", 'online');
            Cache::forever("iot_connection_type_{$macAddress}", 'ws');
            broadcast(new IotDeviceStatusChanged($macAddress, 'online'));
        }

        // Update database
        $device = IotDevice::where('device_mac_address', $macAddress)->first();
        if ($device && $device->subarea) {
            $subarea = $device->subarea;
            $subarea->current_count = (int) $request->count;
            $subarea->save();

            // Broadcast count updated
            broadcast(new IotCountUpdated($macAddress, $request->count));
            DB::afterCommit(function () use ($subarea) {
                broadcast(new SubareaStatusUpdated($subarea));
            });
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Endpoint untuk memberikan konfigurasi terbaru ke IoT device pada saat startup.
     */
    public function receiveConfigQuery(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'timestamp' => 'required|numeric',
            'signature' => 'required|string',
        ]);

        $macAddress = $request->mac_address;

        if (! $this->validateMacAddress($macAddress)) {
            return response()->json(['status' => 'error', 'message' => 'Device not registered.'], 403);
        }

        // Validasi HMAC Signature
        $iotSecret = config('services.iot.secret');
        $key32 = substr(hash('sha256', $iotSecret, true), 0, 32);

        $dataToSign = "{$macAddress}:{$request->timestamp}";
        $calculatedSignature = hash_hmac('sha256', $dataToSign, $key32);

        if (! hash_equals($calculatedSignature, $request->signature)) {
            Log::warning('Rejected: Invalid HMAC signature', ['mac' => $macAddress]);

            return response()->json(['status' => 'error', 'message' => 'Invalid signature.'], 401);
        }

        $device = IotDevice::where('device_mac_address', $macAddress)->first();
        if ($device && $device->subarea) {
            $subarea = $device->subarea;
            Log::info('Konfigurasi dikirim ke IoT Device', ['mac' => $macAddress]);

            return response()->json([
                'status' => 'success',
                'config' => [
                    'max_slots' => (int) $subarea->max_slots,
                    'detection_polygon' => $subarea->detection_polygon ?? [],
                    'threshold_banyak' => (float) ($subarea->threshold_banyak ?? 30.0),
                    'threshold_terbatas' => (float) ($subarea->threshold_terbatas ?? 80.0),
                ],
            ]);
        }

        Log::warning('Config query ditolak: Subarea tidak ditemukan', ['mac' => $macAddress]);

        return response()->json(['status' => 'error', 'message' => 'Subarea not found.'], 404);
    }


}
