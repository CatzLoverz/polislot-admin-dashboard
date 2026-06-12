<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\IotDevice;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IotDetectionControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $device;
    protected $mac = '00:11:22:33:44:55';
    protected $secret = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.iot.secret', $this->secret);

        $area = ParkArea::create(['park_area_name' => 'Area', 'park_area_code' => 'AR', 'park_area_data' => '[]']);
        $subarea = ParkSubarea::create([
            'park_area_id' => $area->park_area_id, 
            'park_subarea_name' => 'Sub',
            'park_subarea_polygon' => '[]',
            'max_slots' => 100,
        ]);

        $this->device = IotDevice::create([
            'device_mac_address' => $this->mac,
            'park_subarea_id' => $subarea->park_subarea_id,
        ]);
        
        Event::fake([
            \App\Events\IotDeviceStatusChanged::class,
            \App\Events\SubareaStatusUpdated::class,
            \App\Events\IotCountUpdated::class,
            \App\Events\IotDetectionReceived::class,
            \App\Events\IotCommandSent::class
        ]);
        Storage::fake('public');
    }

    #[Test]
    public function receive_detection_fails_with_unregistered_mac()
    {
        $response = $this->postJson('/api/iot/detection', [
            'mac_address' => '00:00:00:00:00:00',
            'frame' => 'base64frame',
            'timestamp' => time(),
            'signature' => 'invalid',
        ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function receive_detection_fails_with_invalid_signature()
    {
        $response = $this->postJson('/api/iot/detection', [
            'mac_address' => $this->mac,
            'frame' => 'base64frame',
            'timestamp' => time(),
            'signature' => 'invalid',
        ]);

        $response->assertStatus(401);
    }

    #[Test]
    public function receive_detection_succeeds_with_valid_signature()
    {
        $timestamp = time();
        $frame = 'base64frame';
        $frameLength = strlen($frame);
        $dataToSign = "{$this->mac}:{$timestamp}:{$frameLength}";
        $signature = hash_hmac('sha256', $dataToSign, $this->secret);

        $response = $this->postJson('/api/iot/detection', [
            'mac_address' => $this->mac,
            'frame' => $frame,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function receive_snapshot_succeeds()
    {
        $timestamp = time();
        $key32 = substr(hash('sha256', $this->secret, true), 0, 32);
        
        $iv = openssl_random_pseudo_bytes(16);
        $image = 'fake_image_data';
        $encryptedImage = openssl_encrypt($image, 'aes-256-cbc', $key32, OPENSSL_RAW_DATA, $iv);

        $payloadToSign = [
            'mac_address' => $this->mac,
            'timestamp' => $timestamp,
            'encrypted_image' => base64_encode($encryptedImage),
            'iv' => base64_encode($iv),
            'current_count' => 10,
            'save_image' => true,
        ];
        
        $dataToSign = json_encode($payloadToSign, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $dataToSign, $key32);
        
        $payloadToSign['signature'] = $signature;

        $response = $this->postJson('/api/iot/snapshot', $payloadToSign);

        $response->assertStatus(200);
        $response->assertStatus(200);
    }

    #[Test]
    public function receive_count_succeeds()
    {
        $timestamp = time();
        $count = 5;
        $key32 = substr(hash('sha256', $this->secret, true), 0, 32);
        
        $payloadToSign = [
            'mac_address' => $this->mac,
            'timestamp' => $timestamp,
            'count' => $count,
        ];
        $dataToSign = json_encode($payloadToSign, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha256', $dataToSign, $key32);

        $response = $this->postJson('/api/iot/count', [
            'mac_address' => $this->mac,
            'timestamp' => $timestamp,
            'count' => $count,
            'signature' => $signature,
        ]);

        $response->assertStatus(200);
    }

    #[Test]
    public function receive_config_query_succeeds()
    {
        $timestamp = time();
        $key32 = substr(hash('sha256', $this->secret, true), 0, 32);
        
        $dataToSign = "{$this->mac}:{$timestamp}";
        $signature = hash_hmac('sha256', $dataToSign, $key32);

        $response = $this->postJson('/api/iot/config', [
            'mac_address' => $this->mac,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        $response->assertStatus(200)->assertJsonStructure(['status', 'config']);
    }
}
