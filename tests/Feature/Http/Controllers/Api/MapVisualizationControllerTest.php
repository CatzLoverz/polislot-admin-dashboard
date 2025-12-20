<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\ParkArea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MapVisualizationControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    #[Test]
    public function index_returns_200_and_data()
    {
        $user = User::factory()->create();
        ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);

        $this->actingAs($user);

        $response = $this->getJson('/api/map-visualization');

        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);
    }

    #[Test]
    public function show_returns_200_and_detail_data()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        
        $this->actingAs($user);
        
        $response = $this->getJson("/api/map-visualization/{$area->park_area_id}");
        
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success'])
                 ->assertJsonPath('data.area_name', 'A');
    }

    #[Test]
    public function show_returns_404_if_not_found()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        
        $response = $this->getJson("/api/map-visualization/9999");
        
        $response->assertStatus(404)
                 ->assertJson(['status' => 'error', 'message' => 'Area parkir tidak ditemukan.']);
    }
}
