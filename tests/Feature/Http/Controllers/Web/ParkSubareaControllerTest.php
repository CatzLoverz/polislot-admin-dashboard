<?php

namespace Tests\Feature\Http\Controllers\Web;

use \App\Events\SubareaStatusUpdated;
use \Illuminate\Support\Facades\Event;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParkSubareaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $area;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->area = ParkArea::create(['park_area_name' => 'Area', 'park_area_code' => 'AR', 'park_area_data' => '[]']);
    }

    #[Test]
    public function store_creates_park_subarea()
    {
        $response = $this->actingAs($this->admin)->postJson("/admin/park-area/{$this->area->park_area_id}/subarea", [
            'name' => 'Subarea 1',
            'polygon' => '[{"lat":-6.2,"lng":106.8},{"lat":-6.21,"lng":106.81},{"lat":-6.22,"lng":106.82}]',
        ]);
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('park_subareas', ['park_subarea_name' => 'Subarea 1']);
    }

    #[Test]
    public function update_modifies_park_subarea()
    {
        Event::fake([SubareaStatusUpdated::class]);
        $sub = ParkSubarea::create(['park_area_id' => $this->area->park_area_id, 'park_subarea_name' => 'Old', 'park_subarea_polygon' => '[]', 'max_slots' => 5]);
        $response = $this->actingAs($this->admin)->putJson("/admin/park-subarea/{$sub->park_subarea_id}", [
            'name' => 'New',
            'polygon' => '[{"lat":-6.2,"lng":106.8},{"lat":-6.21,"lng":106.81},{"lat":-6.22,"lng":106.82}]',
        ]);
        $response->assertStatus(200)->assertJson(['status' => 'success']);
        $this->assertDatabaseHas('park_subareas', ['park_subarea_name' => 'New']);
    }

    #[Test]
    public function update_with_fewer_than_three_points_deletes_subarea()
    {
        $sub = ParkSubarea::create(['park_area_id' => $this->area->park_area_id, 'park_subarea_name' => 'Old', 'park_subarea_polygon' => '[]', 'max_slots' => 5]);
        $response = $this->actingAs($this->admin)->putJson("/admin/park-subarea/{$sub->park_subarea_id}", [
            'name' => 'Old',
            'polygon' => '[{"lat":-6.2,"lng":106.8},{"lat":-6.21,"lng":106.81}]',
        ]);
        $response->assertStatus(200)->assertJson(['status' => 'success', 'deleted' => true]);
        $this->assertDatabaseMissing('park_subareas', ['park_subarea_id' => $sub->park_subarea_id]);
    }

    #[Test]
    public function destroy_deletes_park_subarea()
    {
        $sub = ParkSubarea::create(['park_area_id' => $this->area->park_area_id, 'park_subarea_name' => 'Delete', 'park_subarea_polygon' => '[]']);
        $response = $this->actingAs($this->admin)->delete("/admin/park-subarea/{$sub->park_subarea_id}");
        $response->assertRedirect(route('admin.park-area.show', $this->area->park_area_id))->assertSessionHas('swal_success_crud');
        $this->assertDatabaseMissing('park_subareas', ['park_subarea_id' => $sub->park_subarea_id]);
    }
}
