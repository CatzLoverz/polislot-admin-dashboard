<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkSubarea;
use App\Models\UserValidation;
use App\Models\Validation;
use App\Services\HistoryService;
use App\Services\MissionService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
     *
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
            // 1. Cek Cooldown (15 Menit)
            $lastValidation = UserValidation::where('user_id', $user->user_id)
                ->latest()
                ->first();

            if ($lastValidation) {
                $diffInMinutes = Carbon::parse($lastValidation->created_at)->diffInMinutes(now());
                if ($diffInMinutes < 15) {
                    $wait = 15 - $diffInMinutes;

                    // Pesan ini akan muncul di Snackbar Gagal (Merah)
                    return $this->sendError("Anda baru saja melakukan validasi. Mohon tunggu $wait menit lagi.", 429);
                }
            }

            // 2. Ambil Info Subarea & Area (Untuk History Name)
            $subarea = ParkSubarea::with('parkArea')->find($request->park_subarea_id);
            $areaName = $subarea->parkArea ? $subarea->parkArea->park_area_name : 'Area Parkir';

            // 3. Ambil Setting Validasi
            $validationSetting = Validation::first();
            $points = $validationSetting ? $validationSetting->validation_points : 0;
            $isGeofenceActive = $validationSetting ? $validationSetting->validation_is_geofence_active : false;

            // 🛑 GEOFENCE CHECK
            if ($isGeofenceActive) {
                if (! $request->has(['latitude', 'longitude'])) {
                    return $this->sendError('Lokasi diperlukan untuk validasi di area ini.', 400);
                }

                $userLat = (float) $request->latitude;
                $userLng = (float) $request->longitude;

                // Hitung Centroid dari Polygon Subarea
                $polygon = $subarea->park_subarea_polygon; // Array of ['lat', 'lng']

                if (empty($polygon)) {
                    // Fallback jika polygon kosong, skip atau deny (di sini kita skip agar aman)
                    Log::warning("[API UserValidationController@store] Polygon kosong untuk Subarea ID {$subarea->park_subarea_id}");
                } else {
                    $centerLat = 0;
                    $centerLng = 0;
                    $count = count($polygon);

                    foreach ($polygon as $point) {
                        $centerLat += $point['lat'];
                        $centerLng += $point['lng'];
                    }

                    $centerLat /= $count;
                    $centerLng /= $count;

                    // Hitung Jarak (Haversine Formula)
                    $distance = $this->calculateDistance($userLat, $userLng, $centerLat, $centerLng);

                    // Batas Toleransi: 100 meter (Agar tidak terlalu sulit)
                    if ($distance > 100) {
                        return $this->sendError("Anda terlalu jauh dari area parkir ($distance m). Harap berada di lokasi.", 422);
                    }
                }
            }

            return DB::transaction(function () use ($request, $user, $areaName, $validationSetting, $points) {
                // 4. Simpan Validasi
                UserValidation::create([
                    'user_id' => $user->user_id,
                    'validation_id' => $validationSetting ? $validationSetting->validation_id : 1,
                    'park_subarea_id' => $request->park_subarea_id,
                    'user_validation_content' => $request->user_validation_content,
                ]);

                // 5. Tambah Poin & History
                if ($points > 0) {
                    $user->increment('current_points', $points);
                    $user->increment('lifetime_points', $points);

                    $this->historyService->log(
                        $user->user_id,
                        'validation',
                        $areaName,
                        $points
                    );
                }

                // 6. Update Misi
                $this->missionService->updateProgress($user->user_id, 'VALIDATION_ACTION');

                return $this->sendSuccess("Validasi berhasil! Anda mendapatkan $points poin.", [
                    'points_earned' => $points,
                    'current_points' => $user->current_points,
                    'status' => $request->user_validation_content,
                ], 201);
            });

        } catch (Exception $e) {
            Log::error('[API UserValidationController@store] Error: '.$e->getMessage());

            return $this->sendError('Terjadi kesalahan server saat memproses validasi.', 500);
        }
    }

    /**
     * Menghitung jarak antara dua titik koordinat (dalam meter).
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c);
    }
}
