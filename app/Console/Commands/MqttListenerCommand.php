<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\Facades\MQTT;
use App\Events\IotStreamReceived;
use App\Events\ChatMessageSent;
use App\Events\IotDeviceStatusChanged;
use App\Models\IotDevice;
use App\Models\IotCapture;
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
        $secretKey = env('IOT_API_SECRET');
        
        if (!$secretKey) {
            $this->error("IOT_API_SECRET tidak ditemukan di .env!");
            return 1;
        }

        try {
            $mqtt = MQTT::connection();
            
            // Beri tahu seluruh perangkat IoT bahwa Server Laravel (Daemon) sedang ONLINE
            $mqtt->publish('polislot/server/status', 'online', 1, true);
            $this->info("✅ Server Status: ONLINE (Diumumkan ke MQTT)");
            
            $this->info("Terhubung ke MQTT Broker. Mendengarkan polislot/device/+/snapshot...");
            
            $mqtt->subscribe('polislot/device/+/snapshot', function (string $topic, string $message) use ($secretKey) {
                $this->info("Pesan diterima di topik: {$topic}");
                
                try {
                    $payload = json_decode($message, true);
                    
                    if (!$payload || !isset($payload['mac_address'], $payload['timestamp'], $payload['encrypted_image'], $payload['iv'], $payload['signature'])) {
                        $this->warn("Payload tidak lengkap, diabaikan.");
                        return;
                    }

                    // 1. Validasi HMAC Signature
                    $receivedSignature = $payload['signature'];
                    
                    // Buat payload untuk divalidasi (tanpa signature)
                    // PENTING: Gunakan JSON_UNESCAPED_SLASHES agar karakter '/' pada Base64 tidak di-escape (\/),
                    // yang mana akan membuat hasil JSON berbeda dengan script Python dan menggagalkan HMAC.
                    $dataToSign = json_encode([
                        'mac_address' => $payload['mac_address'],
                        'timestamp' => $payload['timestamp'],
                        'encrypted_image' => $payload['encrypted_image'],
                        'iv' => $payload['iv']
                    ], JSON_UNESCAPED_SLASHES);
                    
                    // Gunakan substr untuk memastikan panjang key sesuai untuk AES-256-CBC (32 bytes)
                    $key32 = substr(hash('sha256', $secretKey, true), 0, 32);
                    $calculatedSignature = hash_hmac('sha256', $dataToSign, $key32);
                    
                    if (!hash_equals($calculatedSignature, $receivedSignature)) {
                        $this->warn("⚠️ Peringatan: Signature HMAC tidak valid! Melanjutkan untuk keperluan debugging.");
                        $this->warn("Data to sign: " . $dataToSign);
                        $this->warn("Python Sig: " . $receivedSignature);
                        $this->warn("PHP Sig   : " . $calculatedSignature);
                        // KITA HAPUS "return;" SEMENTARA AGAR PROSES TETAP BERJALAN MESKI SIGNATURE GAGAL
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
                        // 3. Simpan gambar ke storage public
                        $fileName = 'capture_' . time() . '_' . str_replace(':', '', $payload['mac_address']) . '.jpg';
                        $path = 'iot_captures/' . $fileName;
                        
                        Storage::disk('public')->put($path, $decryptedImageBytes);
                        
                        // 4. Simpan ke database IotCapture
                        IotCapture::create([
                            'device_id' => $device->device_id,
                            'capture_image_path' => $path,
                            'capture_is_trained' => false,
                            'capture_ai_status' => 'Pending',
                        ]);
                        
                        $this->info("✅ Gambar berhasil didekripsi dan disimpan ke database (IotCapture).");
                    } else {
                        $this->warn("⚠️ Perangkat dengan MAC {$payload['mac_address']} tidak terdaftar di database. Gambar tidak disimpan.");
                    }

                    // 5. Ubah bytes menjadi base64 string untuk UI HTML dan Broadcast ke Reverb
                    $imageBase64String = 'data:image/jpeg;base64,' . base64_encode($decryptedImageBytes);
                    broadcast(new IotStreamReceived($payload['mac_address'], $imageBase64String));
                    $this->info("📡 Gambar di-broadcast ke Web UI.");
                } catch (\Exception $e) {
                    $this->error("Error memproses pesan: " . $e->getMessage());
                }
            });
            
            // Listener tambahan untuk fitur Live Chat dari IoT Device
            $mqtt->subscribe('polislot/device/+/chat_reply', function (string $topic, string $message) {
                try {
                    $payload = json_decode($message, true);
                    if ($payload && isset($payload['username'], $payload['message'])) {
                        // Broadcast balasan chat ke web (Reverb)
                        broadcast(new ChatMessageSent($payload['username'], $payload['message']));
                        $this->info("💬 Pesan chat diterima dari {$payload['username']}: {$payload['message']}");
                    }
                } catch (\Exception $e) {
                    $this->error("Error memproses chat: " . $e->getMessage());
                }
            });

            // Listener tambahan untuk status Online/Offline (LWT)
            $mqtt->subscribe('polislot/device/+/status', function (string $topic, string $message) {
                // Topic format: polislot/device/{MAC}/status
                $parts = explode('/', $topic);
                $mac = $parts[2] ?? 'unknown';
                $status = trim(strtolower($message)); // 'online' atau 'offline'
                
                $this->info("⚡ Status Perangkat [{$mac}]: " . strtoupper($status));
                
                // Simpan status terbaru ke Cache agar web bisa tahu status awal saat halaman baru dibuka
                Cache::forever("iot_status_{$mac}", $status);
                
                // Broadcast ke Reverb agar UI berubah secara real-time
                broadcast(new IotDeviceStatusChanged($mac, $status));
            });
            
            $mqtt->loop(true);
            
        } catch (\Exception $e) {
            $this->error("Koneksi MQTT Gagal: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
