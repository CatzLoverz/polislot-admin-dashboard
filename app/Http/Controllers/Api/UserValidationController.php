<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserValidation;
use App\Models\Validation;
use App\Services\MissionService;
use App\Services\HistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class UserValidationController extends Controller
{
    protected $missionService;
    protected $historyService;

    public function __construct(MissionService $missionService, HistoryService $historyService)
    {
        $this->missionService = $missionService;
        $this->historyService = $historyService;
    }

    /**
     * Memproses validasi parkir dari user.
     * * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'park_subarea_id' => 'required|exists:park_subareas,park_subarea_id',
            'user_validation_content' => 'required|in:banyak,terbatas,penuh',
        ]);

        $user = $request->user();

        try {
            // Cek Cooldown (15 Menit)
            $lastValidation = UserValidation::where('user_id', $user->user_id)
                ->latest()
                ->first();

            if ($lastValidation) {
                $diffInMinutes = Carbon::parse($lastValidation->created_at)->diffInMinutes(now());
                if ($diffInMinutes < 15) {
                    $wait = 15 - $diffInMinutes;
                    return $this->sendError("Anda baru saja melakukan validasi. Tunggu $wait menit lagi.", 429);
                }
            }

            return DB::transaction(function () use ($request, $user) {
                // Ambil Poin Setting
                $validationSetting = Validation::first();
                $points = $validationSetting ? $validationSetting->validation_points : 0;

                // Simpan Validasi
                UserValidation::create([
                    'user_id' => $user->user_id,
                    'validation_id' => $validationSetting ? $validationSetting->validation_id : 1,
                    'park_subarea_id' => $request->park_subarea_id,
                    'user_validation_content' => $request->user_validation_content,
                ]);

                // Tambah Poin & History
                if ($points > 0) {
                    $user->increment('current_points', $points);
                    $user->increment('lifetime_points', $points);

                    $this->historyService->log(
                        $user->user_id,
                        'validation',
                        'Validasi Area Parkir',
                        $points
                    );
                }

                // Update Misi
                $this->missionService->updateProgress($user->user_id, 'VALIDATION_ACTION');

                return $this->sendSuccess("Validasi berhasil! Anda mendapatkan $points poin.", [
                    'points_earned' => $points,
                    'current_points' => $user->current_points,
                    'status' => $request->user_validation_content
                ]);
            });

        } catch (Exception $e) {
            Log::error('[API UserValidation] Error: ' . $e->getMessage());
            return $this->sendError('Terjadi kesalahan server.', 500);
        }
    }
}