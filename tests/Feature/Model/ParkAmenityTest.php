<?php

namespace Tests\Feature\Models;

use App\Models\ParkAmenity;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParkAmenityTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function amenities_dapat_dibuat()
    {
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $amenity = ParkAmenity::create([
            'park_subarea_id' => $sub->park_subarea_id, 
            'park_amenity_name' => 'CCTV'
        ]);

        $this->assertDatabaseHas('park_amenities', [
            'park_amenity_name' => 'CCTV'
        ]);
    }

    #[Test]
    public function amenities_dapat_dibaca()
    {
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        
        $amenity = ParkAmenity::create([
            'park_subarea_id' => $sub->park_subarea_id, 
            'park_amenity_name' => 'WiFi'
        ]);

        $found = ParkAmenity::find($amenity->park_amenity_id);
        
        $this->assertNotNull($found);
        $this->assertEquals('WiFi', $found->park_amenity_name);
    }

    #[Test]
    public function amenities_dapat_diupdate()
    {
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        
        $amenity = ParkAmenity::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'park_amenity_name' => 'Toilet'
        ]);
        $amenity->update(['park_amenity_name' => 'Restroom']);

        $this->assertDatabaseHas('park_amenities', [
            'park_amenity_name' => 'Restroom'
        ]);
    }

    #[Test]
    public function amenities_dapat_dihapus()
    {
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);

        $amenity = ParkAmenity::create([
            'park_subarea_id' => $sub->park_subarea_id,
            'park_amenity_name' => 'Musholla'
        ]);
        $amenity->delete();

        $this->assertDatabaseMissing('park_amenities', ['park_amenity_name' => 'Musholla']);
    }
}
