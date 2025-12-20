<?php

namespace Tests\Feature\Models;

use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\User;
use App\Models\UserValidation;
use App\Models\Validation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserValidationTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_validation_dapat_dibuat()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        $valSetting = Validation::create(['validation_points' => 10]);

        $validation = UserValidation::create([
            'user_id' => $user->user_id,
            'validation_id' => $valSetting->validation_id,
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh',
        ]);

        $this->assertDatabaseHas('user_validations', [
            'user_validation_content' => 'penuh',
            'user_id' => $user->user_id,
        ]);

        $this->assertEquals($validation->park_subarea_id, $sub->park_subarea_id);
    }


    #[Test]
    public function user_validation_dapat_dibaca()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        $valSetting = Validation::create(['validation_points' => 10]);

        $uv = UserValidation::create([
            'user_id' => $user->user_id,
            'validation_id' => $valSetting->validation_id,
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh'
        ]);

        $found = UserValidation::find($uv->user_validation_id);
        $this->assertNotNull($found);
        $this->assertEquals('penuh', $found->user_validation_content);
    }

    #[Test]
    public function user_validation_dapat_diupdate()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        $valSetting = Validation::create(['validation_points' => 10]);

        $uv = UserValidation::create([
            'user_id' => $user->user_id,
            'validation_id' => $valSetting->validation_id,
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh'
        ]);

        $uv->update(['user_validation_content' => 'terbatas']);

        $this->assertDatabaseHas('user_validations', [
            'user_validation_id' => $uv->user_validation_id,
            'user_validation_content' => 'terbatas'
        ]);
    }

    #[Test]
    public function user_validation_dapat_dihapus()
    {
        $user = User::factory()->create();
        $area = ParkArea::create(['park_area_name' => 'A', 'park_area_code' => 'A', 'park_area_data' => []]);
        $sub = ParkSubarea::create(['park_area_id' => $area->park_area_id, 'park_subarea_name' => 'S1', 'park_subarea_polygon' => '[]']);
        $valSetting = Validation::create(['validation_points' => 10]);

        $uv = UserValidation::create([
            'user_id' => $user->user_id,
            'validation_id' => $valSetting->validation_id,
            'park_subarea_id' => $sub->park_subarea_id,
            'user_validation_content' => 'penuh'
        ]);

        $uv->delete();

        $this->assertDatabaseMissing('user_validations', ['user_validation_id' => $uv->user_validation_id]);
    }
}
