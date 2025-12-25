<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
use App\Models\UserValidation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

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
     * @param Request $request
     * @param int $id (park_area_id)
     * @return JsonResponse
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
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
                        // 1. Hitung Vote per Status
                        $counts = $validVotes->countBy('user_validation_content');
                        
                        // 2. Cari Nilai Vote Tertinggi (Mayoritas)
                        $maxVote = $counts->max();

                        // 3. Cari Status apa saja yang punya nilai Max tersebut (Bisa lebih dari 1 jika seri)
                        $candidates = $counts->keys()->filter(function($key) use ($counts, $maxVote) {
                            return $counts[$key] === $maxVote;
                        });

                        // 4. Penentuan Pemenang
                        if ($candidates->count() === 1) {
                            // Jika TIDAK seri, ambil langsung pemenangnya
                            $status = $candidates->first();
                        } else {
                            // Jika SERI (Tie), ambil status dari vote TERBARU di antara kandidat yang seri
                            // Karena $validVotes sudah urut descending (terbaru diatas), kita loop cari yang pertama ketemu
                            $latestDecider = $validVotes->first(function ($vote) use ($candidates) {
                                return $candidates->contains($vote->user_validation_content);
                            });
                            
                            if ($latestDecider) {
                                $status = $latestDecider->user_validation_content;
                            }
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

            // Hitung Cooldown User
            $canValidate = true;
            $waitMinutes = 0;

            if ($user) {
                $lastValidation = UserValidation::where('user_id', $user->user_id)
                    ->latest()
                    ->first();

                if ($lastValidation) {
                    $diffInMinutes = Carbon::parse($lastValidation->created_at)->diffInMinutes(now());
                    if ($diffInMinutes < 15) {
                        $canValidate = false;
                        $waitMinutes = 15 - $diffInMinutes;
                    }
                }
            }

            $data = [
                'area_id'   => $area->park_area_id,
                'area_name' => $area->park_area_name,
                'area_code' => $area->park_area_code,
                'validation_cooldown' => [
                    'can_validate' => $canValidate,
                    'wait_minutes' => $waitMinutes,
                ],
                'subareas'  => $formattedSubareas
            ];

            return $this->sendSuccess('Data visualisasi berhasil diambil.', $data);

        } catch (\Exception $e) {
            Log::error('[API MapVisualizationController@show] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return $this->sendError('Gagal memuat visualisasi: ' . $e->getMessage(), 500);
        }
    }
}