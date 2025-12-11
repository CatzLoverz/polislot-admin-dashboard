<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MissionService
{
    /**
     * Entry Point Utama: Update progress misi user berdasarkan aksi (Metric Code).
     * Service ini akan mencari SEMUA misi aktif dengan metric code tersebut,
     * lalu memperbarui progress user untuk masing-masing mission_id secara independen.
     *
     * @param int $userId ID User yang melakukan aksi
     * @param string $metricCode Kode event (contoh: 'VALIDATION_STREAK', 'PROFILE_UPDATE')
     * @param int $incrementValue Jumlah penambahan progress (default 1)
     * @return void
     */
    public function updateProgress(int $userId, string $metricCode, int $incrementValue = 1)
    {
        try {
            // 1. Ambil SEMUA Misi yang COCOK dengan Metric Code & AKTIF
            // Kita ambil collection agar bisa di-loop, sehingga 2 misi dengan metric sama
            // bisa berjalan berbarengan (Parallel Progress).
            $missions = Mission::where('mission_metric_code', $metricCode)
                ->where('mission_is_active', true)
                ->get();

            if ($missions->isEmpty()) {
                return; // Tidak ada misi aktif untuk event ini
            }

            Log::info("[SERVICE MissionService@updateProgress] Triggered '{$metricCode}' for User {$userId}. Found {$missions->count()} active missions.");

            foreach ($missions as $mission) {
                $this->processMission($userId, $mission, $incrementValue);
            }

        } catch (Exception $e) {
            Log::error("[SERVICE MissionService@updateProgress] Global Error: " . $e->getMessage(), [
                'user_id' => $userId,
                'metric' => $metricCode
            ]);
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
            // 2. Ambil Tracker User dengan Lock (Pessimistic Locking)
            // Mencegah race condition jika user spam klik/aksi
            $userMission = UserMission::where('user_id', $userId)
                ->where('mission_id', $mission->mission_id)
                ->lockForUpdate()
                ->first();

            // Jika belum ada tracker, buat baru
            if (!$userMission) {
                $userMission = UserMission::create([
                    'user_id' => $userId,
                    'mission_id' => $mission->mission_id,
                    'user_mission_current_value' => 0,
                    'user_mission_is_completed' => false,
                    'user_mission_completed_at' => null
                ]);
            }

            // 3. CEK SIKLUS & RESET (Logic Cycle)
            // Apakah hari/minggu sudah berganti sesuai mission_reset_cycle?
            $this->checkAndResetCycle($userMission, $mission->mission_reset_cycle);

            // Jika status masih completed (artinya user sudah selesai di siklus ini) -> STOP.
            // Commit transaksi reset cycle (jika ada) dan return.
            if ($userMission->user_mission_is_completed) {
                DB::commit();
                return;
            }

            // 4. Update Progress Berdasarkan Tipe Misi
            $shouldCheckCompletion = false;

            if ($mission->mission_type === 'TARGET') {
                // --- LOGIC A: TARGET (Akumulasi Biasa) ---
                // Menggunakan nama kolom yang benar: user_mission_current_value
                $userMission->user_mission_current_value += $incrementValue;
                $shouldCheckCompletion = true;

            } elseif ($mission->mission_type === 'SEQUENCE') {
                // --- LOGIC B: SEQUENCE (Harian/Waktu) ---
                
                $lastAction = $userMission->updated_at; // Carbon instance
                
                // Kasus 1: Baru pertama kali main (Tracker baru dibuat / baru direset)
                if ($userMission->user_mission_current_value == 0) {
                    $userMission->user_mission_current_value = 1;
                    $shouldCheckCompletion = true;
                }
                // Kasus 2: User sudah melakukan aksi HARI INI (Anti-Spam)
                elseif ($lastAction->isToday()) {
                    // Abaikan. Streak/Hari tidak boleh nambah 2x sehari.
                    // Tidak perlu log info disini agar log tidak penuh spam.
                }
                // Kasus 3: User melakukan aksi KEMARIN (Streak Lanjut)
                elseif ($lastAction->isYesterday()) {
                    $userMission->user_mission_current_value += 1;
                    $shouldCheckCompletion = true;
                }
                // Kasus 4: User bolos > 1 hari
                else {
                    if ($mission->mission_is_consecutive) {
                        // Reset Streak ke 1 (Mulai dari awal hari ini karena putus)
                        $userMission->user_mission_current_value = 1;
                        Log::info("[SERVICE MissionService@processMission] Streak Broken: User {$userId} reset mission {$mission->mission_id} to 1.");
                    } else {
                        // Kalau tidak wajib berurut, lanjut akumulasi hari
                        $userMission->user_mission_current_value += 1;
                    }
                    $shouldCheckCompletion = true;
                }
            }

            // Simpan perubahan nilai progress
            // updated_at otomatis terupdate disini
            $userMission->save();

            // 5. Cek Kemenangan
            if ($shouldCheckCompletion) {
                // Refresh model untuk memastikan value terbaru
                $userMission->refresh(); 
                
                // Cek apakah Current Value >= Threshold
                if ($userMission->user_mission_current_value >= $mission->mission_threshold) {
                    // Tandai selesai
                    $userMission->user_mission_is_completed = true;
                    $userMission->user_mission_completed_at = now();
                    $userMission->save();

                    // BERI HADIAH (Auto Claim)
                    if ($mission->mission_points > 0) {
                        $this->awardPoints($userId, $mission->mission_points);
                    }

                    Log::info("[SERVICE MissionService@processMission] COMPLETED: Mission '{$mission->mission_title}' for User {$userId}");
                }
            }

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("[SERVICE MissionService@processMission] Error processing mission ID {$mission->mission_id}: " . $e->getMessage());
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
        // Jika tracker baru dibuat (wasRecentlyCreated), tidak perlu reset.
        if ($userMission->wasRecentlyCreated) return;

        $lastUpdate = $userMission->updated_at;
        $now = now();
        $shouldReset = false;

        switch ($cycle) {
            case 'DAILY':
                // Reset jika terakhir update BUKAN hari ini
                if (!$lastUpdate->isToday()) {
                    $shouldReset = true;
                }
                break;

            case 'WEEKLY':
                // Reset jika terakhir update BUKAN di minggu ini
                if (!$lastUpdate->isSameWeek($now)) {
                    $shouldReset = true;
                }
                break;

            case 'MONTHLY':
                // Reset jika terakhir update BUKAN di bulan ini
                if (!$lastUpdate->isSameMonth($now)) {
                    $shouldReset = true;
                }
                break;

            case 'NONE':
                // Tidak pernah reset (Sekali seumur hidup)
                $shouldReset = false;
                break;
        }

        if ($shouldReset) {
            // Reset Progress & Status agar user bisa main lagi
            $userMission->user_mission_current_value = 0;
            $userMission->user_mission_is_completed = false;
            $userMission->user_mission_completed_at = null;
            $userMission->save(); // updated_at berubah jadi NOW() di DB
            
            Log::info("[SERVICE MissionService@checkAndResetCycle] Cycle Reset Triggered", [
                'user_id' => $userMission->user_id,
                'mission_id' => $userMission->mission_id,
                'cycle' => $cycle
            ]);
        }
    }

    /**
     * Helper untuk memberikan poin ke user dengan locking.
     *
     * @param int $userId
     * @param int $points
     */
    private function awardPoints(int $userId, int $points)
    {
        $user = User::where('user_id', $userId)->lockForUpdate()->first();
        
        if ($user) {
            $user->increment('current_points', $points);
            $user->increment('lifetime_points', $points);
            // Log::info("Points awarded to User {$userId}: +{$points}");
        }
    }
}