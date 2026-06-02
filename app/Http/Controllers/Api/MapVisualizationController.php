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
            $areas = ParkArea::withCount('parkSubarea')->orderBy('park_area_name', 'asc')->get();

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
                $status = 'netral';
                $isValidated = false;
                $hasUserReport = false;

                // 1. Filter validations within the last 5 minutes from now
                $allValidations = $sub->userValidation;
                $cutoffTime = now()->subMinutes(5);
                $validVotes = $allValidations->filter(function ($val) use ($cutoffTime) {
                    return $val->created_at >= $cutoffTime;
                });

                // 2. Determine CV Status
                $hasOnlineIot = false;
                $cvStatus = 'netral';
                $occupancy = 0.0;

                if ($sub->iotDevice) {
                    $mac = $sub->iotDevice->device_mac_address;
                    $deviceStatus = \Illuminate\Support\Facades\Cache::get("iot_status_{$mac}", 'offline');
                    if ($deviceStatus === 'online') {
                        $hasOnlineIot = true;
                        if ($sub->max_slots > 0) {
                            $occupancy = ($sub->current_count / $sub->max_slots) * 100;
                            if ($occupancy < ($sub->threshold_banyak ?? 30.0)) {
                                $cvStatus = 'banyak';
                            } elseif ($occupancy >= ($sub->threshold_terbatas ?? 80.0)) {
                                $cvStatus = 'penuh';
                            } else {
                                $cvStatus = 'terbatas';
                            }
                        } else {
                            $cvStatus = 'banyak';
                        }
                    }
                }

                // 3. Process votes to determine status
                if ($validVotes->isNotEmpty()) {
                    $counts = $validVotes->countBy('user_validation_content');
                    $maxVote = $counts->max();
                    $candidates = $counts->keys()->filter(function($key) use ($counts, $maxVote) {
                        return $counts[$key] === $maxVote;
                    });

                    $votedStatus = 'banyak';
                    if ($candidates->count() === 1) {
                        $votedStatus = $candidates->first();
                    } else {
                        // Tie-breaker: latest vote from candidates
                        $latestDecider = $validVotes->first(function ($vote) use ($candidates) {
                            return $candidates->contains($vote->user_validation_content);
                        });
                        if ($latestDecider) {
                            $votedStatus = $latestDecider->user_validation_content;
                        }
                    }

                    $status = $votedStatus;

                    if ($hasOnlineIot) {
                        if ($votedStatus === $cvStatus) {
                            $isValidated = true;
                        } else {
                            $hasUserReport = true;
                        }
                    }
                } else {
                    // Fallback to CV status if online, otherwise netral
                    if ($hasOnlineIot) {
                        $status = $cvStatus;
                    } else {
                        $status = 'netral';
                    }
                }

                return [
                    'id'              => $sub->park_subarea_id,
                    'name'            => $sub->park_subarea_name,
                    'polygon'         => $sub->park_subarea_polygon, 
                    'status'          => $status, 
                    'is_validated'    => $isValidated,
                    'has_user_report' => $hasUserReport,
                    'current_count'   => $sub->current_count ?? 0,
                    'max_slots'       => $sub->max_slots ?? 0,
                    'amenities'       => $sub->parkAmenity->pluck('park_amenity_name'),
                    'comment_count'   => $sub->subarea_comment_count ?? 0, 
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