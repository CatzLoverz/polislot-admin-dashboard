<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Http\JsonResponse;
use Exception;

class UserRewardController extends Controller
{
    /**
     * Tampilkan katalog reward untuk user
     */
    public function index(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Ambil poin user dari tabel users
            $user = DB::table('users')->where('user_id', $userId)->first();
            $userPoints = $user->current_points ?? 0;
            
            // Ambil semua rewards
            $rewards = DB::table('rewards')
                ->select(
                    'reward_id',
                    'reward_name',
                    'description',
                    'points_required',
                    'reward_type',
                    'reward_image'
                )
                ->orderBy('points_required', 'asc')
                ->get()
                ->map(function ($reward) use ($userPoints) {
                    return [
                        'reward_id' => $reward->reward_id,
                        'reward_name' => $reward->reward_name,
                        'description' => $reward->description,
                        'points_required' => $reward->points_required,
                        'reward_type' => $reward->reward_type,
                        'reward_image' => $reward->reward_image ? asset('storage/' . $reward->reward_image) : null,
                        'can_exchange' => $userPoints >= $reward->points_required, // ← Cek apakah bisa tukar
                    ];
                });

            Log::info('[API UserRewardController@index] User membuka katalog reward', [
                'user_id' => $userId,
                'user_points' => $userPoints
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Katalog reward berhasil diambil.',
                'data' => [
                    'current_points' => $userPoints,
                    'rewards' => $rewards
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('[API UserRewardController@index] Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat memuat katalog reward.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Proses penukaran reward oleh user
     * Generate kode voucher otomatis
     */
    public function exchange(Request $request): JsonResponse
    {
        $request->validate([
            'reward_id' => 'required|exists:rewards,reward_id',
        ]);

        DB::beginTransaction();
        
        try {
            $userId = Auth::id();
            $rewardId = $request->reward_id;
            
            // Ambil data reward
            $reward = DB::table('rewards')->where('reward_id', $rewardId)->first();
            
            if (!$reward) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Reward tidak ditemukan.',
                ], 404);
            }
            
            // Ambil poin user
            $user = DB::table('users')->where('user_id', $userId)->first();
            $userPoints = $user->current_points ?? 0;
            
            // Cek apakah poin cukup
            if ($userPoints < $reward->points_required) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Poin kamu tidak cukup untuk menukar reward ini.',
                    'data' => [
                        'current_points' => $userPoints,
                        'points_required' => $reward->points_required,
                        'points_needed' => $reward->points_required - $userPoints
                    ]
                ], 400);
            }
            
            // Generate kode voucher unik (format: RWD-XXXXXX)
            do {
                $voucherCode = 'RWD-' . strtoupper(Str::random(6));
                $exists = DB::table('user_rewards')->where('voucher_code', $voucherCode)->exists();
            } while ($exists);
            
            // Kurangi current_points user (current_points berkurang, lifetime_points tetap)
            DB::table('users')
                ->where('user_id', $userId)
                ->decrement('current_points', $reward->points_required);
            
            // Insert ke user_rewards (masuk ke data verification)
            $userRewardId = DB::table('user_rewards')->insertGetId([
                'user_id' => $userId,
                'reward_id' => $rewardId,
                'voucher_code' => $voucherCode,
                'redeemed_status' => 'belum dipakai',
                'redeemed_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            
            DB::commit();
            
            // Ambil sisa poin user setelah penukaran
            $newUserPoints = $userPoints - $reward->points_required;
            
            Log::info('[API UserRewardController@exchange] User berhasil menukar reward', [
                'user_id' => $userId,
                'reward_id' => $rewardId,
                'voucher_code' => $voucherCode,
                'points_used' => $reward->points_required,
                'remaining_points' => $newUserPoints
            ]);
            
            return response()->json([
                'status' => 'success',
                'message' => 'Selamat! Reward berhasil ditukar.',
                'data' => [
                    'user_reward_id' => $userRewardId,
                    'voucher_code' => $voucherCode,
                    'reward_name' => $reward->reward_name,
                    'reward_type' => $reward->reward_type,
                    'reward_image' => $reward->reward_image ? asset('storage/' . $reward->reward_image) : null,
                    'points_used' => $reward->points_required,
                    'remaining_points' => $newUserPoints,
                    'redeemed_at' => now()->format('Y-m-d H:i:s'),
                    'status' => 'belum dipakai',
                    'instruction' => 'Tunjukkan kode voucher ini ke pihak manajemen untuk menukar reward.'
                ]
            ], 201);
                
        } catch (Exception $e) {
            DB::rollBack();
            
            Log::error('[API UserRewardController@exchange] Error', [
                'user_id' => Auth::id(),
                'reward_id' => $request->reward_id,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat menukar reward.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Riwayat penukaran reward user
     */
    public function myRewards(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Ambil riwayat penukaran user
            $myRewards = DB::table('user_rewards')
                ->join('rewards', 'user_rewards.reward_id', '=', 'rewards.reward_id')
                ->where('user_rewards.user_id', $userId)
                ->select(
                    'user_rewards.user_reward_id',
                    'user_rewards.voucher_code',
                    'user_rewards.redeemed_status',
                    'user_rewards.redeemed_at',
                    'user_rewards.created_at',
                    'user_rewards.updated_at',
                    'rewards.reward_name',
                    'rewards.reward_type',
                    'rewards.reward_image',
                    'rewards.points_required'
                )
                ->orderByDesc('user_rewards.created_at')
                ->get()
                ->map(function ($item) {
                    return [
                        'user_reward_id' => $item->user_reward_id,
                        'voucher_code' => $item->voucher_code,
                        'reward_name' => $item->reward_name,
                        'reward_type' => $item->reward_type,
                        'reward_image' => $item->reward_image ? asset('storage/' . $item->reward_image) : null,
                        'points_required' => $item->points_required,
                        'status' => $item->redeemed_status,
                        'exchanged_at' => $item->created_at,
                        'used_at' => $item->redeemed_status === 'terpakai' ? $item->updated_at : null,
                    ];
                });

            Log::info('[API UserRewardController@myRewards] User melihat riwayat reward', [
                'user_id' => $userId,
                'total_rewards' => $myRewards->count()
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Riwayat reward berhasil diambil.',
                'data' => $myRewards
            ], 200);

        } catch (Exception $e) {
            Log::error('[API UserRewardController@myRewards] Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil riwayat reward.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Detail voucher berdasarkan kode (untuk user cek status)
     */
    public function checkVoucher(Request $request): JsonResponse
    {
        $request->validate([
            'voucher_code' => 'required|string',
        ]);

        try {
            $userId = Auth::id();
            $voucherCode = strtoupper(trim($request->voucher_code));
            
            $voucher = DB::table('user_rewards')
                ->join('rewards', 'user_rewards.reward_id', '=', 'rewards.reward_id')
                ->where('user_rewards.voucher_code', $voucherCode)
                ->where('user_rewards.user_id', $userId) // Hanya voucher milik user sendiri
                ->select(
                    'user_rewards.*',
                    'rewards.reward_name',
                    'rewards.reward_type',
                    'rewards.reward_image',
                    'rewards.points_required'
                )
                ->first();
            
            if (!$voucher) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Kode voucher tidak ditemukan atau bukan milik kamu.',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Detail voucher berhasil diambil.',
                'data' => [
                    'voucher_code' => $voucher->voucher_code,
                    'reward_name' => $voucher->reward_name,
                    'reward_type' => $voucher->reward_type,
                    'reward_image' => $voucher->reward_image ? asset('storage/' . $voucher->reward_image) : null,
                    'points_required' => $voucher->points_required,
                    'status' => $voucher->redeemed_status,
                    'exchanged_at' => $voucher->created_at,
                    'used_at' => $voucher->redeemed_status === 'terpakai' ? $voucher->updated_at : null,
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('[API UserRewardController@checkVoucher] Error', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengecek voucher.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}