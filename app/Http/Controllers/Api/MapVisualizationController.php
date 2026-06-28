<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
use App\Models\UserValidation;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MapVisualizationController extends Controller
{
    /**
     * Menampilkan list Area parkir
     */
    public function index(): JsonResponse
    {
        try {
            $areas = ParkArea::withCount('parkSubarea')->orderBy('park_area_name', 'asc')->get();

            $data = $areas->map(function ($area) {
                return [
                    'id' => $area->park_area_id,
                    'name' => $area->park_area_name,
                    'code' => $area->park_area_code,
                    'sub_count' => $area->park_subarea_count,
                    'description' => "Memiliki {$area->park_subarea_count} sub-area yang dapat Anda tempati.",
                ];
            });

            return $this->sendSuccess('Daftar area berhasil diambil.', $data);
        } catch (Exception $e) {
            return $this->sendError('Gagal memuat area: '.$e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail Area beserta Subarea, Polygon, Amenities, Status, dan Jumlah Komentar.
     *
     * @param  int  $id  (park_area_id)
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $area = ParkArea::with([
                'parkSubarea' => function ($query) {
                    $query->withCount('subareaComment') // Hitung jumlah komentar
                        ->with(['parkAmenity', 'userValidation' => function ($q) {
                            $q->orderBy('created_at', 'desc');
                        }]);
                },
            ])->find($id);

            if (! $area) {
                return $this->sendError('Area parkir tidak ditemukan.', 404);
            }

            $formattedSubareas = $area->parkSubarea->map(function ($sub) {
                $live = $sub->getLiveStatus();

                return [
                    'id' => $sub->park_subarea_id,
                    'name' => $sub->park_subarea_name,
                    'polygon' => $sub->park_subarea_polygon,
                    'status' => $live['status'],
                    'is_validated' => $live['is_validated'],
                    'has_user_report' => $live['has_user_report'],
                    'current_count' => $sub->current_count ?? 0,
                    'max_slots' => $sub->max_slots ?? 0,
                    'validation_expires_at' => $live['validation_expires_at'],
                    'last_validation_time' => $live['last_validation_time'],
                    'validation_remaining_seconds' => $live['validation_remaining_seconds'],
                    'fallback_status' => $live['fallback_status'],
                    'fallback_status_color' => $live['fallback_status_color'],
                    'amenities' => $sub->parkAmenity->pluck('park_amenity_name'),
                    'comment_count' => $sub->subarea_comment_count ?? 0,
                ];
            });

            // Hitung Cooldown User
            $canValidate = true;
            $waitMinutes = 0;
            $remainingSeconds = 0;

            if ($user) {
                $lastValidation = UserValidation::where('user_id', $user->user_id)
                    ->latest()
                    ->first();

                if ($lastValidation) {
                    $diffInSeconds = Carbon::parse($lastValidation->created_at)->diffInSeconds(now());
                    if ($diffInSeconds < 900) {
                        $canValidate = false;
                        $remainingSeconds = 900 - $diffInSeconds;
                        $waitMinutes = ceil($remainingSeconds / 60);
                    }
                }
            }

            $data = [
                'area_id' => $area->park_area_id,
                'area_name' => $area->park_area_name,
                'area_code' => $area->park_area_code,
                'validation_cooldown' => [
                    'can_validate' => $canValidate,
                    'wait_minutes' => (int) $waitMinutes,
                    'remaining_seconds' => (int) $remainingSeconds,
                ],
                'subareas' => $formattedSubareas,
            ];

            return $this->sendSuccess('Data visualisasi berhasil diambil.', $data);

        } catch (Exception $e) {
            Log::error('Error sistem.', ['error' => $e->getMessage()]);

            return $this->sendError('Gagal memuat visualisasi: '.$e->getMessage(), 500);
        }
    }
}
