<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\ParkArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParkAreaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    #[Test]
    public function index_returns_view()
    {
        $response = $this->actingAs($this->admin)->get('/admin/park-area');
        $response->assertStatus(200)->assertViewIs('Contents.ParkArea.index');
    }

    #[Test]
    public function store_creates_park_area()
    {
        $response = $this->actingAs($this->admin)->post('/admin/park-area', [
            'park_area_name' => 'Area 1',
            'park_area_code' => 'A1',
            'center_lat' => -6.2,
            'center_lng' => 106.8,
            'zoom_level' => 15,
        ]);
        $response->assertRedirect(route('admin.park-area.index'))->assertSessionHas('swal_success_crud');
        $this->assertDatabaseHas('park_areas', ['park_area_name' => 'Area 1']);
    }

    #[Test]
    public function show_returns_view()
    {
        $area = ParkArea::create(['park_area_name' => 'Area', 'park_area_code' => 'AR', 'park_area_data' => '[]']);
        $response = $this->actingAs($this->admin)->get("/admin/park-area/{$area->park_area_id}");
        $response->assertStatus(200)->assertViewIs('Contents.ParkArea.show');
    }

    #[Test]
    public function destroy_deletes_park_area()
    {
        $area = ParkArea::create(['park_area_name' => 'Delete', 'park_area_code' => 'DL', 'park_area_data' => '[]']);
        $response = $this->actingAs($this->admin)->delete("/admin/park-area/{$area->park_area_id}");
        $response->assertRedirect(route('admin.park-area.index'))->assertSessionHas('swal_success_crud');
        $this->assertDatabaseMissing('park_areas', ['park_area_id' => $area->park_area_id]);
    }
}
