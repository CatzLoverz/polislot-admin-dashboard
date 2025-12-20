<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use App\Models\User;
use App\Models\UserReward;
use App\Services\HistoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RewardController extends Controller
{
    protected $historyService;

    public function __construct(HistoryService $historyService)
    {
        $this->historyService = $historyService;
    }

    /**
     * Mengambil daftar reward yang tersedia dan poin user saat ini.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            /** @var User $user */
            $user = Auth::user();

            $rewards = DB::table('rewards')->orderBy('created_at', 'desc')->get();

            // Format data reward agar siap pakai di frontend
            $formattedRewards = $rewards->map(function ($reward) {
                return [
                    'reward_id' => $reward->reward_id,
                    'name' => $reward->reward_name,
                    'type' => $reward->reward_type, // 'Voucher' atau 'Barang'
                    'points_required' => $reward->reward_point_required,
                    'image' => $reward->reward_image, // Path gambar
                ];
            });

            return $this->sendSuccess('Data reward berhasil diambil.', [
                'current_points' => $user->current_points,
                'rewards' => $formattedRewards,
            ]);

        } catch (\Exception $e) {
            Log::error('[API RewardController@index] Gagal: '.$e->getMessage());

            return $this->sendError('Terjadi kesalahan server.', 500);
        }
    }

    /**
     * Melakukan penukaran reward (Redeem).
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function redeem(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                $request->validate([
                    'reward_id' => 'required|exists:rewards,reward_id',
                ]);

                /** @var User $user */
                $user = Auth::user();

                // Lock row user untuk mencegah race condition saldo
                $user = User::where('user_id', $user->user_id)->lockForUpdate()->first();

                $reward = Reward::where('reward_id', $request->reward_id)->first();

                // Cek Poin Cukup
                if ($user->current_points < $reward->reward_point_required) {
                    DB::rollBack();
                    Log::warning("[API RewardController@redeem] Gagal: Poin tidak cukup. User: {$user->user_id}, Reward: {$reward->reward_id}");

                    return $this->sendError('Poin Anda tidak mencukupi untuk reward ini.', 422);
                }

                // Kurangi Poin
                $user->current_points -= $reward->reward_point_required;
                $user->save();

                // Generate Kode Unik
                if ($reward->reward_type == 'Voucher') {
                    $code = 'VCR-'.strtoupper(Str::random(6));
                } else {
                    $code = 'BRG-'.strtoupper(Str::random(6));
                }
                // Buat Record UserReward
                $userReward = UserReward::create([
                    'user_id' => $user->user_id,
                    'reward_id' => $reward->reward_id,
                    'user_reward_code' => $code,
                    'user_reward_status' => UserReward::STATUS_PENDING,
                ]);

                Log::info("[API RewardController@redeem] Sukses: User {$user->user_id} menukar {$reward->reward_name}.");
                $this->historyService->log(
                    $user->user_id,
                    'redeem',
                    $reward->reward_name,
                    $reward->reward_point_required,
                    true
                );

                return $this->sendSuccess('Penukaran berhasil!', [
                    'voucher_code' => $code,
                    'current_points' => $user->current_points,
                    'reward_name' => $reward->reward_name,
                ], 201);
            });
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('[API RewardController@redeem] Gagal: '.$e->getMessage());

            return $this->sendError('Gagal memproses penukaran: '.$e->getMessage(), 422);
        }
    }

    /**
     * Mengambil riwayat penukaran reward user.
     */
    public function history(): JsonResponse
    {
        try {
            $user = Auth::user();

            $history = UserReward::with('reward')
                ->where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->user_reward_id,
                        'name' => $item->reward->reward_name,
                        'type' => $item->reward->reward_type,
                        'code' => $item->user_reward_code,
                        'status' => $item->user_reward_status,
                        'created_at' => $item->created_at->format('d M Y H:i'),
                        'updated_at' => $item->updated_at->format('d M Y H:i'),
                    ];
                });

            return $this->sendSuccess('Riwayat penukaran berhasil diambil.', $history);

        } catch (\Exception $e) {
            Log::error('[API RewardController@history] Gagal: '.$e->getMessage());

            return $this->sendError('Gagal memuat riwayat.', 500);
        }
    }
}
