<?php

namespace Tests\Feature\Models;

use App\Models\ParkArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ParkAreaTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function area_parkir_dapat_dibuat()
    {
        $area = ParkArea::create([
            'park_area_name' => 'Gedung A',
            'park_area_code' => 'G-A',
            'park_area_data' => []
        ]);

        $this->assertDatabaseHas('park_areas', [
            'park_area_name' => 'Gedung A',
            'park_area_code' => 'G-A'
        ]);
    }

    #[Test]
    public function area_parkir_dapat_diupdate()
    {
        $area = ParkArea::create([
            'park_area_name' => 'Old Name', 
            'park_area_code' => 'OLD',
            'park_area_data' => []
        ]);

        $area->update(['park_area_name' => 'New Name']);

        $this->assertDatabaseHas('park_areas', [
            'park_area_id' => $area->park_area_id,
            'park_area_name' => 'New Name'
        ]);
    }

    #[Test]
    public function area_parkir_dapat_dihapus()
    {
        $area = ParkArea::create([
            'park_area_name' => 'To Delete', 
            'park_area_code' => 'DEL',
            'park_area_data' => []
        ]);

        $area->delete();

        $this->assertDatabaseMissing('park_areas', ['park_area_id' => $area->park_area_id]);
    }
}
