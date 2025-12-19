<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
use Illuminate\Http\JsonResponse;

class MapVisualizationController extends Controller
{
    /**
     * Menampilkan list Area parkir
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $areas = ParkArea::withCount('parkSubarea')->get();

            $data = $areas->map(function ($area) {
                return [
                    'id'          => $area->park_area_id,
                    'name'        => $area->park_area_name,
                    'code'        => $area->park_area_code,
                    'sub_count'   => $area->park_subarea_count,
                    'description' => "Memiliki {$area->park_subarea_count} sub-area yang dapat Anda tempati.",
                ];
            });

            return $this->sendSuccess('Daftar area berhasil diambil.', $data);
        } catch (\Exception $e) {
            return $this->sendError('Gagal memuat area: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail Area beserta Subarea, Polygon, Amenities, Status, dan Jumlah Komentar.
     * @param int $id (park_area_id)
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $area = ParkArea::with([
                'parkSubarea' => function($query) {
                    $query->withCount('subareaComment') // Hitung jumlah komentar
                          ->with(['parkAmenity', 'userValidation' => function($q) {
                              $q->orderBy('created_at', 'desc');
                          }]);
                }
            ])->find($id);

            if (!$area) {
                return $this->sendError('Area parkir tidak ditemukan.', 404);
            }

            $formattedSubareas = $area->parkSubarea->map(function ($sub) {
                // --- LOGIKA PENENTUAN STATUS (Voting 1 Jam Terakhir) ---
                $status = 'netral'; 
                $allValidations = $sub->userValidation;

                if ($allValidations->isNotEmpty()) {
                    $latestValidation = $allValidations->first();
                    $anchorTime = $latestValidation->created_at;
                    $cutoffTime = $anchorTime->copy()->subHour();

                    $validVotes = $allValidations->filter(function ($val) use ($cutoffTime) {
                        return $val->created_at >= $cutoffTime;
                    });

                    if ($validVotes->isNotEmpty()) {
                        $counts = $validVotes->countBy('user_validation_content');
                        $banyak = $counts->get('banyak', 0);
                        $terbatas = $counts->get('terbatas', 0);
                        $penuh = $counts->get('penuh', 0);

                        if ($penuh >= $terbatas && $penuh >= $banyak) {
                            $status = 'penuh';
                        } elseif ($terbatas >= $banyak) {
                            $status = 'terbatas';
                        } else {
                            $status = 'banyak';
                        }
                    }
                }

                return [
                    'id'            => $sub->park_subarea_id,
                    'name'          => $sub->park_subarea_name,
                    'polygon'       => $sub->park_subarea_polygon, 
                    'status'        => $status, 
                    'amenities'     => $sub->parkAmenity->pluck('park_amenity_name'),
                    'comment_count' => $sub->subarea_comment_count ?? 0, 
                ];
            });

            $data = [
                'area_id'   => $area->park_area_id,
                'area_name' => $area->park_area_name,
                'area_code' => $area->park_area_code, // Pastikan area code terkirim
                'subareas'  => $formattedSubareas
            ];

            return $this->sendSuccess('Data visualisasi berhasil diambil.', $data);

        } catch (\Exception $e) {
            return $this->sendError('Gagal memuat visualisasi: ' . $e->getMessage(), 500);
        }
    }
}