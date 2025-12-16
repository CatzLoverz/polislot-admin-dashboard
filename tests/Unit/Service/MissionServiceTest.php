<?php

namespace Tests\Unit\Services;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use App\Models\UserHistory;
use App\Services\HistoryService;
use App\Services\MissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MissionServiceTest extends TestCase
{
    use RefreshDatabase;

    protected $missionService;

    protected function setUp(): void
    {
        parent::setUp();
        
        // MENGGUNAKAN REAL OBJECT, BUKAN MOCK
        $historyService = new HistoryService(); 
        $this->missionService = new MissionService($historyService);
    }

    // =========================================================================
    // 🟡 TEST MISI TIPE: TARGET
    // =========================================================================

    #[Test]
    public function misi_target_selesai_dan_mencatat_history()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create(['current_points' => 0]);

        $mission = Mission::create([
            'mission_title' => 'Validator Handal',
            'mission_metric_code' => 'VALIDATION_ACTION',
            'mission_type' => 'TARGET',
            'mission_threshold' => 5,
            'mission_points' => 100,
            'mission_is_active' => true,
            'mission_reset_cycle' => 'NONE'
        ]);

        // Progress 1: Tambah 2
        $this->missionService->updateProgress($user->user_id, 'VALIDATION_ACTION', 2);
        
        // Belum selesai, belum ada history
        $this->assertDatabaseMissing('user_histories', [
            'user_id' => $user->user_id,
            'user_history_name' => 'Validator Handal'
        ]);

        // Progress 2: Tambah 3 (Total 5) -> Selesai
        $this->missionService->updateProgress($user->user_id, 'VALIDATION_ACTION', 3);
        
        // Assert History Terbuat (Integrasi HistoryService)
        $this->assertDatabaseHas('user_histories', [
            'user_id' => $user->user_id,
            'user_history_type' => 'mission',
            'user_history_name' => 'Validator Handal',
            'user_history_points' => 100
        ]);

        // Assert Poin User Bertambah
        $user->refresh();
        $this->assertEquals(100, $user->current_points);
    }

    // =========================================================================
    // 🟡 TEST MISI TIPE: SEQUENCE
    // =========================================================================

    #[Test]
    public function misi_sequence_login_bertambah_dan_reset_manual_di_db()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $mission = Mission::create([
            'mission_title' => 'Login Streak',
            'mission_metric_code' => 'LOGIN_ACTION',
            'mission_type' => 'SEQUENCE',
            'mission_threshold' => 3,
            'mission_points' => 50,
            'mission_is_active' => true,
            'mission_reset_cycle' => 'NONE',
            'mission_is_consecutive' => true
        ]);

        // Hari 1
        $this->missionService->updateProgress($user->user_id, 'LOGIN_ACTION');

        // Manipulasi: Paksa updated_at menjadi Kemarin di DB agar logic "isToday()" false
        UserMission::where('user_id', $user->user_id)
            ->where('mission_id', $mission->mission_id)
            ->update(['updated_at' => now()->subDay()]);

        // Hari 2
        $this->missionService->updateProgress($user->user_id, 'LOGIN_ACTION');

        $this->assertDatabaseHas('user_missions', [
            'user_id' => $user->user_id,
            'mission_id' => $mission->mission_id,
            'user_mission_current_value' => 2
        ]);
    }

    #[Test]
    public function misi_sequence_consecutive_reset_jika_bolos()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $mission = Mission::create([
            'mission_title' => 'Rajin Login',
            'mission_metric_code' => 'LOGIN_ACTION',
            'mission_type' => 'SEQUENCE',
            'mission_threshold' => 7,
            'mission_is_active' => true,
            'mission_is_consecutive' => true 
        ]);

        // Login
        $this->missionService->updateProgress($user->user_id, 'LOGIN_ACTION');

        // Manipulasi: Set waktu terakhir login menjadi 2 hari yang lalu (Bolos kemarin)
        UserMission::where('user_id', $user->user_id)
            ->where('mission_id', $mission->mission_id)
            ->update(['updated_at' => now()->subDays(2)]);

        // Login Hari Ini -> Harusnya Reset ke 1
        $this->missionService->updateProgress($user->user_id, 'LOGIN_ACTION');

        $this->assertDatabaseHas('user_missions', [
            'user_id' => $user->user_id,
            'mission_id' => $mission->mission_id,
            'user_mission_current_value' => 1
        ]);
    }

    // =========================================================================
    // 🟡 TEST RESET SIKLUS (Daily)
    // =========================================================================

    #[Test]
    public function misi_daily_reset_setelah_ganti_hari()
    {
        /** @var \App\Models\User $user */
        $user = User::factory()->create();

        $mission = Mission::create([
            'mission_title' => 'Harian Validasi',
            'mission_metric_code' => 'VALIDATION_ACTION',
            'mission_type' => 'TARGET',
            'mission_threshold' => 10,
            'mission_is_active' => true,
            'mission_reset_cycle' => 'DAILY'
        ]);

        // Progress kemarin
        $this->missionService->updateProgress($user->user_id, 'VALIDATION_ACTION', 5);

        // Manipulasi: Paksa updated_at menjadi Kemarin
        UserMission::where('user_id', $user->user_id)
            ->where('mission_id', $mission->mission_id)
            ->update(['updated_at' => now()->subDay()]);

        // Aksi Hari Ini -> Reset jadi 0, lalu tambah 1 = 1
        $this->missionService->updateProgress($user->user_id, 'VALIDATION_ACTION', 1);

        $this->assertDatabaseHas('user_missions', [
            'user_id' => $user->user_id,
            'mission_id' => $mission->mission_id,
            'user_mission_current_value' => 1
        ]);
    }
}