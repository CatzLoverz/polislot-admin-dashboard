<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\User;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
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
        // Buat user admin
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    #[Test]
    public function it_can_display_dashboard_index()
    {
        $this->actingAs($this->admin);

        // Buat beberapa data dummy
        User::create(['name' => 'User 1', 'email' => 'user@test.com', 'password' => 'pass', 'role' => 'user', 'email_verified_at' => now()]);
        ParkArea::create(['park_area_name' => 'Area 1', 'park_area_code' => 'A1', 'park_area_data' => []]);

        $response = $this->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('Contents.Dashboard.index');
        $response->assertViewHasAll(['totalUsers', 'totalParkAreas', 'totalSubareas', 'pendingRewards', 'parkAreas']);
    }

    #[Test]
    public function it_can_fetch_chart_data()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('dashboard.chart', ['period' => 'day']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'labels',
            'datasets',
            'period'
        ]);
    }

    #[Test]
    public function it_can_fetch_leaderboard()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('dashboard.leaderboard'));

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }

    #[Test]
    public function it_can_fetch_realtime_validations()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson(route('dashboard.realtime'));

        $response->assertStatus(200);
        $this->assertIsArray($response->json());
    }
}
