<?php

namespace Tests\Feature\Models;

use App\Models\ParkArea;
use App\Models\ParkSubarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParkSubareaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function subarea_dapat_dibuat_dengan_relasi_area()
    {
        $area = ParkArea::create([
            'park_area_name' => 'Area 1', 
            'park_area_code' => 'A1',
            'park_area_data' => []
        ]);

        $subarea = ParkSubarea::create([
            'park_area_id' => $area->park_area_id,
            'park_subarea_name' => 'Slot A1',
            'park_subarea_polygon' => '[[0,0],[1,1]]'
        ]);

        $this->assertDatabaseHas('park_subareas', [
            'park_subarea_name' => 'Slot A1',
            'park_area_id' => $area->park_area_id
        ]);
        
        $this->assertEquals($area->park_area_id, $subarea->parkArea->park_area_id);
    }

    #[Test]
    public function subarea_dapat_diupdate()
    {
        $area = ParkArea::create([
            'park_area_name' => 'Area 1', 
            'park_area_code' => 'A1',
            'park_area_data' => []
        ]);
        $subarea = ParkSubarea::create([
            'park_area_id' => $area->park_area_id,
            'park_subarea_name' => 'Old Slot',
            'park_subarea_polygon' => '...'
        ]);

        $subarea->update(['park_subarea_name' => 'New Slot']);

        $this->assertDatabaseHas('park_subareas', [
            'park_subarea_id' => $subarea->park_subarea_id,
            'park_subarea_name' => 'New Slot'
        ]);
    }

    #[Test]
    public function subarea_dapat_dibaca()
    {
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $found = ParkSubarea::find($sub->park_subarea_id);
        $this->assertNotNull($found);
        $this->assertEquals('S1', $found->park_subarea_name);
    }

    #[Test]
    public function subarea_dapat_dihapus()
    {
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'Delete Me', 'park_subarea_polygon' => '[]']);

        $sub->delete();

        $this->assertDatabaseMissing('park_subareas', ['park_subarea_id' => $sub->park_subarea_id]);
    }
}
