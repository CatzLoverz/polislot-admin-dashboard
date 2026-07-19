<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\User;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\ParkSubareaHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function index_returns_dashboard_view()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard');
        $response->assertStatus(200)->assertViewIs('Contents.Dashboard.index');
    }

    #[Test]
    public function chart_data_returns_json()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/chart');
        $response->assertStatus(200)->assertJsonStructure(['labels', 'datasets', 'period']);
    }

    #[Test]
    public function detection_chart_data_returns_json()
    {
        $response = $this->actingAs($this->admin)->get('/dashboard/detection-chart');
        $response->assertStatus(200)->assertJsonStructure(['labels', 'datasets', 'filter_type']);
    }

    #[Test]
    public function leaderboard_returns_json()
    {
        User::factory()->create(['role' => 'user', 'lifetime_points' => 100, 'email_verified_at' => now()]);
        $response = $this->actingAs($this->admin)->get('/dashboard/leaderboard');
        $response->assertStatus(200)->assertJsonStructure([['user_id', 'name', 'avatar', 'lifetime_points']]);
    }

    #[Test]
    public function realtime_validations_returns_json()
    {
        $user = User::factory()->create(['role' => 'user', 'email_verified_at' => now()]);
        $area = ParkArea::create(['park_area_name' => 'Area', 'park_area_code' => 'AR', 'park_area_data' => '[]']);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'Sub', 'park_subarea_polygon' => '[]', 'max_slots' => 10]);
        $validation = \App\Models\Validation::create(['validation_points' => 10, 'validation_is_geofence_active' => true]);
        \App\Models\UserValidation::create(['validation_id' => $validation->validation_id, 'user_id' => $user->user_id, 'park_subarea_id' => $sub->park_subarea_id, 'user_validation_content' => 'banyak', 'user_validation_image' => 'img.jpg', 'user_validation_status' => 'valid']);
        $response = $this->actingAs($this->admin)->get('/dashboard/realtime');
        $response->assertStatus(200)->assertJsonStructure([['avatar', 'username', 'status', 'area', 'subarea', 'time', 'timestamp']]);
    }

    #[Test]
    public function detection_chart_average_shows_raw_slot_count()
    {
        $area = ParkArea::create(['park_area_name' => 'Area Test', 'park_area_code' => 'AT', 'park_area_data' => '[]']);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'Sub Test', 'park_subarea_polygon' => '[]', 'max_slots' => 50]);

        // Gunakan hour saat ini
        $now = now()->startOfHour();

        ParkSubareaHistory::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'current_count' => 10,
            'max_slots' => 50,
            'status' => 'banyak',
            'created_at' => $now,
        ]);

        ParkSubareaHistory::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'current_count' => 20,
            'max_slots' => 50,
            'status' => 'banyak',
            'created_at' => $now->copy()->addMinutes(20),
        ]);

        $response = $this->actingAs($this->admin)->get('/dashboard/detection-chart?filter_type=tanggal&date_from=' . $now->toDateString());
        $response->assertStatus(200);

        $json = $response->json();
        $hourIndex = (int) $now->format('H');

        $dataPoint = $json['datasets'][0]['data'][$hourIndex];

        // Rata-rata slot kosong: ( (50 - 10) + (50 - 20) ) / 2 = (40 + 30) / 2 = 35
        $this->assertEquals(35.0, $dataPoint);
    }

    #[Test]
    public function detection_chart_weekly_returns_valid_data()
    {
        $area = ParkArea::create(['park_area_name' => 'Area Weekly', 'park_area_code' => 'AW', 'park_area_data' => '[]']);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'Sub Weekly', 'park_subarea_polygon' => '[]', 'max_slots' => 50]);

        $monday = now()->startOfWeek();

        // Seed records on Monday
        ParkSubareaHistory::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'current_count' => 10,
            'max_slots' => 50,
            'status' => 'banyak',
            'created_at' => $monday,
        ]);

        // Seed records on Wednesday
        ParkSubareaHistory::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'current_count' => 35,
            'max_slots' => 50,
            'status' => 'terbatas',
            'created_at' => $monday->copy()->addDays(2),
        ]);

        $response = $this->actingAs($this->admin)->get(
            '/dashboard/detection-chart?filter_type=minggu&week_from=' . $monday->toDateString()
        );
        $response->assertStatus(200);

        $json = $response->json();

        // Should have 7 labels (Senin - Minggu)
        $this->assertCount(7, $json['labels']);
        $this->assertArrayHasKey('drill_dates', $json);
        $this->assertCount(7, $json['drill_dates']);

        // Monday (index 0): slot kosong = 50 - 10 = 40
        $this->assertEquals(40.0, $json['datasets'][0]['data'][0]);

        // Wednesday (index 2): slot kosong = 50 - 35 = 15
        $this->assertEquals(15.0, $json['datasets'][0]['data'][2]);
    }
}
