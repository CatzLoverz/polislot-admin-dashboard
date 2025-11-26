<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Tier;
use App\Models\UserTier;
use Exception;

class TierService
{
    /**
     * Cek dan update tier user jika perlu.
     */
    public function checkAndUpdateTier($userId)
{
    try {

        $user = DB::table('users')->where('user_id', $userId)->first();

        if (!$user) {
            Log::warning('[TierService] User tidak ditemukan', ['user_id' => $userId]);
            return false;
        }

        $lifetime = $user->lifetime_points ?? 0;

        // Tier yang seharusnya didapat sesuai lifetime
        $newTier = Tier::where('min_points', '<=', $lifetime)
            ->orderBy('min_points', 'desc')
            ->first();

        if (!$newTier) {
            return false; // Tidak ada tier
        }

        // Ambil tier user sekarang
        $currentTier = UserTier::where('user_id', $userId)->first();

        // Jika belum punya tier → set tier pertama
        if (!$currentTier) {

            UserTier::create([
                'user_id' => $userId,
                'tier_id' => $newTier->tier_id,
            ]);

            $this->logTierChange($userId, 'None', $newTier->tier_name, true, $lifetime);

            return [
                'upgraded' => true,
                'is_new_tier' => true,
                'old_tier' => null,
                'new_tier' => $newTier->tier_name,
                'tier_data' => $newTier,
            ];
        }

        // Jika tier tidak berubah → selesai
        if ($currentTier->tier_id == $newTier->tier_id) {
            return [
                'upgraded' => false,
                'is_new_tier' => false,
                'old_tier' => $currentTier->tier_id,
                'new_tier' => $newTier->tier_id,
                'tier_data' => $newTier,
            ];
        }

        // Jika tier berubah → update tier
        $oldTier = Tier::find($currentTier->tier_id);

        $currentTier->tier_id = $newTier->tier_id;
        $currentTier->save();

        $this->logTierChange(
            $userId,
            $oldTier?->tier_name ?? 'Unknown',
            $newTier->tier_name,
            false,
            $lifetime
        );

        return [
            'upgraded' => true,
            'is_new_tier' => false,
            'old_tier' => $oldTier?->tier_name,
            'new_tier' => $newTier->tier_name,
            'tier_data' => $newTier,
        ];

    } catch (Exception $e) {

        Log::error('[TierService] Gagal cek/update tier', [
            'user_id' => $userId,
            'error' => $e->getMessage()
        ]);

        return false;
    }
}
    /**
     * Catat perubahan tier ke activity_logs.
     */
    private function logTierChange($userId, $oldTier, $newTier, $isNewTier, $lifetimePoints)
    {
        try {
            $activityType = $isNewTier ? 'TIER_FIRST_ASSIGNED' : 'TIER_UPGRADED';
            $description = $isNewTier 
                ? "Mendapatkan tier pertama: {$newTier}"
                : "Naik tier dari {$oldTier} ke {$newTier}";

            DB::table('activity_logs')->insert([
                'user_id' => $userId,
                'activity_type' => $activityType,
                'points_awarded' => 0,
                'description' => $description,
                'metadata' => json_encode([
                    'old_tier' => $oldTier,
                    'new_tier' => $newTier,
                    'is_new_tier' => $isNewTier,
                    'lifetime_points_at_upgrade' => $lifetimePoints,
                    'upgraded_at' => now()->toDateTimeString(),
                ]),
                'created_at' => now(),
            ]);

            Log::info('[TierService] Tier change berhasil dicatat ke activity_logs', [
                'user_id' => $userId,
                'activity_type' => $activityType,
            ]);

        } catch (Exception $e) {
            Log::error('[TierService] Gagal mencatat tier change ke activity_logs', [
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get tier info berdasarkan lifetime points.
     */
    public function getTierByPoints($lifetimePoints)
    {
        return Tier::where('min_points', '<=', $lifetimePoints)
            ->orderBy('min_points', 'desc')
            ->first();
    }

    /**
     * Get next tier info untuk user.
     */
    public function getNextTier($currentLifetimePoints)
    {
        return Tier::where('min_points', '>', $currentLifetimePoints)
            ->orderBy('min_points', 'asc')
            ->first();
    }
}