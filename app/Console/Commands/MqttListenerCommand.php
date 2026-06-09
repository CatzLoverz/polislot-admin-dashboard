<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Events\IotStreamReceived;
use App\Events\IotDeviceStatusChanged;
use App\Events\IotCountUpdated;
use App\Models\IotDevice;
use App\Models\IotCapture;
use App\Models\UserValidation;
use App\Models\Validation;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class MqttListenerCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mqtt:listen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Listen to MQTT broker for IoT snapshots and broadcast to Reverb';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Memulai MQTT Listener...");
        $secretKey = config('services.iot.secret');
        
        if (!$secretKey) {
            $this->error("IOT_API_SECRET tidak ditemukan di configuration services.php!");
            return 1;
        }

        try {
            $key32 = substr(hash('sha256', $secretKey, true), 0, 32);

            // default offline status for devices
            $devices = IotDevice::all();
            foreach ($devices as $device) {
                $mac = $device->device_mac_address;
                Cache::forever("iot_status_{$mac}", 'offline');
                broadcast(new IotDeviceStatusChanged($mac, 'offline'));

                if ($device->subarea) {
                    $subarea = $device->subarea;
                    $subarea->current_count = 0;
                    $subarea->save();
                    broadcast(new IotCountUpdated($mac, 0));
                }
            }
            $this->info("🔄 Reset {$devices->count()} device status ke OFFLINE dan count ke 0 (cache + broadcast)");

            // announce server online
            $mqtt = MQTT::connection();
            
            $serverPayload = ['status' => 'online'];
            $serverPayload['signature'] = hash_hmac('sha256', json_encode($serverPayload, JSON_UNESCAPED_SLASHES), $key32);
            
            $mqtt->publish('polislot/server/status', json_encode($serverPayload, JSON_UNESCAPED_SLASHES), 1, true);
            $this->info("✅ Server Status: ONLINE (Diumumkan ke MQTT dengan HMAC)");

            // Connection test to all listed devices
            foreach ($devices as $device) {
                $mac = $device->device_mac_address;
                $testPayload = ['action' => 'connection_test', 'timestamp' => time()];
                $testPayload['signature'] = hash_hmac('sha256', json_encode($testPayload, JSON_UNESCAPED_SLASHES), $key32);
                $mqtt->publish("polislot/device/{$mac}/command", json_encode($testPayload, JSON_UNESCAPED_SLASHES), 1);
            }
            $this->info("📡 Connection test dikirim ke {$devices->count()} device");
            
            $this->info("Terhubung ke MQTT Broker. Mendengarkan polislot/device/+/snapshot...");
            
            $mqtt->subscribe('polislot/device/+/snapshot', function (string $topic, string $message) use ($secretKey) {
                $this->info("Pesan diterima di topik: {$topic}");
                
                try {
                    $payload = json_decode($message, true);
                    
                    if (!$payload || !isset($payload['mac_address'], $payload['timestamp'], $payload['encrypted_image'], $payload['iv'], $payload['signature'])) {
                        $this->warn("Payload tidak lengkap, diabaikan.");
                        return;
                    }

                    // Update last seen cache
                    Cache::put("iot_last_seen_{$payload['mac_address']}", time());

                    // 1. Validasi HMAC Signature
                    $receivedSignature = $payload['signature'];
                    
                    $payloadToSign = [
                        'mac_address' => $payload['mac_address'],
                        'timestamp' => (int) $payload['timestamp'],
                        'encrypted_image' => $payload['encrypted_image'],
                        'iv' => $payload['iv']
                    ];
                    if (isset($payload['current_count'])) {
                        $payloadToSign['current_count'] = (int) $payload['current_count'];
                    }
                    if (isset($payload['save_image'])) {
                        $payloadToSign['save_image'] = filter_var($payload['save_image'], FILTER_VALIDATE_BOOLEAN);
                    }
                    
                    $key32 = substr(hash('sha256', $secretKey, true), 0, 32);
                    $dataToSign = json_encode($payloadToSign, JSON_UNESCAPED_SLASHES);
                    $calculatedSignature = hash_hmac('sha256', $dataToSign, $key32);
                    
                    if (!hash_equals($calculatedSignature, $receivedSignature)) {
                        $this->warn("🚨 DITOLAK: Signature HMAC tidak valid! (Secret Key Salah/Manipulasi Data)");
                        $this->warn("Data to sign: " . $dataToSign);
                        $this->warn("Python Sig: " . $receivedSignature);
                        $this->warn("PHP Sig   : " . $calculatedSignature);
                        return;
                    }

                    // 2. Dekripsi AES-256-CBC
                    $iv = base64_decode($payload['iv']);
                    $encryptedImage = base64_decode($payload['encrypted_image']);
                    
                    $decryptedImageBytes = openssl_decrypt(
                        $encryptedImage,
                        'aes-256-cbc',
                        $key32,
                        OPENSSL_RAW_DATA,
                        $iv
                    );

                    if ($decryptedImageBytes === false) {
                        $this->error("Gagal mendekripsi gambar.");
                        return;
                    }

                    $device = IotDevice::where('device_mac_address', $payload['mac_address'])->first();

                    if ($device) {
                        $saveImage = true;
                        if (isset($payload['save_image'])) {
                            $saveImage = filter_var($payload['save_image'], FILTER_VALIDATE_BOOLEAN);
                        }

                        if ($saveImage) {
                            // 3. Simpan gambar ke storage public
                            $fileName = 'capture_' . time() . '_' . str_replace(':', '', $payload['mac_address']) . '.jpg';
                            $path = 'iot_captures/' . $fileName;
                            
                            Storage::disk('public')->put($path, $decryptedImageBytes);
                            
                            $subarea = $device->subarea;
                            $cvStatus = null;
                            if ($subarea && $subarea->max_slots > 0) {
                                $count = isset($payload['current_count']) ? (int) $payload['current_count'] : ($subarea->current_count ?? 0);
                                $occupancy = ($count / $subarea->max_slots) * 100;
                                
                                if ($occupancy < ($subarea->threshold_banyak ?? 30.0)) {
                                    $cvStatus = 'banyak';
                                } elseif ($occupancy >= ($subarea->threshold_terbatas ?? 80.0)) {
                                    $cvStatus = 'penuh';
                                } else {
                                    $cvStatus = 'terbatas';
                                }
                            }

                            // 4. Simpan ke database IotCapture
                            $capture = IotCapture::create([
                                'device_id' => $device->device_id,
                                'capture_image_path' => $path,
                                'capture_is_trained' => false,
                                'capture_ai_status' => $cvStatus,
                            ]);
                            
                            $this->info("✅ Gambar berhasil didekripsi dan disimpan ke database (IotCapture). Status CV: {$cvStatus}");

                            // Save the current count if present in payload
                            if (isset($payload['current_count']) && $subarea) {
                                $subarea->current_count = (int) $payload['current_count'];
                                $subarea->save();
                                broadcast(new IotCountUpdated($payload['mac_address'], $payload['current_count']));
                            }

                            // Check pending validation in cache
                            $cleanMac = str_replace(':', '', $payload['mac_address']);
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

                                    $this->info("📈 [MQTT] Saved manual validation from admin snapshot: subarea={$subarea->park_subarea_name}, content={$pending['content']}");

                                    // Evaluate WMA Threshold Shift!
                                    $subarea->evaluateThresholdShift();
                                }
                            }
                        } else {
                            $this->info("ℹ️ Snapshot diterima tetapi tidak disimpan (save_image = false).");
                        }
                    } else {
                        $this->warn("⚠️ Perangkat dengan MAC {$payload['mac_address']} tidak terdaftar di database. Gambar tidak disimpan.");
                    }

                    // 5. Ubah bytes menjadi base64 string untuk UI HTML dan Broadcast ke Reverb
                    $imageBase64String = 'data:image/jpeg;base64,' . base64_encode($decryptedImageBytes);
                    broadcast(new IotStreamReceived($payload['mac_address'], $imageBase64String, $saveImage));
                    $this->info("📡 Gambar di-broadcast ke Web UI.");
                } catch (\Exception $e) {
                    $this->error("Error memproses pesan: " . $e->getMessage());
                }
            });

            // Listener tambahan untuk update count dari IoT Device via MQTT
            $mqtt->subscribe('polislot/device/+/count', function (string $topic, string $message) use ($secretKey) {
                try {
                    $payload = json_decode($message, true);
                    if (!$payload || !isset($payload['signature'])) {
                        $this->warn("⚠️ Pesan count ditolak: Tidak ada signature.");
                        return;
                    }

                    $receivedSignature = $payload['signature'];
                    unset($payload['signature']);
                    
                    $dataToSign = json_encode($payload, JSON_UNESCAPED_SLASHES);
                    $key32 = substr(hash('sha256', $secretKey, true), 0, 32);
                    $calculatedSignature = hash_hmac('sha256', $dataToSign, $key32);

                    if (!hash_equals($calculatedSignature, $receivedSignature)) {
                        $this->warn("🚨 DITOLAK: Signature count tidak valid!");
                        return;
                    }

                    if (isset($payload['mac_address'], $payload['count'])) {
                        $mac = $payload['mac_address'];
                        $count = (int) $payload['count'];
                        
                        // Update last seen cache
                        Cache::put("iot_last_seen_{$mac}", time());
                        
                        // Hanya perbarui jika status di cache adalah online
                        $status = Cache::get("iot_status_{$mac}", 'offline');
                        if ($status === 'online') {
                            $device = IotDevice::where('device_mac_address', $mac)->first();
                            if ($device && $device->subarea) {
                                $subarea = $device->subarea;
                                $subarea->current_count = $count;
                                $subarea->save();
                                
                                // Broadcast count update
                                broadcast(new IotCountUpdated($mac, $count));
                            }
                        } else {
                            $this->warn("⚠️ Mengabaikan count MQTT dari perangkat offline: {$mac} (Count: {$count})");
                        }
                    }
                } catch (\Exception $e) {
                    $this->error("Error memproses count MQTT: " . $e->getMessage());
                }
            });

            // Listener tambahan untuk status Online/Offline (LWT)
            $mqtt->subscribe('polislot/device/+/status', function (string $topic, string $message) use ($secretKey) {
                // Topic format: polislot/device/{MAC}/status
                $parts = explode('/', $topic);
                $mac = $parts[2] ?? 'unknown';
                
                $payload = json_decode($message, true);
                if (!$payload || !isset($payload['signature'])) {
                    $this->warn("⚠️ Pesan status ditolak: Tidak ada signature. (Pesan: $message)");
                    return;
                }

                $receivedSignature = $payload['signature'];
                unset($payload['signature']);
                
                $dataToSign = json_encode($payload, JSON_UNESCAPED_SLASHES);
                $key32 = substr(hash('sha256', $secretKey, true), 0, 32);
                $calculatedSignature = hash_hmac('sha256', $dataToSign, $key32);

                if (!hash_equals($calculatedSignature, $receivedSignature)) {
                    $this->warn("🚨 DITOLAK: Signature status tidak valid!");
                    return;
                }
                
                $status = strtolower($payload['status'] ?? 'offline'); // 'online' atau 'offline'
                
                $this->info("⚡ Status Perangkat [{$mac}]: " . strtoupper($status) . " (Secured)");
                
                // Update last seen cache
                Cache::put("iot_last_seen_{$mac}", time());
                
                // Simpan status terbaru ke Cache agar web bisa tahu status awal saat halaman baru dibuka
                Cache::forever("iot_status_{$mac}", $status);
                if ($status === 'online') {
                    Cache::forever("iot_connection_type_{$mac}", 'mqtt');
                }
                
                // Broadcast ke Reverb agar UI berubah secara real-time
                broadcast(new IotDeviceStatusChanged($mac, $status));

                // Log status changes to laravel.log
                if ($status === 'online') {
                    Log::info("Device authenticated successfully via MQTT", [
                        'mac' => $mac,
                        'connection_type' => 'mqtt'
                    ]);
                } else {
                    Log::info("Device disconnected via MQTT (LWT)", [
                        'mac' => $mac,
                        'connection_type' => 'mqtt'
                    ]);
                }

                // Reset count to 0 in database if device goes offline via MQTT
                if ($status === 'offline') {
                    $device = IotDevice::where('device_mac_address', $mac)->first();
                    if ($device && $device->subarea) {
                        $subarea = $device->subarea;
                        $subarea->current_count = 0;
                        $subarea->save();

                        // Broadcast count updated to 0
                        broadcast(new IotCountUpdated($mac, 0));
                        $this->info("📈 [MQTT] Device {$mac} went offline. Reset subarea count to 0.");
                    }
                }

                // Auto-push config if device connects and becomes online via MQTT
                if ($status === 'online') {
                    $device = IotDevice::where('device_mac_address', $mac)->first();
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
                        $payloadData['signature'] = hash_hmac('sha256', json_encode($payloadData, JSON_UNESCAPED_SLASHES), $key32);
                        
                        $mqtt->publish("polislot/device/{$mac}/command", json_encode($payloadData, JSON_UNESCAPED_SLASHES), 1);
                        $this->info("📈 [MQTT] Auto-pushed config to device {$mac} on connection.");
                        
                        // Log config push to laravel.log
                        Log::info("Konfigurasi dikirim ke IoT Device via MQTT", [
                            'mac' => $mac,
                            'connection_type' => 'mqtt'
                        ]);
                    }
                }
            });
            
            $mqtt->loop(true);
            
        } catch (\Exception $e) {
            $this->error("Koneksi MQTT Gagal: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
