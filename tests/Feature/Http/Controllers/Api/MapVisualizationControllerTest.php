<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\User;
use App\Models\UserValidation;
use App\Models\Validation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MapVisualizationControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithoutMiddleware;

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
        $subarea = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => []]);

        // Buat records untuk mencegah foreign key error
        Validation::create(['validation_name' => 'Banyak', 'validation_slug' => 'banyak', 'validation_points' => 10]);

        // Buat user validation 5 menit lalu agar masuk logika cooldown
        UserValidation::create([
            'user_id' => $user->user_id,
            'validation_id' => 1,
            'park_subarea_id' => $subarea->park_subarea_id,
            'user_validation_content' => 'banyak',
            'created_at' => now()->subMinutes(5),
        ]);

        $this->actingAs($user);

        $response = $this->getJson("/api/map-visualization/{$area->park_area_id}");

        $response->assertStatus(200)
            ->assertJson(['status' => 'success'])
            ->assertJsonPath('data.area_name', 'A')
            ->assertJsonPath('data.validation_cooldown.can_validate', false);
    }

    #[Test]
    public function show_returns_404_if_not_found()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->getJson('/api/map-visualization/9999');

        $response->assertStatus(404)
            ->assertJson(['status' => 'error', 'message' => 'Area parkir tidak ditemukan.']);
    }
}
