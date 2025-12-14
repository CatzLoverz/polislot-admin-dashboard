<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use App\Services\HistoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MissionService
{

    protected $historyService; // <--- 2. Property

    // 3. Inject via Constructor
    public function __construct(HistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Entry Point: Update progress misi user berdasarkan aksi.
     * Service ini mencari SEMUA misi aktif dengan metric code tersebut,
     * lalu memperbarui progress user untuk masing-masing mission_id secara independen.
     *
     * @param int $userId ID User yang melakukan aksi
     * @param string $metricCode Kode event (VALIDATION_ACTION, LOGIN_ACTION, PROFILE_UPDATE)
     * @param int $incrementValue Jumlah penambahan progress (default 1)
     * @return void
     */
    public function updateProgress(int $userId, string $metricCode, int $incrementValue = 1)
    {
        try {
            // Ambil SEMUA Misi yang COCOK dengan Metric Code & AKTIF
            $missions = Mission::where('mission_metric_code', $metricCode)
                ->where('mission_is_active', true)
                ->get();

            if ($missions->isEmpty()) {
                return;
            }

            Log::info("[SERVICE MissionService@updateProgress] Trigger: {$metricCode} for User {$userId}. Found: {$missions->count()} missions.");

            foreach ($missions as $mission) {
                $this->processMission($userId, $mission, $incrementValue);
            }

        } catch (Exception $e) {
            Log::error("[SERVICE MissionService@updateProgress] Global Error: " . $e->getMessage());
        }
    }

    /**
     * Memproses logika update progress untuk satu mission_id spesifik.
     * Menggunakan Transaction per misi agar isolasi logic terjaga.
     *
     * @param int $userId
     * @param Mission $mission
     * @param int $incrementValue
     */
    private function processMission(int $userId, Mission $mission, int $incrementValue)
    {
        DB::beginTransaction();
        try {
            // 1. Lock & Get Tracker
            $userMission = UserMission::where('user_id', $userId)
                ->where('mission_id', $mission->mission_id)
                ->lockForUpdate()
                ->first();

            if (!$userMission) {
                $userMission = UserMission::create([
                    'user_id' => $userId,
                    'mission_id' => $mission->mission_id,
                    'user_mission_current_value' => 0,
                    'user_mission_is_completed' => false
                ]);
            }

            // 2. Cek Siklus Reset
            $this->checkAndResetCycle($userMission, $mission->mission_reset_cycle);

            if ($userMission->user_mission_is_completed) {
                DB::commit(); return;
            }

            // 3. BRANCHING LOGIC BERDASARKAN TIPE
            $shouldCheck = false;

            // === TIPE TARGET (Akumulasi Biasa) ===
            if ($mission->mission_type === 'TARGET') {
                $userMission->user_mission_current_value += $incrementValue;
                $shouldCheck = true;
            } 
            
            // === TIPE SEQUENCE (Harian / Waktu) ===
            elseif ($mission->mission_type === 'SEQUENCE') {
                $lastAction = $userMission->updated_at;
                
                if ($userMission->user_mission_current_value == 0) {
                    $userMission->user_mission_current_value = 1;
                    $shouldCheck = true;
                } elseif ($lastAction->isToday()) {
                    // Anti-Spam: Sudah aksi hari ini
                } elseif ($lastAction->isYesterday()) {
                    // Kemarin aksi -> Lanjut streak
                    $userMission->user_mission_current_value += 1;
                    $shouldCheck = true;
                } else {
                    // Bolos > 1 hari
                    if ($mission->mission_is_consecutive) {
                        $userMission->user_mission_current_value = 1;
                        Log::info("[SERVICE MissionService@processMission] Streak Reset: User {$userId} Mission {$mission->mission_id}");
                    } else {
                        // Tidak wajib berurut -> Lanjut hitungan hari
                        $userMission->user_mission_current_value += 1;
                    }
                    $shouldCheck = true;
                }
            }

            $userMission->save();

            // 4. Cek Finish
            if ($shouldCheck) {
                $userMission->refresh(); 
                
                if ($userMission->user_mission_current_value >= $mission->mission_threshold) {
                    $userMission->user_mission_is_completed = true;
                    $userMission->user_mission_completed_at = now();
                    $userMission->save();

                    // Reward Auto Claim
                    if ($mission->mission_points > 0) {
                        $this->awardPoints($userId, $mission->mission_points);
                    }
                    // Log History
                    $this->historyService->log(
                        $userId,
                        'mission',
                        $mission->mission_title,
                        $mission->mission_points,
                        false
                    );

                    Log::info("[SERVICE MissionService@processMission] COMPLETED: {$mission->mission_title}");
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("[SERVICE MissionService@processMission] Error: " . $e->getMessage());
        }
    }

    /**
     * Logic Reset Siklus (Daily, Weekly, Monthly).
     * Jika waktu sudah lewat siklus, kembalikan progress ke 0 dan status completed false.
     *
     * @param UserMission $userMission
     * @param string $cycle ENUM: 'NONE', 'DAILY', 'WEEKLY', 'MONTHLY'
     */
    private function checkAndResetCycle(UserMission $userMission, string $cycle)
    {
        if ($userMission->wasRecentlyCreated) return;
        $lastUpdate = $userMission->updated_at;
        $shouldReset = false;

        switch ($cycle) {
            case 'DAILY': $shouldReset = !$lastUpdate->isToday(); break;
            case 'WEEKLY': $shouldReset = !$lastUpdate->isSameWeek(now()); break;
            case 'MONTHLY': $shouldReset = !$lastUpdate->isSameMonth(now()); break;
        }

        if ($shouldReset) {
            $userMission->user_mission_current_value = 0;
            $userMission->user_mission_is_completed = false;
            $userMission->user_mission_completed_at = null;
            $userMission->save();
        }
    }

    private function awardPoints(int $userId, int $points)
    {
        $user = User::where('user_id', $userId)->lockForUpdate()->first();
        if ($user) {
            $user->increment('current_points', $points);
            $user->increment('lifetime_points', $points);
        }
    }
}