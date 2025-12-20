<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\User;
use App\Models\Validation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserValidationControllerTest extends TestCase
{
    use RefreshDatabase;
    use \Illuminate\Foundation\Testing\WithoutMiddleware;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        
        $this->mock(\App\Services\MissionService::class, function ($mock) {
            $mock->shouldReceive('updateProgress')->andReturn(true);
        });

        $this->mock(\App\Services\HistoryService::class, function ($mock) {
            $mock->shouldReceive('log')->andReturn(true);
        });
    }

    #[Test]
    public function store_returns_201_when_successful()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        $val = Validation::create(['validation_points' => 10]);

        $this->actingAs($user);

        $response = $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh'
        ]);

        $response->assertStatus(201)
                 ->assertJson([
                     'status' => 'success',
                     'message' => 'Validasi berhasil! Anda mendapatkan 10 poin.'
                 ]);

        $this->assertDatabaseHas('user_validations', [
            'user_id' => $user->user_id,
            'user_validation_content' => 'penuh'
        ]);
    }

    #[Test]
    public function store_returns_422_if_payload_invalid()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/validation', []);
        
        $response->assertStatus(422)
                 ->assertJsonStructure(['message', 'errors']);
    }

    #[Test]
    public function store_returns_429_if_cooldown_active()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        $val = Validation::create(['validation_points' => 10]);
        
        $this->actingAs($user);

        // First validation
        $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh'
        ]);

        // Second validation (Too quick)
        $response = $this->postJson('/api/validation', [
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh'
        ]);

        $response->assertStatus(429)
                 ->assertJson(['status' => 'error']);
    }
}
