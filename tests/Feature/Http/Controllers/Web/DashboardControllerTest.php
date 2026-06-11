<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\User;
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
        $area = \App\Models\ParkArea::create(['park_area_name' => 'Area', 'park_area_code' => 'AR', 'park_area_data' => '[]']);
        $sub = \App\Models\ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'Sub', 'park_subarea_polygon' => '[]', 'max_slots' => 10]);
        $validation = \App\Models\Validation::create(['validation_points' => 10, 'validation_is_geofence_active' => true]);
        \App\Models\UserValidation::create(['validation_id' => $validation->validation_id, 'user_id' => $user->user_id, 'park_subarea_id' => $sub->park_subarea_id, 'user_validation_content' => 'banyak', 'user_validation_image' => 'img.jpg', 'user_validation_status' => 'valid']);
        $response = $this->actingAs($this->admin)->get('/dashboard/realtime');
        $response->assertStatus(200)->assertJsonStructure([['avatar', 'username', 'status', 'area', 'subarea', 'time', 'timestamp']]);
    }
}
