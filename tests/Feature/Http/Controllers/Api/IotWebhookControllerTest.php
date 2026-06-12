<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\IotDevice;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IotWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $device;
    protected $mac = '00:11:22:33:44:55';

    protected function setUp(): void
    {
        parent::setUp();
        
        $area = ParkArea::create(['park_area_name' => 'Area', 'park_area_code' => 'AR', 'park_area_data' => '[]']);
        $subarea = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'Sub', 'current_count' => 10, 'park_subarea_polygon' => '[]']);

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
    }

    #[Test]
    public function it_handles_member_added()
    {
        $response = $this->postJson('/api/iot/webhook', [
            'events' => [
                [
                    'name' => 'member_added',
                    'channel' => 'presence-iot.device.001122334455',
                    'user_id' => '001122334455',
                ]
            ]
        ], [
            'X-Reverb-Signature' => 'fake_signature'
        ]);

        $response->dump();
        $response->assertStatus(200);
    }

    #[Test]
    public function it_handles_member_removed()
    {
        $response = $this->postJson('/api/iot/webhook', [
            'events' => [
                [
                    'name' => 'member_removed',
                    'channel' => 'presence-iot.device.001122334455',
                    'user_id' => '001122334455',
                ]
            ]
        ], [
            'X-Reverb-Signature' => 'fake_signature'
        ]);

        $response->assertStatus(200);
        $this->assertEquals('offline', Cache::get("iot_status_{$this->mac}"));
        $this->assertEquals(0, $this->device->subarea->refresh()->current_count);
    }
}
