<?php

namespace Tests\Feature\Models;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserMissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function user_mission_dapat_dibuat_dan_berelasi()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        
        $mission = Mission::create([
            'mission_title' => 'Test Mission',
            'mission_type' => 'TARGET',
            'mission_metric_code' => 'VALIDATION_ACTION', // Valid Enum
            'mission_threshold' => 10,
            'mission_is_active' => true,
            'mission_reset_cycle' => 'NONE'
        ]);

        $userMission = UserMission::create([
            'user_id' => $user->user_id,
            'mission_id' => $mission->mission_id,
            'user_mission_current_value' => 5,
            'user_mission_is_completed' => false,
        ]);

        $this->assertDatabaseHas('user_missions', [
            'user_id' => $user->user_id,
            'mission_id' => $mission->mission_id,
            'user_mission_current_value' => 5
        ]);

        // Test Relasi
        $this->assertEquals($user->user_id, $userMission->user->user_id);
        $this->assertEquals($mission->mission_id, $userMission->mission->mission_id);
    }

    #[Test]
    public function user_mission_dapat_diupdate()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        
        // Gunakan data valid agar tidak error Truncated
        $mission = Mission::create([
            'mission_title' => 'Valid Title',
            'mission_metric_code' => 'VALIDATION_ACTION', 
            'mission_type' => 'TARGET', 
            'mission_threshold' => 1,
            'mission_is_active' => true,
            'mission_reset_cycle' => 'NONE'
        ]);

        $userMission = UserMission::create([
            'user_id' => $user->user_id,
            'mission_id' => $mission->mission_id,
            'user_mission_current_value' => 0,
        ]);

        $userMission->update([
            'user_mission_current_value' => 10, 
            'user_mission_is_completed' => true
        ]);

        $this->assertDatabaseHas('user_missions', [
            'user_mission_id' => $userMission->user_mission_id,
            'user_mission_current_value' => 10,
            'user_mission_is_completed' => true
        ]);
    }

    #[Test]
    public function user_mission_dapat_dihapus()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();
        
        $mission = Mission::create([
            'mission_title' => 'Valid Title',
            'mission_metric_code' => 'VALIDATION_ACTION', 
            'mission_type' => 'TARGET', 
            'mission_threshold' => 1,
            'mission_is_active' => true,
            'mission_reset_cycle' => 'NONE'
        ]);

        $userMission = UserMission::create([
            'user_id' => $user->user_id,
            'mission_id' => $mission->mission_id,
        ]);

        $userMission->delete();

        $this->assertDatabaseMissing('user_missions', [
            'user_mission_id' => $userMission->user_mission_id
        ]);
    }
}