<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use App\Services\HistoryService;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MissionService
{
    protected $historyService;

    // 3. Inject via Constructor
    public function __construct(HistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Memeriksa dan mereset semua misi user yang siklusnya sudah kadaluwarsa.
     * Dipanggil saat user membuka halaman misi.
     *
     * @param int $userId
     * @return void
     */
    public function checkResetAllMissions(int $userId): void
    {
        $userMissions = UserMission::with('mission')
            ->where('user_id', $userId)
            ->get();

        foreach ($userMissions as $um) {
            if ($um->mission) {
                $this->checkAndResetCycle($um, $um->mission->mission_reset_cycle);
            }
        }
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
    public function updateProgress(int $userId, string $metricCode, int $incrementValue = 1): void
    {
        try {
            $missions = Mission::where('mission_metric_code', $metricCode)
                ->where('mission_is_active', true)
                ->get();

            if ($missions->isEmpty()) return;

            Log::info("Trigger: {$metricCode} for User {$userId}. Found: {$missions->count()} missions.");

            foreach ($missions as $mission) {
                $this->processMission($userId, $mission, $incrementValue);
            }

        } catch (Exception $e) {
            Log::error("Global Error: " . $e->getMessage());
        }
    }

    /**
     * Memproses logika update progress untuk satu mission_id spesifik.
     * Menggunakan Transaction per misi agar isolasi logic terjaga.
     *
     * @param int $userId
     * @param Mission $mission
     * @param int $incrementValue
     * @return void
     */
    private function processMission(int $userId, Mission $mission, int $incrementValue): void
    {
        DB::transaction(function () use ($userId, $mission, $incrementValue) {
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

            // 1. Snapshot Waktu Sebelum Diubah
            $lastActionAt = $userMission->updated_at ? $userMission->updated_at->copy() : null;

            // 2. CEK SIKLUS RESET
            // Mengembalikan TRUE jika reset terjadi (Ganti Hari/Minggu/Bulan)
            $isReset = $this->checkAndResetCycle($userMission, $mission->mission_reset_cycle);

            if ($userMission->user_mission_is_completed) {
                return;
            }

            // 3. LOGIC UPDATE PROGRESS
            $shouldCheck = false;

            if ($mission->mission_type === 'TARGET') {
                // Explicit Reset Handling untuk TARGET
                if ($isReset) {
                    // Jika baru saja direset (Ganti hari/minggu), mulai dari awal
                    $userMission->user_mission_current_value = $incrementValue;
                } else {
                    // Akumulasi normal
                    $userMission->user_mission_current_value += $incrementValue;
                }
                $shouldCheck = true;
            }
            elseif ($mission->mission_type === 'SEQUENCE') {
                // Tipe Sequence: Butuh penanganan khusus saat Reset Siklus
                
                if ($isReset) {
                    // KASUS A: Baru saja Reset Siklus (Misal: Senin Pagi)
                    // Maka Streak dipaksa mulai dari 1 lagi.
                    $userMission->user_mission_current_value = 1;
                    $shouldCheck = true;
                } 
                elseif ($userMission->user_mission_current_value == 0) {
                    // KASUS B: Baru pertama kali main
                    $userMission->user_mission_current_value = 1;
                    $shouldCheck = true;
                } 
                elseif ($lastActionAt && $lastActionAt->isToday()) {
                    // KASUS C: Anti-Spam (Sudah aksi hari ini, dan tidak ada reset)
                    // Skip
                } 
                elseif ($lastActionAt && $lastActionAt->isYesterday()) {
                    // KASUS D: Streak Lanjut (Aksi kemarin)
                    $userMission->user_mission_current_value += 1;
                    $shouldCheck = true;
                } 
                else {
                    // KASUS E: Bolos > 1 hari (Streak Putus di tengah minggu)
                    if ($mission->mission_is_consecutive) {
                        $userMission->user_mission_current_value = 1;
                        Log::info("Streak Reset: User {$userId} Mission {$mission->mission_id}");
                    } else {
                        $userMission->user_mission_current_value += 1; // Akumulasi Hari (Tidak Wajib Urut)
                    }
                    $shouldCheck = true;
                }
            }

            $userMission->save();

            // 4. CEK FINISH
            if ($shouldCheck) {
                $userMission->refresh(); 
                if ($userMission->user_mission_current_value >= $mission->mission_threshold) {
                    $userMission->user_mission_is_completed = true;
                    $userMission->user_mission_completed_at = now();
                    $userMission->save();

                    if ($mission->mission_points > 0) {
                        $this->awardPoints($userId, $mission->mission_points);
                    }
                    
                    $this->historyService->log(
                        $userId,
                        'mission',
                        $mission->mission_title,
                        $mission->mission_points,
                        false
                    );

                    Log::info("COMPLETED: {$mission->mission_title}");
                }
            }
        });
    }

    /**
     * Logic Reset Siklus (Daily, Weekly, Monthly).
     * Jika waktu sudah lewat siklus, kembalikan progress ke 0 dan status completed false.
     *
     * @param UserMission $userMission
     * @param string $cycle ENUM: 'NONE', 'DAILY', 'WEEKLY', 'MONTHLY'
     * @return bool
     */
    private function checkAndResetCycle(UserMission $userMission, string $cycle): bool
    {
        if ($userMission->wasRecentlyCreated) return false;
        
        $lastUpdate = $userMission->updated_at;
        $shouldReset = false;

        switch ($cycle) {
            case 'DAILY': $shouldReset = !$lastUpdate->isToday(); break;
            case 'WEEKLY': $shouldReset = !$lastUpdate->isSameWeek(now()); break;
            case 'MONTHLY': $shouldReset = !$lastUpdate->isSameMonth(now()); break;
        }

        if ($shouldReset) {
            DB::transaction(function () use ($userMission) {
                $userMission->user_mission_current_value = 0;
                $userMission->user_mission_is_completed = false;
                $userMission->user_mission_completed_at = null;
                $userMission->save(); // updated_at berubah jadi NOW
            });
            return true; // Reset Terjadi
        }

        return false; // Tidak ada Reset
    }

    /**
     * Memberikan poin kepada user atas misi yang diselesaikan.
     *
     * @param int $userId
     * @param int $points
     * @return void
     */
    private function awardPoints(int $userId, int $points): void
    {
        DB::transaction(function () use ($userId, $points) {
            $user = User::where('user_id', $userId)->lockForUpdate()->first();
            if ($user) {
                $user->increment('current_points', $points);
                $user->increment('lifetime_points', $points);
            }
        });
    }
}