<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserValidationControllerTest extends TestCase
{
    use \Illuminate\Foundation\Testing\WithoutMiddleware;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');

        $this->mock(\App\Services\MissionService::class, function ($mock) {
            $mock->shouldReceive('updateProgress')->andReturn(true);
        });

        $this->mock(\App\Services\HistoryService::class, function ($mock) {
            $mock->shouldReceive('log')->andReturn(true);
        });
    }

    #[Test]
    public function store_returns_201_when_geofence_disabled_and_no_location_provided()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => []]);

        // Geofence Disabled
        Validation::create([
            'validation_points' => 10,
            'validation_is_geofence_active' => false,
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'message' => 'Validasi berhasil! Anda mendapatkan 10 poin.',
            ]);
    }

    #[Test]
    public function store_returns_400_when_geofence_enabled_but_no_location()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $polygon = [['lat' => 0.0, 'lng' => 0.0]];
        $sub = ParkSubarea::create([
            'park_area_id' => $area->park_area_id,
            'park_subarea_name' => 'S1',
            'park_subarea_polygon' => $polygon,
        ]);

        // Geofence Enabled
        Validation::create([
            'validation_points' => 10,
            'validation_is_geofence_active' => true,
        ]);

        $this->actingAs($user);

        // No lat/lng provided
        $response = $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh',
        ]);

        $response->assertStatus(400)
            ->assertJson(['message' => 'Lokasi diperlukan untuk validasi di area ini.']);
    }

    #[Test]
    public function store_returns_422_when_geofence_enabled_and_user_too_far()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'B', 'park_area_code' => 'B', 'park_area_data' => []]);

        // Center at 0,0
        $polygon = [['lat' => 0.0, 'lng' => 0.0], ['lat' => 0.0001, 'lng' => 0.0001]];
        $sub = ParkSubarea::create([
            'park_area_id' => $area->park_area_id,
            'park_subarea_name' => 'S2',
            'park_subarea_polygon' => $polygon,
        ]);

        Validation::create([
            'validation_points' => 10,
            'validation_is_geofence_active' => true,
        ]);

        $this->actingAs($user);

        // User at 1.0, 1.0 (approx 100km+ away)
        $response = $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh',
            'latitude' => 1.0,
            'longitude' => 1.0,
        ]);

        $response->assertStatus(422)
            ->assertJson(['status' => 'error']); // Controller sends message but usually generic key check first
    }

    #[Test]
    public function store_returns_201_when_geofence_enabled_and_user_nearby()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'C', 'park_area_code' => 'C', 'park_area_data' => []]);

        // Center at 1.1185, 104.0483 (Polibatam approx)
        $lat = 1.1185;
        $lng = 104.0483;
        $polygon = [['lat' => $lat, 'lng' => $lng]];

        $sub = ParkSubarea::create([
            'park_area_id' => $area->park_area_id,
            'park_subarea_name' => 'S3',
            'park_subarea_polygon' => $polygon,
        ]);

        Validation::create([
            'validation_points' => 10,
            'validation_is_geofence_active' => true,
        ]);

        $this->actingAs($user);

        // User very close (same point)
        $response = $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh',
            'latitude' => $lat,
            'longitude' => $lng,
        ]);

        $response->assertStatus(201)
            ->assertJson(['status' => 'success']);
    }

    #[Test]
    public function store_returns_422_if_payload_invalid()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/validation', []);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    #[Test]
    public function store_returns_429_if_cooldown_active()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => []]);

        Validation::create([
            'validation_points' => 10,
            'validation_is_geofence_active' => false, // Disable geofence to focus on cooldown
        ]);

        $this->actingAs($user);

        // First validation
        $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh',
        ]);

        // Second validation (Too quick)
        $response = $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh',
        ]);

        $response->assertStatus(429)
            ->assertJson(['status' => 'error']);
    }
}
