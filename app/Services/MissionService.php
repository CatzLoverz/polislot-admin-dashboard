<?php

namespace App\Services;

use App\Models\Mission;
use App\Models\UserMission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class MissionService
{
    protected $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * Update progress mission untuk user berdasarkan tipe mission.
     * 
     * @param int $userId
     * @param string $missionType (e.g., 'FEEDBACK_SUBMIT', 'DAILY_LOGIN')
     * @param int $incrementValue
     * @return void
     */
    public function updateMissionProgress($userId, $missionType, $incrementValue = 1)
    {
        try {
            Log::info('[MissionService] Update mission progress', [
                'user_id' => $userId,
                'mission_type' => $missionType,
                'increment' => $incrementValue
            ]);

            // Ambil semua mission aktif dengan tipe yang sesuai
            $missions = Mission::active()
                ->ofType($missionType)
                ->get();

            if ($missions->isEmpty()) {
                Log::info('[MissionService] Tidak ada mission aktif untuk tipe ini', [
                    'mission_type' => $missionType
                ]);
                return;
            }

            foreach ($missions as $mission) {
                // Cek apakah perlu reset (untuk daily/weekly)
                $this->checkAndResetProgress($userId, $mission);

                // Ambil atau buat progress user
                $userMission = UserMission::where('user_id', $userId)
                    ->where('mission_id', $mission->mission_id)
                    ->first();

                if (!$userMission) {
                    // Buat progress baru
                    $userMission = UserMission::create([
                        'user_id' => $userId,
                        'mission_id' => $mission->mission_id,
                        'progress_value' => $incrementValue,
                        'streak_count' => $missionType === 'DAILY_LOGIN' ? 1 : 0,
                        'last_completed_date' => now()->toDateString(),
                        'is_completed' => false,
                        'is_claimed' => false,
                    ]);

                    $currentProgress = $incrementValue;
                } elseif (!$userMission->is_completed || !$userMission->is_claimed) {
                    // Update progress yang sudah ada
                    $newProgress = $userMission->progress_value + $incrementValue;
                    
                    // Update streak untuk daily login
                    $newStreakCount = $userMission->streak_count;
                    if ($missionType === 'DAILY_LOGIN') {
                        $lastDate = $userMission->last_completed_date 
                            ? Carbon::parse($userMission->last_completed_date) 
                            : null;
                        
                        if ($lastDate && $lastDate->isYesterday()) {
                            // Lanjutkan streak
                            $newStreakCount = $userMission->streak_count + 1;
                        } elseif (!$lastDate || !$lastDate->isToday()) {
                            // Reset streak jika terputus
                            $newStreakCount = 1;
                        }
                        // Jika hari ini, jangan update streak (sudah login hari ini)
                    }
                    
                    $userMission->update([
                        'progress_value' => $newProgress,
                        'streak_count' => $newStreakCount,
                        'last_completed_date' => now()->toDateString(),
                    ]);

                    $currentProgress = $newProgress;
                } else {
                    // Sudah completed dan claimed, skip
                    continue;
                }

                Log::info('[MissionService] Progress updated', [
                    'user_id' => $userId,
                    'mission_id' => $mission->mission_id,
                    'current_progress' => $currentProgress,
                    'target' => $mission->target_value
                ]);

                // Cek apakah mission completed
                if ($currentProgress >= $mission->target_value) {
                    $this->completeMission($userId, $mission);
                }
            }

        } catch (Exception $e) {
            Log::error('[MissionService] Error update mission progress', [
                'user_id' => $userId,
                'mission_type' => $missionType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Tandai mission sebagai completed (belum claim reward).
     * 
     * @param int $userId
     * @param Mission $mission
     * @return bool
     */
    private function completeMission($userId, $mission)
    {
        try {
            // Update status completed
            UserMission::where('user_id', $userId)
                ->where('mission_id', $mission->mission_id)
                ->update([
                    'is_completed' => true,
                    'completed_at' => now(),
                ]);

            Log::info('[MissionService] Mission completed! (Belum diklaim)', [
                'user_id' => $userId,
                'mission_id' => $mission->mission_id,
                'mission_name' => $mission->mission_name,
            ]);

            return true;

        } catch (Exception $e) {
            Log::error('[MissionService] Error completing mission', [
                'user_id' => $userId,
                'mission_id' => $mission->mission_id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Claim reward mission (user manual claim).
     * 
     * @param int $userId
     * @param int $missionId
     * @return array
     */
    public function claimMissionReward($userId, $missionId)
    {
        try {
            DB::beginTransaction();

            // Ambil user mission
            $userMission = UserMission::where('user_id', $userId)
                ->where('mission_id', $missionId)
                ->first();

            if (!$userMission) {
                return [
                    'success' => false,
                    'message' => 'Mission tidak ditemukan.'
                ];
            }

            if (!$userMission->is_completed) {
                return [
                    'success' => false,
                    'message' => 'Mission belum selesai.'
                ];
            }

            if ($userMission->is_claimed) {
                return [
                    'success' => false,
                    'message' => 'Reward sudah diklaim.'
                ];
            }

            // Ambil data mission
            $mission = Mission::find($missionId);

            if (!$mission) {
                return [
                    'success' => false,
                    'message' => 'Mission tidak ditemukan.'
                ];
            }

            // Berikan reward poin
            $pointResult = null;
            if ($mission->reward_points > 0) {
                $pointResult = $this->pointService->addCustomPoints(
                    $userId,
                    'MISSION_COMPLETED',
                    $mission->reward_points,
                    "Menyelesaikan mission: {$mission->mission_name}"
                );

                if (!$pointResult) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'message' => 'Gagal memberikan reward poin.'
                    ];
                }
            }

            // Update status claimed
            $userMission->update([
                'is_claimed' => true,
                'claimed_at' => now(),
            ]);

            // Log ke activity_logs
            $this->logMissionClaimed($userId, $mission, $mission->reward_points);

            DB::commit();

            Log::info('[MissionService] Mission reward claimed!', [
                'user_id' => $userId,
                'mission_id' => $mission->mission_id,
                'reward_points' => $mission->reward_points
            ]);

            return [
                'success' => true,
                'message' => "Selamat! Kamu mendapatkan {$mission->reward_points} poin dari mission: {$mission->mission_name}",
                'points_earned' => $mission->reward_points,
                'tier_upgraded' => $pointResult['tier_upgraded'] ?? false,
                'new_tier' => $pointResult['new_tier'] ?? null,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[MissionService] Error claiming mission reward', [
                'user_id' => $userId,
                'mission_id' => $missionId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan saat claim reward.'
            ];
        }
    }

    /**
     * Cek dan reset progress jika sudah melewati period (daily/weekly).
     * 
     * @param int $userId
     * @param Mission $mission
     * @return void
     */
    private function checkAndResetProgress($userId, $mission)
    {
        $userMission = UserMission::where('user_id', $userId)
            ->where('mission_id', $mission->mission_id)
            ->first();

        if (!$userMission) {
            return; // Belum ada progress
        }

        $needReset = false;
        $lastDate = $userMission->last_completed_date 
            ? Carbon::parse($userMission->last_completed_date) 
            : null;

        if ($mission->period_type === 'daily') {
            // Reset setiap hari di reset_time
            $todayResetTime = Carbon::today()->setTimeFromTimeString($mission->reset_time);
            
            // Jika sudah lewat hari dan melewati reset_time
            if ($lastDate && $lastDate->lt(Carbon::today()) && now()->gte($todayResetTime)) {
                $needReset = true;
            }
        } elseif ($mission->period_type === 'weekly') {
            // Reset setiap minggu di hari Senin reset_time
            $weeklyResetTime = Carbon::now()->startOfWeek()->setTimeFromTimeString($mission->reset_time);
            
            // Jika last_completed_date sebelum awal minggu ini
            if ($lastDate && $lastDate->lt(Carbon::now()->startOfWeek())) {
                $needReset = true;
            }
        }
        // one_time tidak perlu reset

        if ($needReset) {
            $userMission->update([
                'progress_value' => 0,
                'streak_count' => 0,
                'is_completed' => false,
                'is_claimed' => false,
                'completed_at' => null,
                'claimed_at' => null,
                'last_completed_date' => null,
            ]);

            Log::info('[MissionService] Progress reset', [
                'user_id' => $userId,
                'mission_id' => $mission->mission_id,
                'period_type' => $mission->period_type
            ]);
        }
    }

    /**
     * Log mission claimed ke activity_logs.
     * 
     * @param int $userId
     * @param Mission $mission
     * @param int $rewardPoints
     * @return void
     */
    private function logMissionClaimed($userId, $mission, $rewardPoints)
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'activity_type' => 'MISSION_COMPLETED',
                'points_awarded' => $rewardPoints,
                'description' => "Menyelesaikan mission: {$mission->mission_name}",
                'metadata' => json_encode([
                    'mission_id' => $mission->mission_id,
                    'mission_name' => $mission->mission_name,
                    'mission_type' => $mission->mission_type,
                    'target_value' => $mission->target_value,
                    'reward_points' => $rewardPoints,
                    'period_type' => $mission->period_type,
                    'claimed_at' => now()->toDateTimeString(),
                ]),
                'created_at' => now(),
            ]);
        } catch (Exception $e) {
            Log::error('[MissionService] Gagal log mission claimed', [
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get progress semua mission untuk user.
     * 
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUserMissions($userId)
    {
        try {
            $missions = Mission::active()
                ->with(['userMissions' => function($query) use ($userId) {
                    $query->where('user_id', $userId);
                }])
                ->get()
                ->map(function($mission) use ($userId) {
                    $userMission = $mission->userMissions->first();
                    $progressValue = $userMission?->progress_value ?? 0;
                    $progressPercentage = $mission->target_value > 0 
                        ? round(($progressValue / $mission->target_value) * 100, 2) 
                        : 0;
                    
                    return [
                        'mission_id' => $mission->mission_id,
                        'mission_name' => $mission->mission_name,
                        'description' => $mission->description,
                        'mission_type' => $mission->mission_type,
                        'target_value' => $mission->target_value,
                        'progress_value' => $progressValue,
                        'streak_count' => $userMission?->streak_count ?? 0,
                        'reward_points' => $mission->reward_points,
                        'period_type' => $mission->period_type,
                        'is_completed' => $userMission?->is_completed ?? false,
                        'is_claimed' => $userMission?->is_claimed ?? false,
                        'completed_at' => $userMission?->completed_at,
                        'claimed_at' => $userMission?->claimed_at,
                        'progress_percentage' => min($progressPercentage, 100),
                        'can_claim' => $userMission?->canClaim() ?? false,
                        'end_date' => $mission->end_date->format('Y-m-d'),
                        'days_left' => now()->diffInDays($mission->end_date, false),
                    ];
                });

            return $missions;

        } catch (Exception $e) {
            Log::error('[MissionService] Error get user missions', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Get completed missions yang belum diklaim.
     * 
     * @param int $userId
     * @return \Illuminate\Support\Collection
     */
    public function getUnclaimedMissions($userId)
    {
        try {
            return UserMission::with('mission')
                ->where('user_id', $userId)
                ->unclaimed()
                ->get()
                ->map(function($userMission) {
                    return [
                        'user_mission_id' => $userMission->user_mission_id,
                        'mission_id' => $userMission->mission_id,
                        'mission_name' => $userMission->mission->mission_name,
                        'description' => $userMission->mission->description,
                        'reward_points' => $userMission->mission->reward_points,
                        'progress_value' => $userMission->progress_value,
                        'target_value' => $userMission->mission->target_value,
                        'completed_at' => $userMission->completed_at?->format('Y-m-d H:i:s'),
                    ];
                });

        } catch (Exception $e) {
            Log::error('[MissionService] Error get unclaimed missions', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }

    /**
     * Get mission statistics untuk user.
     * 
     * @param int $userId
     * @return array
     */
    public function getUserMissionStats($userId)
    {
        try {
            $stats = [
                'total_missions' => Mission::active()->count(),
                'completed_missions' => UserMission::where('user_id', $userId)
                    ->completed()
                    ->count(),
                'claimed_missions' => UserMission::where('user_id', $userId)
                    ->claimed()
                    ->count(),
                'unclaimed_missions' => UserMission::where('user_id', $userId)
                    ->unclaimed()
                    ->count(),
                'total_points_earned' => DB::table('activity_logs')
                    ->where('user_id', $userId)
                    ->where('activity_type', 'MISSION_COMPLETED')
                    ->sum('points_awarded'),
            ];

            return $stats;

        } catch (Exception $e) {
            Log::error('[MissionService] Error get mission stats', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
}