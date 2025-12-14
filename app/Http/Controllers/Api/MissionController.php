<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\User;
use App\Models\UserMission;
use App\Models\UserHistory;
use App\Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class MissionController extends Controller
{
    /**
     * Mengambil data halaman Misi & Leaderboard secara agregat.
     * * Data mencakup:
     * 1. Statistik User (Total Misi Selesai & Lifetime Points)
     * 2. Daftar Misi Aktif beserta progres user saat ini
     * 3. Leaderboard Top 20 berdasarkan Lifetime Points
     * 4. Posisi User saat ini di Leaderboard
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            // 1. Header Stats
            $completedMissionsCount = UserHistory::where('user_id', $user->user_id)
                ->where('user_history_type', 'mission')
                ->count();
            
            $stats = [
                'total_completed' => $completedMissionsCount,
                'lifetime_points' => $user->lifetime_points,
            ];

            // 2. Missions List
            $missionsRaw = Mission::where('mission_is_active', true)->orderBy('created_at', 'desc')->get();
            
            $missions = $missionsRaw->map(function ($mission) use ($user) {
                // Ambil progress user untuk misi ini
                // Gunakan latest() jika ada kemungkinan multiple record (history), ambil yang paling baru
                $userProgress = UserMission::where('user_id', $user->user_id)
                    ->where('mission_id', $mission->mission_id)
                    ->latest('updated_at') 
                    ->first();

                $currentValue = $userProgress ? $userProgress->user_mission_current_value : 0;
                $isCompleted = $userProgress ? (bool)$userProgress->user_mission_is_completed : false;
                $completedAt = $userProgress ? $userProgress->user_mission_completed_at : null;

                // 🛑 LOGIKA PERSENTASE (PERBAIKAN UTAMA)
                // Jika sudah selesai (is_completed = true), paksa persentase jadi 1.0 (100%)
                if ($isCompleted) {
                    $percentage = 1.0;
                } else {
                    // Jika belum, hitung normal
                    $percentage = $mission->mission_threshold > 0 
                        ? min(1, $currentValue / $mission->mission_threshold) 
                        : 0;
                }

                return [
                    'mission_id' => $mission->mission_id,
                    'title' => $mission->mission_title,
                    'description' => $mission->mission_description,
                    'points' => $mission->mission_points,
                    'metric_code' => $mission->mission_metric_code,
                    'threshold' => $mission->mission_threshold,
                    'current_value' => $currentValue,
                    'percentage' => $percentage, // Ini yang dipakai UI Flutter
                    'is_completed' => $isCompleted,
                    'completed_at' => $completedAt ? \Carbon\Carbon::parse($completedAt)->format('d M Y, H:i') : null,
                ];
            });

            // 3. Leaderboard & Rank (Logic Tetap Sama)
            $leaderboardRaw = User::select('user_id', 'name', 'avatar', 'lifetime_points')
                ->whereNotNull('email_verified_at')
                ->orderBy('lifetime_points', 'desc')
                ->take(10)
                ->get();

            $leaderboard = $leaderboardRaw->map(function ($u, $index) use ($user) {
                return [
                    'rank' => $index + 1,
                    'name' => $u->name,
                    'avatar' => $u->avatar,
                    'points' => $u->lifetime_points,
                    'is_current_user' => $u->user_id === $user->user_id,
                ];
            });

            $userRankPos = User::whereNotNull('email_verified_at')
                ->where('lifetime_points', '>', $user->lifetime_points)
                ->count() + 1;
            
            $userRankData = [
                'rank' => $userRankPos,
                'name' => $user->name,
                'points' => $user->lifetime_points,
            ];

            return $this->sendSuccess('Data misi berhasil dimuat.', [
                'stats' => $stats,
                'missions' => $missions,
                'leaderboard' => $leaderboard,
                'user_rank' => $userRankData
            ]);

        } catch (\Exception $e) {
            return $this->sendError('Gagal memuat data misi: ' . $e->getMessage(), 500);
        }
    }
}