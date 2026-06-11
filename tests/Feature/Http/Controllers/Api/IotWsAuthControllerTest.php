<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\IotDevice;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IotWsAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $device;
    protected $mac = '00:11:22:33:44:55';
    protected $secret = 'test-secret';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.iot.secret', $this->secret);
        Config::set('broadcasting.connections.reverb.secret', 'reverb-secret');
        Config::set('broadcasting.connections.reverb.key', 'reverb-key');

        $area = ParkArea::create(['park_area_name' => 'Area', 'park_area_code' => 'AR', 'park_area_data' => '[]']);
        $subarea = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'Sub', 'park_subarea_polygon' => '[]']);

        $this->device = IotDevice::create([
            'device_mac_address' => $this->mac,
            'park_subarea_id' => $subarea->park_subarea_id,
        ]);
        
        Event::fake([
            \App\Events\IotDeviceStatusChanged::class,
            \App\Events\SubareaStatusUpdated::class,
            \App\Events\IotCountUpdated::class,
            \App\Events\IotStreamReceived::class,
            \App\Events\IotCommandSent::class
        ]);
    }

    #[Test]
    public function authenticate_succeeds_with_valid_credentials()
    {
        $timestamp = time();
        $socketId = '123.456';
        $channelName = 'presence-iot.device.001122334455';

        $dataToSign = "{$this->mac}:{$timestamp}";
        $key32 = substr(hash('sha256', $this->secret, true), 0, 32);
        $signature = hash_hmac('sha256', $dataToSign, $key32);

        $response = $this->postJson('/api/iot/ws-auth', [
            'socket_id' => $socketId,
            'channel_name' => $channelName,
            'mac_address' => $this->mac,
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        $response->assertStatus(200)->assertJsonStructure(['auth', 'channel_data']);
    }
    
    #[Test]
    public function authenticate_fails_with_invalid_mac()
    {
        $timestamp = time();
        $socketId = '123.456';
        $channelName = 'presence-iot.device.000000000000';

        $dataToSign = "00:00:00:00:00:00:{$timestamp}";
        $key32 = substr(hash('sha256', $this->secret, true), 0, 32);
        $signature = hash_hmac('sha256', $dataToSign, $key32);

        $response = $this->postJson('/api/iot/ws-auth', [
            'socket_id' => $socketId,
            'channel_name' => $channelName,
            'mac_address' => '00:00:00:00:00:00',
            'timestamp' => $timestamp,
            'signature' => $signature,
        ]);

        $response->assertStatus(403);
    }
}
