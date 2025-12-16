<?php

namespace Tests\Feature\Models;

use App\Models\Mission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MissionTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function mission_dapat_ditambahkan()
    {
        $data = [
            'mission_title' => 'Misi Harian',
            'mission_description' => 'Login setiap hari',
            'mission_points' => 50,
            'mission_type' => 'SEQUENCE',
            'mission_reset_cycle' => 'DAILY',
            'mission_metric_code' => 'LOGIN_ACTION',
            'mission_threshold' => 1,
            'mission_is_consecutive' => true,
            'mission_is_active' => true,
        ];

        $mission = Mission::create($data);

        $this->assertDatabaseHas('missions', [
            'mission_title' => 'Misi Harian',
            'mission_metric_code' => 'LOGIN_ACTION'
        ]);
    }

    #[Test]
    public function mission_dapat_diubah()
    {
        $mission = Mission::create([
            'mission_title' => 'Judul Lama',
            'mission_type' => 'TARGET',
            'mission_metric_code' => 'VALIDATION_ACTION', // Ganti 'TEST' dengan Valid Enum
            'mission_threshold' => 10,
            'mission_is_active' => true, // Tambahkan default
            'mission_reset_cycle' => 'NONE' // Tambahkan default
        ]);

        $mission->update(['mission_title' => 'Judul Baru']);

        $this->assertDatabaseHas('missions', [
            'mission_id' => $mission->mission_id,
            'mission_title' => 'Judul Baru'
        ]);
    }

    #[Test]
    public function mission_dapat_dihapus()
    {
        $mission = Mission::create([
            'mission_title' => 'Hapus Saya',
            'mission_type' => 'TARGET',
            'mission_metric_code' => 'VALIDATION_ACTION', // Ganti 'TEST' dengan Valid Enum
            'mission_threshold' => 10,
            'mission_is_active' => true,
            'mission_reset_cycle' => 'NONE'
        ]);

        $mission->delete();

        $this->assertDatabaseMissing('missions', [
            'mission_id' => $mission->mission_id
        ]);
    }
}