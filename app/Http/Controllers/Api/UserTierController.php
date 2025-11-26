<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Tier;
use App\Models\UserTier;
use Exception;

class UserTierController extends Controller
{
    /**
     * Ambil tier user saat ini + poin
     */
    public function show(): JsonResponse
{
    try {
        $user = Auth::user();

        Log::info('[API UserTierController@show] Mengambil tier user', [
            'user_id' => $user->user_id,
        ]);

        // Ambil poin dari tabel users
        $userData = DB::table('users')->where('user_id', $user->user_id)->first();
        
        if (!$userData) {
            return response()->json([
                'status' => 'error',
                'message' => 'User tidak ditemukan',
            ], 404);
        }

        // Tentukan tier berdasarkan lifetime_points
        $tier = Tier::where('min_points', '<=', $userData->lifetime_points)
            ->orderBy('min_points', 'desc')
            ->first();

        $data = [
            'id' => $userData->user_id, // ✅ TAMBAHKAN INI
            'tier' => $tier?->tier_name ?? null,
            'tier_color' => $tier?->color_theme ?? null,
            'tier_icon' => $tier?->icon ?? null,
            'lifetime_points' => $userData->lifetime_points ?? 0,
            'current_points' => $userData->current_points ?? 0,
        ];

        return response()->json([
            'status' => 'success',
            'message' => 'Data tier user berhasil diambil.',
            'data' => $data
        ], 200);

    } catch (Exception $e) {

        Log::error('[API UserTierController@show] Error', [
            'error' => $e->getMessage(),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan saat mengambil tier user.',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * Perbarui tier berdasarkan lifetime_points
     * Simpan ke user_tiers jika tier naik (untuk notifikasi)
     */
    public function updateTier(): JsonResponse
    {
        try {
            $user = Auth::user();
            Log::info('[API UserTierController@updateTier] Memperbarui tier user', [
                'user_id' => $user->user_id
            ]);

            // Ambil data user
            $userData = DB::table('users')->where('user_id', $user->user_id)->first();

            if (!$userData) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User tidak ditemukan.',
                ], 404);
            }

            $lifetime = $userData->lifetime_points ?? 0;

            // Cari tier terakhir user dari history
            $lastUserTier = UserTier::where('user_id', $user->user_id)
                ->orderBy('assigned_at', 'desc')
                ->first();

            // Cari tier baru berdasarkan lifetime_points saat ini
            $newTier = Tier::where('min_points', '<=', $lifetime)
                ->orderBy('min_points', 'desc')
                ->first();

            $tierUp = false;
            $isNewTier = false;

            // Cek apakah tier naik
            if ($newTier) {
                if (!$lastUserTier) {
                    // User belum punya tier sama sekali - tier pertama
                    $isNewTier = true;
                } elseif ($lastUserTier->tier_id !== $newTier->tier_id) {
                    // Tier berubah - cek apakah naik atau turun
                    $oldTier = Tier::find($lastUserTier->tier_id);
                    if ($oldTier && $newTier->min_points > $oldTier->min_points) {
                        $tierUp = true;
                    }
                }

                // Simpan ke user_tiers jika tier naik atau tier pertama
                if ($tierUp || $isNewTier) {
                    UserTier::create([
                        'user_id' => $user->user_id,
                        'tier_id' => $newTier->tier_id,
                        'assigned_at' => now(),
                    ]);

                    Log::info('[API UserTierController@updateTier] Tier berubah', [
                        'user_id' => $user->user_id,
                        'old_tier' => $lastUserTier?->tier_id,
                        'new_tier' => $newTier->tier_id,
                        'tier_up' => $tierUp,
                    ]);
                }
            }

            $data = [
                'tier_up' => $tierUp,
                'is_new_tier' => $isNewTier,
                'tier' => $newTier?->tier_name ?? null,
                'tier_color' => $newTier?->color_theme ?? null,
                'tier_icon' => $newTier?->icon ?? null,
                'lifetime_points' => $userData->lifetime_points ?? 0,
                'current_points' => $userData->current_points ?? 0,
                'message' => $tierUp 
                    ? "Selamat! Kamu naik ke tier {$newTier->tier_name}!" 
                    : ($isNewTier 
                        ? "Selamat! Kamu mendapat tier {$newTier->tier_name}!" 
                        : 'Tier tetap.'),
            ];

            return response()->json([
                'status' => 'success',
                'message' => $data['message'],
                'data' => $data
            ], 200);

        } catch (Exception $e) {

            Log::error('[API UserTierController@updateTier] Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memperbarui tier user.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Leaderboard berdasarkan lifetime_points
     */
    /**
     * Leaderboard berdasarkan lifetime_points
     */
    public function leaderboard(): JsonResponse
    {
        try {
            // Ambil semua user berdasarkan lifetime_points (descending)
            $leaders = DB::table('users')
                ->select('user_id', 'name', 'avatar', 'lifetime_points', 'current_points')
                ->where('role', 'user')
                ->orderBy('lifetime_points', 'desc')
                ->limit(100) // Ambil top 100
                ->get()
                ->map(function ($item, $index) {
                    // Tentukan tier berdasarkan lifetime_points
                    $tier = Tier::where('min_points', '<=', $item->lifetime_points)
                        ->orderBy('min_points', 'desc')
                        ->first();

                    // KELOMPOKKAN DATA TIER DI SINI AGAR SESUAI DENGAN MODEL FLUTTER BARU
                    $tierData = $tier ? [
                        'tier' => $tier->tier_name,
                        'color' => $tier->color_theme, // Menggunakan key 'color'
                        'icon' => $tier->icon,
                        'lifetime_points' => $item->lifetime_points ?? 0,
                        'current_points' => $item->current_points ?? 0,
                        'tier_up' => false, // Default: false, hanya relevan saat update
                    ] : null;

                    return [
                        'rank' => $index + 1,
                        'id' => $item->user_id, // Ganti user_id ke id agar sesuai model Flutter
                        'name' => $item->name ?? 'Unknown',
                        'avatar' => $item->avatar,
                        'lifetime_points' => $item->lifetime_points ?? 0,
                        
                        // KIRIM DATA TIER SEBAGAI OBJECT BERSARANG
                        'tier' => $tierData, 
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'Leaderboard berhasil diambil.',
                'data' => $leaders,
            ], 200);

        } catch (Exception $e) {

            Log::error('[API UserTierController@leaderboard] Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil leaderboard.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * History tier user (kapan naik tier)
     */
    public function tierHistory(): JsonResponse
    {
        try {
            $user = Auth::user();

            $history = UserTier::with('tier')
                ->where('user_id', $user->user_id)
                ->orderBy('assigned_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'tier_name' => $item->tier?->tier_name,
                        'tier_color' => $item->tier?->color_theme,
                        'tier_icon' => $item->tier?->icon,
                        'achieved_at' => $item->assigned_at?->format('d M Y, H:i'),
                    ];
                });

            return response()->json([
                'status' => 'success',
                'message' => 'History tier berhasil diambil.',
                'data' => $history,
            ], 200);

        } catch (Exception $e) {

            Log::error('[API UserTierController@tierHistory] Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil history tier.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}