<?php

namespace App\Http\Controllers\Web;

use App\Events\IotCommandSent;
use App\Events\SubareaStatusUpdated;
use App\Http\Controllers\Controller;
use App\Models\IotCapture;
use App\Models\IotDevice;
use App\Models\UserValidation;
use App\Models\Validation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use PhpMqtt\Client\Facades\MQTT;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class IotDetectionController extends Controller
{
    /**
     * Menampilkan halaman IoT Stream Viewer.
     * MAC Address dipilih dari daftar perangkat yang terdaftar di database.
     */
    public function index(Request $request): View
    {
        // Ambil semua device dari database, termasuk subarea dan area induknya
        $devices = IotDevice::with('subarea.parkArea')->get();

        // MAC Address yang dipilih (dari query string atau default ke device pertama)
        $targetMac = $request->query('mac', $devices->first()?->device_mac_address ?? '00:00:00:00:00:00');

        // Verifikasi status aktual untuk device yang dipilih (bukan hanya baca cache)
        // syncStatus() akan cek Reverb presence / MQTT last-seen, dan koreksi jika stale
        $initialStatus = IotDevice::syncStatus($targetMac);

        $selectedDevice = $devices->firstWhere('device_mac_address', $targetMac);
        $maxSlots = $selectedDevice?->subarea?->max_slots ?? 0;
        $detectionPolygon = $selectedDevice?->subarea?->detection_polygon ?? [];
        $thresholdBanyak = $selectedDevice?->subarea?->threshold_banyak ?? 30.0;
        $thresholdTerbatas = $selectedDevice?->subarea?->threshold_terbatas ?? 80.0;
        $initialCount = $selectedDevice?->subarea?->current_count ?? 0;

        $liveStatusData = $selectedDevice?->subarea ? $selectedDevice->subarea->getLiveStatus() : [];
        $parkAreaId = $selectedDevice?->subarea?->park_area_id ?? 0;
        $parkSubareaId = $selectedDevice?->subarea?->park_subarea_id ?? 0;
        $isValidated = $liveStatusData['is_validated'] ?? false;
        $hasUserReport = $liveStatusData['has_user_report'] ?? false;
        $votedStatus = $liveStatusData['voted_status'] ?? null;
        $anchorCvStatus = $liveStatusData['anchor_cv_status'] ?? null;
        $validationExpiresAt = $liveStatusData['validation_expires_at'] ?? null;
        $lastValidationTime = $liveStatusData['last_validation_time'] ?? null;

        $captures = [];
        if ($selectedDevice) {
            $captures = IotCapture::where('device_id', $selectedDevice->device_id)
                ->with('userValidation')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        if ($request->ajax()) {
            return view('Contents.IoTDetection.partials.captures_grid', compact('captures', 'targetMac'));
        }

        return view('Contents.IoTDetection.viewer', compact(
            'devices', 'targetMac', 'initialStatus', 'maxSlots',
            'detectionPolygon', 'thresholdBanyak', 'thresholdTerbatas', 'captures', 'initialCount',
            'validationExpiresAt', 'lastValidationTime', 'parkAreaId', 'parkSubareaId', 'isValidated', 'hasUserReport',
            'votedStatus', 'anchorCvStatus'
        ));
    }

    /**
     * Helper: Generate HMAC signature untuk command payload.
     * Digunakan oleh triggerSnapshot dan sendChat.
     */
    private function generateCommandSignature(array $payloadData): string
    {
        $key32 = substr(hash('sha256', config('services.iot.secret'), true), 0, 32);

        return hash_hmac('sha256', json_encode($payloadData, JSON_UNESCAPED_SLASHES), $key32);
    }

    /**
     * Helper: Kirim command ke device via kedua jalur (Reverb WS + MQTT).
     *
     * Reverb WS = untuk device yang terhubung via iot_ws_client.py (presence channel)
     * MQTT      = untuk device yang terhubung via mqtt_test_iot.py (backward compatibility)
     */
    private function sendCommandToDevice(string $mac, string $action, array $payloadData): array
    {
        $errors = [];

        // 1. Broadcast via Reverb WebSocket (untuk iot_ws_client.py)
        try {
            $payloadForWs = $payloadData;
            $payloadForWs['signature'] = $this->generateCommandSignature($payloadData);

            broadcast(new IotCommandSent($mac, $action, $payloadForWs, $payloadForWs['signature']));
            Log::info("Command '{$action}' broadcasted via Reverb WS", ['mac' => $mac]);
        } catch (Exception $e) {
            $errors[] = 'Reverb: '.$e->getMessage();
            Log::error('Failed to broadcast command via Reverb WS', ['mac' => $mac, 'error' => $e->getMessage()]);
        }

        // 2. Publish via MQTT (untuk mqtt_test_iot.py — backward compatibility)
        try {
            $topic = "polislot/device/{$mac}/command";
            $payloadForMqtt = $payloadData;
            $payloadForMqtt['signature'] = $this->generateCommandSignature($payloadData);
            $payload = json_encode($payloadForMqtt, JSON_UNESCAPED_SLASHES);

            $mqtt = MQTT::connection('publisher');
            $mqtt->publish($topic, $payload, 0);
            $mqtt->disconnect();
            Log::info("Command '{$action}' published via MQTT", ['mac' => $mac, 'topic' => $topic]);
        } catch (Exception $e) {
            $errors[] = 'MQTT: '.$e->getMessage();
            Log::error('Failed to publish command via MQTT', ['mac' => $mac, 'error' => $e->getMessage()]);
        }

        return $errors;
    }

    /**
     * Mengirim perintah 'snapshot' ke perangkat IoT via Reverb + MQTT
     */
    public function triggerSnapshot(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'save_image' => 'nullable',
        ]);

        $mac = $request->mac_address;

        $payloadData = [
            'action' => 'snapshot',
            'timestamp' => time(),
            'requested_by' => auth()->user()->id ?? 'admin',
        ];

        if ($request->has('save_image')) {
            $payloadData['save_image'] = filter_var($request->save_image, FILTER_VALIDATE_BOOLEAN);
        }

        $errors = $this->sendCommandToDevice($mac, 'snapshot', $payloadData);

        if (count($errors) === 2) {
            Log::warning('Gagal mengirim perintah snapshot ke perangkat', ['mac' => $mac, 'errors' => $errors]);

            // Kedua jalur gagal
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim perintah: '.implode(' | ', $errors),
            ], 500);
        }

        Log::info('Perintah snapshot berhasil dikirim', ['mac' => $mac]);

        return response()->json([
            'success' => true,
            'message' => "Perintah snapshot berhasil dikirim ke perangkat {$mac}",
        ]);
    }

    /**
     * Menyimpan setelan deteksi (max slots, detection polygon, thresholds) ke subarea terkait device ini.
     * Kemudian mem-push config terupdate ke IoT Device (jika online).
     */
    public function saveSettings(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'max_slots' => 'required|integer|min:0',
            'detection_polygon' => 'nullable|array',
            'threshold_banyak' => 'required|numeric|min:5|max:90',
            'threshold_terbatas' => 'required|numeric|min:10|max:95',
        ]);

        $mac = $request->mac_address;
        $device = IotDevice::where('device_mac_address', $mac)->first();

        if (! $device || ! $device->subarea) {
            Log::warning('Gagal menyimpan settings: perangkat atau subarea tidak ditemukan', ['mac' => $mac]);

            return response()->json([
                'success' => false,
                'message' => 'Perangkat atau subarea tidak ditemukan.',
            ], 404);
        }

        $subarea = $device->subarea;
        $subarea->max_slots = $request->max_slots;
        $subarea->detection_polygon = $request->detection_polygon;
        $subarea->threshold_banyak = $request->threshold_banyak;
        $subarea->threshold_terbatas = $request->threshold_terbatas;
        $subarea->save();

        // Broadcast updated status setelah transaksi commit
        DB::afterCommit(function () use ($subarea) {
            broadcast(new SubareaStatusUpdated($subarea));
        });

        // Push update_config command to the device
        $payloadData = [
            'action' => 'update_config',
            'max_slots' => (int) $request->max_slots,
            'detection_polygon' => $request->detection_polygon ?? [],
            'threshold_banyak' => (float) $request->threshold_banyak,
            'threshold_terbatas' => (float) $request->threshold_terbatas,
            'timestamp' => time(),
        ];

        // Send command to device via Reverb WS & MQTT
        $this->sendCommandToDevice($mac, 'update_config', $payloadData);

        Log::info('Konfigurasi deteksi berhasil disimpan dan dikirim ke perangkat', ['mac' => $mac]);

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi deteksi berhasil disimpan dan dikirim ke perangkat.',
        ]);
    }

    /**
     * Menerima request validasi manual Admin dari Web, menyimpan data ke cache pending_validation,
     * lalu memicu perintah 'snapshot' ke perangkat IoT.
     */
    public function validateStream(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
            'validation_content' => 'required|in:banyak,terbatas,penuh',
        ]);

        $mac = $request->mac_address;
        $content = $request->validation_content;

        $device = IotDevice::where('device_mac_address', $mac)->first();
        if (! $device || ! $device->subarea) {
            Log::warning('Validasi stream gagal: Perangkat atau subarea tidak ditemukan', ['mac' => $mac]);

            return response()->json([
                'success' => false,
                'message' => 'Perangkat atau subarea tidak ditemukan.',
            ], 404);
        }

        // Cek status perangkat (default: offline)
        $status = IotDevice::getStatus($mac);
        if ($status === 'offline') {
            // Jalankan flow validasi biasa tanpa IoT (ignore capture)
            $userVal = UserValidation::create([
                'user_id' => auth()->user()->user_id ?? 1,
                'validation_id' => Validation::first()->validation_id ?? 1,
                'park_subarea_id' => $device->subarea->park_subarea_id,
                'user_validation_content' => $content,
            ]);

            // Evaluasi pergeseran threshold WMA
            $device->subarea->evaluateThresholdShift();

            // Broadcast status update setelah transaksi commit
            DB::afterCommit(function () use ($device) {
                broadcast(new SubareaStatusUpdated($device->subarea));
            });

            Log::info('Perangkat offline. Validasi manual langsung diproses', [
                'mac' => $mac,
                'content' => $content,
                'subarea_id' => $device->subarea->park_subarea_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Perangkat offline. Validasi ['.strtoupper($content).'] berhasil diproses langsung (tanpa IoT).',
            ]);
        }

        // Simpan validation content ke Cache agar saat snapshot datang kita bisa menyimpannya ke user_validations
        $cleanMac = str_replace(':', '', $mac);
        Cache::put("pending_validation_{$cleanMac}", [
            'content' => $content,
            'user_id' => auth()->user()->user_id ?? 1, // pastikan admin user id tersimpan
        ], 120); // 2 menit timeout

        // Kirim perintah snapshot ke device
        $payloadData = [
            'action' => 'snapshot',
            'timestamp' => time(),
            'requested_by' => auth()->user()->id ?? 'admin',
            'save_image' => true,
        ];

        $errors = $this->sendCommandToDevice($mac, 'snapshot', $payloadData);

        if (count($errors) === 2) {
            Cache::forget("pending_validation_{$cleanMac}");
            Log::warning('Gagal mengirim perintah snapshot untuk validasi', ['mac' => $mac, 'errors' => $errors]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim perintah: '.implode(' | ', $errors),
            ], 500);
        }

        Log::info('Validasi dipicu, snapshot diminta', ['mac' => $mac, 'content' => $content]);

        return response()->json([
            'success' => true,
            'message' => "Validasi {$content} dipicu, menunggu snapshot dari perangkat IoT...",
        ]);
    }

    /**
     * Batch Download snapshot images as a ZIP file.
     *
     * @return BinaryFileResponse|RedirectResponse
     */
    public function downloadBatch(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'capture_ids' => 'nullable|array',
            'capture_ids.*' => 'integer',
            'filter_trained' => 'nullable|string|in:all,yes,no',
            'filter_cv_status' => 'nullable|string|in:all,banyak,terbatas,penuh',
            'mark_as_trained' => 'nullable|string', // received as string from HTML form
        ]);

        $mac = $request->mac_address;
        $device = IotDevice::where('device_mac_address', $mac)->first();

        if (! $device) {
            return abort(404, 'Device not found.');
        }

        $query = IotCapture::where('device_id', $device->device_id)->with('userValidation');

        if ($request->has('capture_ids') && ! empty($request->capture_ids)) {
            $query->whereIn('capture_id', $request->capture_ids);
        } else {
            if ($request->filled('filter_trained') && $request->filter_trained !== 'all') {
                $isTrained = $request->filter_trained === 'yes';
                $query->where('capture_is_trained', $isTrained);
            }
            if ($request->filled('filter_cv_status') && $request->filter_cv_status !== 'all') {
                $query->where('capture_ai_status', $request->filter_cv_status);
            }
        }

        $captures = $query->get();

        if ($captures->isEmpty()) {
            Log::warning('Batch download dibatalkan: tidak ada data capture yang cocok', ['mac' => $mac]);

            return redirect()->back()->with('error', 'Tidak ada gambar yang cocok dengan kriteria unduh.');
        }

        $zip = new ZipArchive;
        $oldestDate = $captures->min('created_at')->format('d-m-Y');
        $newestDate = $captures->max('created_at')->format('d-m-Y');
        $zipFileName = "polislot_dataset_{$oldestDate}_{$newestDate}.zip";
        $zipFilePath = storage_path('app/public/'.$zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return abort(500, 'Gagal membuat file ZIP.');
        }

        $addedFiles = 0;
        $idsToUpdate = [];

        foreach ($captures as $capture) {
            $filePath = storage_path('app/public/'.$capture->capture_image_path);
            if (file_exists($filePath)) {
                $dateStr = $capture->created_at->format('d-m-Y');
                $cvStatus = $capture->capture_ai_status ?: 'unknown';
                $valStatus = $capture->userValidation ? $capture->userValidation->user_validation_content : 'none';
                $localName = "capture_{$dateStr}_cv-{$cvStatus}_val-{$valStatus}_{$capture->capture_id}.jpg";

                $zip->addFile($filePath, $localName);
                $addedFiles++;

                if ($request->mark_as_trained === '1' || $request->mark_as_trained === 'true') {
                    $idsToUpdate[] = $capture->capture_id;
                }
            }
        }

        $zip->close();

        if ($addedFiles === 0) {
            if (file_exists($zipFilePath)) {
                unlink($zipFilePath);
            }

            return redirect()->back()->with('error', 'Semua berkas gambar terpilih tidak ditemukan di disk server.');
        }

        if (! empty($idsToUpdate)) {
            IotCapture::whereIn('capture_id', $idsToUpdate)->update(['capture_is_trained' => true]);
            Log::info('Tandai capture sebagai trained selama batch download', ['count' => count($idsToUpdate)]);
        }

        Log::info('Batch download sukses', ['mac' => $mac, 'file' => $zipFileName, 'total_files' => $addedFiles]);

        return response()->download($zipFilePath)->deleteFileAfterSend(true);
    }

    /**
     * Batch Delete snapshots from database and local storage.
     */
    public function deleteBatch(Request $request): JsonResponse
    {
        $request->validate([
            'capture_ids' => 'required|array',
            'capture_ids.*' => 'integer',
        ]);

        $captures = IotCapture::whereIn('capture_id', $request->capture_ids)->get();
        $deletedCount = 0;

        foreach ($captures as $capture) {
            $filePath = storage_path('app/public/'.$capture->capture_image_path);
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
            $capture->delete();
            $deletedCount++;
        }

        Log::info('Batch delete capture IoT sukses', ['deleted_count' => $deletedCount]);

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} gambar berhasil dihapus.",
        ]);
    }

    /**
     * Endpoint untuk memicu sinkronisasi status perangkat secara lazy dari frontend.
     * Dipanggil secara periodik oleh halaman Visualisasi Web agar ghost connections
     * WS yang terputus tidak nyangkut.
     *
     * @param  int  $id  Area ID
     */
    public function syncArea($id): JsonResponse
    {
        try {
            $devices = IotDevice::whereHas('subarea', function ($q) use ($id) {
                $q->where('park_area_id', $id);
            })->get();

            foreach ($devices as $device) {
                // syncStatus otomatis memeriksa keberadaan di Reverb / MQTT
                // dan mem-broadcast SubareaStatusUpdated jika perangkat offline
                IotDevice::syncStatus($device->device_mac_address);
            }

            return response()->json(['success' => true, 'message' => 'Synced']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Endpoint untuk langsung menandai device sebagai offline dari Web Admin UI.
     *
     * Dipanggil oleh halaman Visualisasi ketika Presence Channel mendeteksi device
     * 'leaving' (koneksi WS putus).
     *
     * @param  Request  $request  Data input request
     * @return JsonResponse Response status mark offline
     */
    public function markOffline(Request $request): JsonResponse
    {
        $request->validate([
            'mac_address' => 'required|string',
        ]);

        $macAddress = $request->mac_address;

        $isRegistered = IotDevice::where('device_mac_address', $macAddress)->exists();

        if (! $isRegistered) {
            Log::warning('markOffline rejected: Unregistered MAC', ['mac' => $macAddress]);

            return response()->json(['status' => 'error', 'message' => 'Device not registered.'], 403);
        }

        $currentStatus = Cache::get("iot_status_{$macAddress}", 'offline');
        if ($currentStatus === 'offline') {
            return response()->json(['status' => 'success', 'message' => 'Device already offline.']);
        }

        try {
            Log::info("markOffline: Memaksa device {$macAddress} ke OFFLINE via Web Admin.");
            IotDevice::markDeviceOffline($macAddress);

            return response()->json(['status' => 'success', 'message' => 'Device marked offline and broadcasts sent.']);
        } catch (Exception $e) {
            Log::error("markOffline failed for {$macAddress}: ".$e->getMessage());

            return response()->json(['status' => 'error', 'message' => 'Failed to mark device offline.'], 500);
        }
    }
}
