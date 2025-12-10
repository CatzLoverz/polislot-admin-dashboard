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
     * Service ini akan mencari semua misi aktif dengan metric code tersebut,
     * lalu memperbarui progress user sesuai aturan (Target/Sequence) dan Siklus Reset.
     *
     * @param int $userId ID User yang melakukan aksi
     * @param string $metricCode Kode event (contoh: 'VALIDATION_STREAK', 'PROFILE_UPDATE')
     * @param int $incrementValue Jumlah penambahan progress (default 1)
     * @return void
     */
    public function updateProgress(int $userId, string $metricCode, int $incrementValue = 1)
    {
        try {
            $missions = Mission::where('mission_metric_code', $metricCode)
                ->where('mission_is_active', true)
                ->get();

            if ($missions->isEmpty()) return;

            foreach ($missions as $mission) {
                $this->processMission($userId, $mission, $incrementValue);
            }

        } catch (Exception $e) {
            Log::error("[MissionService] Global Error: " . $e->getMessage());
        }
    }

    /**
     * Memproses logika update progress untuk satu misi spesifik.
     *
     * @param int $userId
     * @param Mission $mission
     * @param int $incrementValue
     */
    private function processMission($userId, $mission, $incrementValue)
    {
        // 1. GUNAKAN TRANSACTION DARI AWAL
        DB::beginTransaction();
        try {
            // Lock baris ini agar tidak ada race condition (progress nambah barengan)
            // firstOrCreate tidak support lockForUpdate langsung, jadi kita split logicnya.
            
            $userMission = UserMission::where('user_id', $userId)
                ->where('mission_id', $mission->mission_id)
                ->lockForUpdate() // KUNCI DATANYA
                ->first();

            if (!$userMission) {
                // Jika belum ada, buat baru
                $userMission = UserMission::create([
                    'user_id' => $userId,
                    'mission_id' => $mission->mission_id,
                    'current_value' => 0,
                    'is_completed' => false
                ]);
            }

            // 2. CEK SIKLUS (Reset jika perlu)
            $this->checkAndResetCycle($userMission, $mission->mission_reset_cycle);

            // Jika sudah completed, stop & commit yang sudah ada (reset cycle tadi)
            if ($userMission->is_completed) {
                DB::commit(); 
                return;
            }

            // 3. HITUNG LOGIC PROGRESS
            // Kita tidak pakai increment() langsung agar nilai di memori sinkron dengan DB untuk pengecekan
            
            if ($mission->mission_type === 'TARGET') {
                $userMission->current_value += $incrementValue;

            } elseif ($mission->mission_type === 'SEQUENCE') {
                $lastAction = $userMission->updated_at;
                
                if ($userMission->current_value == 0) {
                    $userMission->current_value = 1;
                } elseif ($lastAction->isToday()) {
                    // Skip, sudah aksi hari ini
                } elseif ($lastAction->isYesterday()) {
                    $userMission->current_value += 1;
                } else {
                    // Reset streak logic
                    if ($mission->mission_is_consecutive) {
                        $userMission->current_value = 1; 
                        Log::info("Streak reset user $userId");
                    } else {
                        $userMission->current_value += 1;
                    }
                }
            }

            // Simpan perubahan nilai progress
            $userMission->save();

            // 4. CEK KEMENANGAN (Threshold)
            if ($userMission->current_value >= $mission->mission_threshold) {
                
                // Update status selesai
                $userMission->is_completed = true;
                $userMission->completed_at = now();
                $userMission->save();

                // BERI HADIAH (Auto Claim)
                if ($mission->mission_points > 0) {
                    $user = User::where('user_id', $userId)->lockForUpdate()->first();
                    
                    if ($user) {
                        // Pastikan kolom ini benar-benar ada di tabel users Anda!
                        $user->current_points += $mission->mission_points;
                        $user->lifetime_points += $mission->mission_points;
                        $user->save();
                    } else {
                        throw new Exception("User ID $userId tidak ditemukan saat giving reward.");
                    }
                }

                Log::info("[MissionService] COMPLETED: Mission {$mission->mission_title} for User {$userId}");
            }

            DB::commit(); // SIMPAN PERUBAHAN PERMANEN

        } catch (Exception $e) {
            DB::rollBack(); // BATALKAN SEMUA JIKA ERROR
            Log::error("[MissionService] Transaction Failed: " . $e->getMessage());
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
            $userMission->current_value = 0;
            $userMission->is_completed = false;
            $userMission->completed_at = null;
            $userMission->save(); // updated_at berubah jadi NOW()
            
            Log::info("[MissionService] Cycle Reset Triggered", [
                'user_id' => $userMission->user_id,
                'mission_id' => $userMission->mission_id,
                'cycle' => $cycle
            ]);
        }
    }

    /**
     * Menandai misi selesai dan memberikan Poin (Auto Claim).
     * Menggunakan Database Transaction untuk integritas data.
     *
     * @param UserMission $userMission
     * @param Mission $mission
     */
    private function completeAndReward(UserMission $userMission, Mission $mission)
    {
        DB::beginTransaction();
        try {
            // 1. Tandai Selesai
            $userMission->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            // 2. Auto-Claim Poin (Langsung masuk ke User)
            if ($mission->mission_points > 0) {
                $user = User::lockForUpdate()->find($userMission->user_id); // Lock baris user mencegah race condition
                
                if ($user) {
                    $user->increment('current_points', $mission->mission_points);
                    $user->increment('lifetime_points', $mission->mission_points);
                }
            }

            Log::info("[MissionService] MISSION COMPLETED", [
                'user_id' => $userMission->user_id,
                'mission' => $mission->mission_title,
                'points_awarded' => $mission->mission_points
            ]);

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("[MissionService] Failed to award points: " . $e->getMessage());
            // Kita biarkan tracker tetap belum selesai agar user bisa coba lagi/sistem retry nanti
        }
    }
}