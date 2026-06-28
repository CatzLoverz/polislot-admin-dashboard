<?php

namespace App\Services;

use App\Models\UserHistory;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HistoryService
{
    /**
     * Mencatat history aktivitas user.
     *
     * @param  string  $type  (mission, validation, redeem)
     * @param  string  $name  Nama aktivitas (snapshot)
     * @param  int|null  $points  Jumlah poin
     * @param  bool  $isNegative  Default false (0)
     */
    public function log(int $userId, string $type, string $name, ?int $points, bool $isNegative = false): void
    {
        try {
            DB::transaction(function () use ($userId, $type, $name, $points, $isNegative) {
                UserHistory::create([
                    'user_id' => $userId,
                    'user_history_type' => $type,
                    'user_history_name' => $name,
                    'user_history_points' => $points,
                    'user_history_is_negative' => $isNegative,
                ]);
            });

            Log::info("Log created: User {$userId} | {$type} | {$name}");
        } catch (Exception $e) {
            Log::error('Failed to create log: '.$e->getMessage());
        }
    }
}
