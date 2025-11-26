<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class PointService
{
    protected $tierService;

    public function __construct(TierService $tierService)
    {
        $this->tierService = $tierService;
        
        Log::info('[PointService] Constructed', [
            'tier_service_class' => get_class($tierService)
        ]);
    }

    /**
     * Tambah poin ke user berdasarkan activity code.
     */
    public function addPoints($userId, $activityCode, $description = null)
    {
        try {
            Log::info('[PointService@addPoints] START', [
                'user_id' => $userId,
                'activity_code' => $activityCode,
                'description' => $description
            ]);

            // Ambil rule poin
            $rule = DB::table('point_rules')
                ->where('activity_code', $activityCode)
                ->where('is_active', 1)
                ->first();

            Log::info('[PointService@addPoints] Query rule result', [
                'activity_code' => $activityCode,
                'rule_found' => $rule ? 'YES' : 'NO',
                'rule_data' => $rule
            ]);

            if (!$rule) {
                $availableRules = DB::table('point_rules')->get(['activity_code', 'is_active']);
                
                Log::warning('[PointService@addPoints] Point rule tidak ditemukan atau tidak aktif', [
                    'activity_code' => $activityCode,
                    'available_rules' => $availableRules
                ]);
                return false;
            }

            Log::info('[PointService@addPoints] Rule ditemukan, mulai transaction', [
                'points' => $rule->points
            ]);

            DB::beginTransaction();

            // Ambil data user sebelum update
            $userBefore = DB::table('users')->where('user_id', $userId)->first();
            $oldPoints = $userBefore->current_points ?? 0;
            $oldLifetimePoints = $userBefore->lifetime_points ?? 0;

            Log::info('[PointService@addPoints] User data before update', [
                'old_current_points' => $oldPoints,
                'old_lifetime_points' => $oldLifetimePoints
            ]);

            // Update poin user
            DB::table('users')
                ->where('user_id', $userId)
                ->increment('current_points', $rule->points);

            DB::table('users')
                ->where('user_id', $userId)
                ->increment('lifetime_points', $rule->points);

            Log::info('[PointService@addPoints] User points updated');

            // HAPUS point_history, langsung ke activity_logs
            $this->logActivity(
                $userId,
                'POINTS_EARNED',
                $rule->points,
                $description ?? $rule->description,
                [
                    'activity_code' => $activityCode,
                    'activity_name' => $rule->activity_name,
                    'old_current_points' => $oldPoints,
                    'new_current_points' => $oldPoints + $rule->points,
                    'old_lifetime_points' => $oldLifetimePoints,
                    'new_lifetime_points' => $oldLifetimePoints + $rule->points,
                ]
            );

            Log::info('[PointService@addPoints] Activity log inserted');

            // CEK TIER OTOMATIS setelah dapat poin
            $tierResult = $this->tierService->checkAndUpdateTier($userId);

            Log::info('[PointService@addPoints] Tier check result', [
                'tier_result' => $tierResult
            ]);

            DB::commit();

            Log::info('[PointService@addPoints] Transaction committed - SUCCESS', [
                'user_id' => $userId,
                'activity_code' => $activityCode,
                'points' => $rule->points,
                'tier_upgraded' => $tierResult['upgraded'] ?? false,
            ]);

            return [
                'success' => true,
                'points' => $rule->points,
                'activity' => $rule->activity_name,
                'tier_upgraded' => $tierResult['upgraded'] ?? false,
                'is_new_tier' => $tierResult['is_new_tier'] ?? false,
                'new_tier' => $tierResult['new_tier'] ?? null,
                'tier_data' => $tierResult['tier_data'] ?? null,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[PointService@addPoints] ERROR - Transaction rolled back', [
                'user_id' => $userId,
                'activity_code' => $activityCode,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Kurangi poin user (untuk penukaran hadiah, dll).
     */
    public function deductPoints($userId, $points, $description = 'Penukaran hadiah', $metadata = [])
    {
        try {
            // Cek apakah poin cukup
            $user = DB::table('users')->where('user_id', $userId)->first();

            if (!$user || $user->current_points < $points) {
                Log::warning('Poin tidak cukup', [
                    'user_id' => $userId,
                    'required_points' => $points,
                    'current_points' => $user->current_points ?? 0
                ]);
                return false;
            }

            DB::beginTransaction();

            $oldPoints = $user->current_points;

            // Kurangi current points saja (lifetime tidak berkurang)
            DB::table('users')
                ->where('user_id', $userId)
                ->decrement('current_points', $points);

            // HAPUS point_history, langsung ke activity_logs
            $this->logActivity(
                $userId,
                'POINTS_SPENT',
                -$points, // Negatif untuk pengeluaran
                $description,
                array_merge([
                    'old_current_points' => $oldPoints,
                    'new_current_points' => $oldPoints - $points,
                    'lifetime_points' => $user->lifetime_points, // Tidak berubah
                ], $metadata)
            );

            DB::commit();

            Log::info('Berhasil mengurangi poin', [
                'user_id' => $userId,
                'points' => $points
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Gagal mengurangi poin', [
                'user_id' => $userId,
                'points' => $points,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Catat aktivitas ke activity_logs.
     */
    private function logActivity($userId, $activityType, $pointsAwarded = null, $description = null, $metadata = [])
    {
        try {
            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'activity_type' => $activityType,
                'points_awarded' => $pointsAwarded,
                'description' => $description,
                'metadata' => json_encode($metadata),
                'created_at' => now(),
            ]);
            
            Log::info('[PointService@logActivity] Activity logged', [
                'user_id' => $userId,
                'activity_type' => $activityType
            ]);
        } catch (Exception $e) {
            Log::error('[PointService@logActivity] Failed to log activity', [
                'user_id' => $userId,
                'activity_type' => $activityType,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Catat aktivitas reward redemption.
     */
    public function logRewardRedemption($userId, $rewardName, $pointsSpent)
    {
        $this->logActivity(
            $userId,
            'REWARD_REDEEMED',
            -$pointsSpent,
            "Menukar hadiah: {$rewardName}",
            [
                'reward_name' => $rewardName,
                'points_spent' => $pointsSpent,
                'redeemed_at' => now()->toDateTimeString(),
            ]
        );

        Log::info('Reward redemption berhasil dicatat', [
            'user_id' => $userId,
            'reward' => $rewardName,
            'points' => $pointsSpent
        ]);
    }

    /**
     * Catat aktivitas achievement unlock.
     */
    public function logAchievementUnlock($userId, $achievementName, $pointsAwarded = 0)
    {
        $this->logActivity(
            $userId,
            'ACHIEVEMENT_UNLOCKED',
            $pointsAwarded,
            "Membuka achievement: {$achievementName}",
            [
                'achievement_name' => $achievementName,
                'unlocked_at' => now()->toDateTimeString(),
            ]
        );

        Log::info('Achievement unlock berhasil dicatat', [
            'user_id' => $userId,
            'achievement' => $achievementName
        ]);
    }
    /**
     * Tambah poin custom (untuk mission reward atau bonus khusus).
     */
    public function addCustomPoints($userId, $activityCode, $points, $description)
    {
        try {
            Log::info('[PointService@addCustomPoints] START', [
                'user_id' => $userId,
                'activity_code' => $activityCode,
                'points' => $points,
                'description' => $description
            ]);

            DB::beginTransaction();

            // Ambil data user sebelum update
            $userBefore = DB::table('users')->where('user_id', $userId)->first();
            $oldPoints = $userBefore->current_points ?? 0;
            $oldLifetimePoints = $userBefore->lifetime_points ?? 0;

            Log::info('[PointService@addCustomPoints] User data before update', [
                'old_current_points' => $oldPoints,
                'old_lifetime_points' => $oldLifetimePoints
            ]);

            // Update poin user
            DB::table('users')
                ->where('user_id', $userId)
                ->increment('current_points', $points);

            DB::table('users')
                ->where('user_id', $userId)
                ->increment('lifetime_points', $points);

            Log::info('[PointService@addCustomPoints] User points updated');

            // Log ke activity_logs
            $this->logActivity(
                $userId,
                'POINTS_EARNED',
                $points,
                $description,
                [
                    'activity_code' => $activityCode,
                    'activity_name' => $description,
                    'old_current_points' => $oldPoints,
                    'new_current_points' => $oldPoints + $points,
                    'old_lifetime_points' => $oldLifetimePoints,
                    'new_lifetime_points' => $oldLifetimePoints + $points,
                ]
            );

            Log::info('[PointService@addCustomPoints] Activity log inserted');

            // Cek tier
            $tierResult = $this->tierService->checkAndUpdateTier($userId);

            Log::info('[PointService@addCustomPoints] Tier check result', [
                'tier_result' => $tierResult
            ]);

            DB::commit();

            Log::info('[PointService@addCustomPoints] Transaction committed - SUCCESS', [
                'user_id' => $userId,
                'points' => $points,
                'tier_upgraded' => $tierResult['upgraded'] ?? false,
            ]);

            return [
                'success' => true,
                'points' => $points,
                'tier_upgraded' => $tierResult['upgraded'] ?? false,
                'is_new_tier' => $tierResult['is_new_tier'] ?? false,
                'new_tier' => $tierResult['new_tier'] ?? null,
                'tier_data' => $tierResult['tier_data'] ?? null,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('[PointService@addCustomPoints] ERROR - Transaction rolled back', [
                'user_id' => $userId,
                'points' => $points,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }
}