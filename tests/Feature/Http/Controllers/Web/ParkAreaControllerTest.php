<?php

namespace Tests\Feature\Http\Controllers\Web;

use App\Models\User;
use App\Models\ParkArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParkAreaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->user = User::create([
            'name' => 'User Test',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'user',
            'email_verified_at' => now(),
        ]);
    }

    #[Test]
    public function it_can_display_park_area_index()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.park-area.index'));

        $response->assertStatus(200);
        $response->assertViewIs('Contents.ParkArea.index');
    }

    #[Test]
    public function it_can_fetch_park_area_datatable()
    {
        $this->actingAs($this->admin);

        ParkArea::create([
            'park_area_name' => 'Area 1',
            'park_area_code' => 'A1',
            'park_area_data' => []
        ]);

        $response = $this->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->getJson(route('admin.park-area.index'));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data',
            'recordsTotal',
            'recordsFiltered'
        ]);
    }

    #[Test]
    public function it_can_display_create_form()
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('admin.park-area.create'));

        $response->assertStatus(200);
        $response->assertViewIs('Contents.ParkArea.create');
    }

    #[Test]
    public function it_can_store_park_area()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.park-area.store'), [
            'park_area_name' => 'New Area',
            'park_area_code' => 'NA1',
            'center_lat' => -6.200000,
            'center_lng' => 106.816666,
            'zoom_level' => 15
        ]);

        $response->assertRedirect(route('admin.park-area.index'));
        $this->assertDatabaseHas('park_areas', [
            'park_area_code' => 'NA1'
        ]);
    }

    #[Test]
    public function it_cannot_store_park_area_with_invalid_data()
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('admin.park-area.store'), [
            'park_area_name' => '', // invalid
        ]);

        $response->assertSessionHasErrors(['park_area_name', 'park_area_code', 'center_lat', 'center_lng', 'zoom_level']);
    }

    #[Test]
    public function it_can_show_park_area()
    {
        $this->actingAs($this->admin);

        $area = ParkArea::create([
            'park_area_name' => 'Area 1',
            'park_area_code' => 'A1',
            'park_area_data' => []
        ]);

        $response = $this->get(route('admin.park-area.show', $area->park_area_id));

        $response->assertStatus(200);
        $response->assertViewIs('Contents.ParkArea.show');
        $response->assertViewHas('area');
    }

    #[Test]
    public function it_can_destroy_park_area()
    {
        $this->actingAs($this->admin);

        $area = ParkArea::create([
            'park_area_name' => 'Area Delete',
            'park_area_code' => 'AD',
            'park_area_data' => []
        ]);

        $response = $this->delete(route('admin.park-area.destroy', $area->park_area_id));

        $response->assertRedirect(route('admin.park-area.index'));
        $this->assertDatabaseMissing('park_areas', [
            'park_area_id' => $area->park_area_id
        ]);
    }

    #[Test]
    public function users_cannot_access_park_area_management()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('admin.park-area.index'));

        // Assuming role middleware blocks access (either 403 or redirect)
        $response->assertStatus(403);
    }
}
