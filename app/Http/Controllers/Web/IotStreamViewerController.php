<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\IotDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PhpMqtt\Client\Facades\MQTT;
use App\Events\ChatMessageSent;
use App\Events\IotCommandSent;

class IotStreamViewerController extends Controller
{
    /**
     * Menampilkan halaman IoT Stream Viewer.
     * MAC Address dipilih dari daftar perangkat yang terdaftar di database.
     */
    public function index(Request $request)
    {
        // Ambil semua device dari database, termasuk subarea dan area induknya
        $devices = IotDevice::with('subarea.parkArea')->get();
        
        // MAC Address yang dipilih (dari query string atau default ke device pertama)
        $targetMac = $request->query('mac', $devices->first()?->device_mac_address ?? '00:00:00:00:00:00');
        
        // Ambil status terakhir dari cache (default: offline jika tidak pernah online)
        $initialStatus = Cache::get("iot_status_{$targetMac}", 'offline');
        
        $selectedDevice = $devices->firstWhere('device_mac_address', $targetMac);
        $maxSlots = $selectedDevice?->subarea?->max_slots ?? 0;
        $detectionPolygon = $selectedDevice?->subarea?->detection_polygon ?? [];
        $thresholdBanyak = $selectedDevice?->subarea?->threshold_banyak ?? 30.0;
        $thresholdTerbatas = $selectedDevice?->subarea?->threshold_terbatas ?? 80.0;
        
        return view('Contents.IotStream.viewer', compact(
            'devices', 'targetMac', 'initialStatus', 'maxSlots', 
            'detectionPolygon', 'thresholdBanyak', 'thresholdTerbatas'
        ));
    }

    /**
     * Helper: Generate HMAC signature untuk command payload.
     * Digunakan oleh triggerSnapshot dan sendChat.
     */
    private function generateCommandSignature(array $payloadData): string
    {
        $key32 = substr(hash('sha256', env('IOT_API_SECRET'), true), 0, 32);
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
        } catch (\Exception $e) {
            $errors[] = "Reverb: " . $e->getMessage();
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
        } catch (\Exception $e) {
            $errors[] = "MQTT: " . $e->getMessage();
        }

        return $errors;
    }

    /**
     * Mengirim perintah 'snapshot' ke perangkat IoT via Reverb + MQTT
     */
    public function triggerSnapshot(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string'
        ]);

        $mac = $request->mac_address;
        
        $payloadData = [
            'action' => 'snapshot',
            'timestamp' => time(),
            'requested_by' => auth()->user()->id ?? 'admin'
        ];

        $errors = $this->sendCommandToDevice($mac, 'snapshot', $payloadData);

        if (count($errors) === 2) {
            // Kedua jalur gagal
            return response()->json([
                'success' => false,
                'message' => "Gagal mengirim perintah: " . implode(' | ', $errors)
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Perintah snapshot berhasil dikirim ke perangkat {$mac}"
        ]);
    }

    /**
     * Mengirim pesan Live Chat (Proof of Concept WebSockets)
     */
    public function sendChat(Request $request)
    {
        $request->validate([
            'mac_address' => 'required|string',
            'username' => 'required|string|max:50',
            'message' => 'required|string|max:500'
        ]);

        $mac = $request->mac_address;
        
        $payloadData = [
            'action' => 'chat',
            'timestamp' => time(),
            'username' => $request->username,
            'message' => $request->message
        ];

        $errors = $this->sendCommandToDevice($mac, 'chat', $payloadData);

        // Broadcast ke Web UI sendiri agar muncul di layar (Reverb)
        try {
            broadcast(new ChatMessageSent($request->username, $request->message));
        } catch (\Exception $e) {
            // Chat broadcast ke UI gagal, tapi command mungkin sudah terkirim
        }

        if (count($errors) === 2) {
            return response()->json([
                'success' => false,
                'message' => "Gagal mengirim chat: " . implode(' | ', $errors)
            ], 500);
        }

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Menyimpan setelan deteksi (max slots, detection polygon, thresholds) ke subarea terkait device ini.
     * Kemudian mem-push config terupdate ke IoT Device (jika online).
     */
    public function saveSettings(Request $request)
    {
        $request->validate([
            'mac_address'        => 'required|string',
            'max_slots'          => 'required|integer|min:0',
            'detection_polygon'  => 'nullable|array',
            'threshold_banyak'   => 'required|numeric|min:5|max:90',
            'threshold_terbatas' => 'required|numeric|min:10|max:95',
        ]);

        $mac = $request->mac_address;
        $device = IotDevice::where('device_mac_address', $mac)->first();

        if (!$device || !$device->subarea) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat atau subarea tidak ditemukan.'
            ], 404);
        }

        $subarea = $device->subarea;
        $subarea->max_slots = $request->max_slots;
        $subarea->detection_polygon = $request->detection_polygon;
        $subarea->threshold_banyak = $request->threshold_banyak;
        $subarea->threshold_terbatas = $request->threshold_terbatas;
        $subarea->save();

        // Push update_config command to the device
        $payloadData = [
            'action'             => 'update_config',
            'max_slots'          => (int) $request->max_slots,
            'detection_polygon'  => $request->detection_polygon ?? [],
            'threshold_banyak'   => (float) $request->threshold_banyak,
            'threshold_terbatas' => (float) $request->threshold_terbatas,
            'timestamp'          => time(),
        ];

        // Send command to device via Reverb WS & MQTT
        $this->sendCommandToDevice($mac, 'update_config', $payloadData);

        return response()->json([
            'success' => true,
            'message' => 'Konfigurasi deteksi berhasil disimpan dan dikirim ke perangkat.'
        ]);
    }

    /**
     * Menerima request validasi manual Admin dari Web, menyimpan data ke cache pending_validation,
     * lalu memicu perintah 'snapshot' ke perangkat IoT.
     */
    public function validateStream(Request $request)
    {
        $request->validate([
            'mac_address'        => 'required|string',
            'validation_content' => 'required|in:banyak,terbatas,penuh',
        ]);

        $mac = $request->mac_address;
        $content = $request->validation_content;

        $device = IotDevice::where('device_mac_address', $mac)->first();
        if (!$device || !$device->subarea) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat atau subarea tidak ditemukan.'
            ], 404);
        }

        // Simpan validation content ke Cache agar saat snapshot datang kita bisa menyimpannya ke user_validations
        $cleanMac = str_replace(':', '', $mac);
        \Illuminate\Support\Facades\Cache::put("pending_validation_{$cleanMac}", [
            'content' => $content,
            'user_id' => auth()->user()->user_id ?? 1, // pastikan admin user id tersimpan
        ], 120); // 2 menit timeout

        // Kirim perintah snapshot ke device
        $payloadData = [
            'action'       => 'snapshot',
            'timestamp'    => time(),
            'requested_by' => auth()->user()->id ?? 'admin'
        ];

        $errors = $this->sendCommandToDevice($mac, 'snapshot', $payloadData);

        if (count($errors) === 2) {
            \Illuminate\Support\Facades\Cache::forget("pending_validation_{$cleanMac}");
            return response()->json([
                'success' => false,
                'message' => "Gagal mengirim perintah: " . implode(' | ', $errors)
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Validasi {$content} dipicu, menunggu snapshot dari perangkat IoT..."
        ]);
    }
}

