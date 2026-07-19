<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\ParkSubareaHistory;
use App\Models\User;
use App\Models\UserReward;
use App\Models\UserValidation;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the dashboard index page with summary statistics.
     */
    public function index(): View
    {
        $user = Auth::user();

        // 1. Summary Cards Data
        $totalUsers = User::whereNotNull('email_verified_at')->where('role', 'user')->count();
        $totalParkAreas = ParkArea::count();
        $totalSubareas = ParkSubarea::count();
        $pendingRewards = UserReward::where('user_reward_status', 'pending')->count();

        // Load filter utility for view
        $parkAreas = ParkArea::select('park_area_id', 'park_area_name')->get();

        return view('Contents.Dashboard.index', compact(
            'user',
            'totalUsers',
            'totalParkAreas',
            'totalSubareas',
            'pendingRewards',
            'parkAreas'
        ));
    }

    /**
     * Fetch chart data for User Validation frequency.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getChartData(Request $request): JsonResponse
    {
        try {
            $filterType = $request->input('filter_type', 'tanggal');
            $labels = collect();
            $dateFormat = '%Y-%m-%d';
            $from = null;
            $to = null;

            $dateFormat = '%Y-%m-%d';
            $from = null;
            $to = null;

            switch ($filterType) {
                case 'tanggal':
                    $dateFormat = '%Y-%m-%d';
                    $fromStr = $request->input('date_from', Carbon::now()->subDays(30)->toDateString());
                    $toStr = $request->input('date_to', $fromStr);
                    $from = Carbon::parse($fromStr)->startOfDay();
                    $to = Carbon::parse($toStr)->endOfDay();
                    break;

                case 'bulan':
                    $dateFormat = '%Y-%u'; // ISO Week
                    $fromStr = $request->input('month_from', Carbon::now()->format('Y-m'));
                    $toStr = $request->input('month_to', $fromStr);
                    $from = Carbon::parse($fromStr . '-01')->startOfMonth();
                    $to = Carbon::parse($toStr . '-01')->endOfMonth();
                    break;

                case 'tahun':
                    $dateFormat = '%Y-%m';
                    $fromYear = $request->input('year_from', Carbon::now()->year);
                    $toYear = $request->input('year_to', $fromYear);
                    $from = Carbon::createFromDate($fromYear, 1, 1)->startOfYear();
                    $to = Carbon::createFromDate($toYear, 12, 31)->endOfYear();
                    break;
            }

            // 2. Query Aggregate Data (Group by HOUR AND Status)
            $queryBuilder = UserValidation::join('park_subareas', 'user_validations.park_subarea_id', '=', 'park_subareas.park_subarea_id')
                ->join('park_areas', 'park_subareas.park_area_id', '=', 'park_areas.park_area_id')
                ->select(
                    DB::raw("HOUR(user_validations.created_at) as hour"),
                    'user_validations.user_validation_content as status',
                    DB::raw('count(*) as aggregate')
                )
                ->whereBetween('user_validations.created_at', [$from, $to]);

            $areaId = $request->input('area_id');
            if ($areaId && $areaId !== 'all') {
                $queryBuilder->where('park_areas.park_area_id', $areaId);
            }

            $query = $queryBuilder->groupBy('hour', 'status')
                ->get();

            // 3. Process Data for Chart.js
            // Generate labels for 24 hours
            $labels = collect();
            for ($hour = 0; $hour < 24; $hour++) {
                $labels->push(sprintf('%02d:00', $hour));
            }

            // Group by Status to build datasets
            $statuses = ['banyak', 'terbatas', 'penuh'];
            $statusLabels = [
                'banyak' => 'Banyak Tersedia',
                'terbatas' => 'Terbatas',
                'penuh' => 'Penuh',
            ];
            $colors = [
                'banyak' => '#28a745',
                'terbatas' => '#ffc107',
                'penuh' => '#dc3545',
            ];
            $groupedByStatus = $query->groupBy('status');
            $datasets = [];

            foreach ($statuses as $status) {
                $items = $groupedByStatus->get($status, collect());
                // Map data to the 24 hours labels
                $dataPoints = collect();
                for ($hour = 0; $hour < 24; $hour++) {
                    $record = $items->firstWhere('hour', $hour);
                    $dataPoints->push($record ? $record->aggregate : 0);
                }

                $datasets[] = [
                    'label' => $statusLabels[$status],
                    'data' => $dataPoints,
                    'borderColor' => $colors[$status],
                    'backgroundColor' => $colors[$status],
                    'borderWidth' => 1,
                ];
            }

            return response()->json([
                'labels' => $labels,
                'datasets' => $datasets,
                'filter_type' => $filterType,
            ]);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'Failed to load chart data'], 500);
        }
    }

    /**
     * Fetch data for Automatic Detection Availability chart.
     * Menampilkan rata-rata slot tersedia per jam dengan warna berdasarkan status
     * dan mengacu pada data histori ketersediaan parkir yang diambil setiap 20 menit.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDetectionChartData(Request $request): JsonResponse
    {
        try {
            $filterType = $request->input('filter_type', 'minggu');
            $from = null;
            $to = null;

            switch ($filterType) {
                case 'minggu':
                    $weekStr = $request->input('week_from', Carbon::now()->startOfWeek()->format('Y-m-d'));
                    if (preg_match('/(\d{4})-W(\d{2})/', $weekStr, $m)) {
                        $from = Carbon::now()->setISODate((int)$m[1], (int)$m[2])->startOfWeek();
                    } else {
                        $from = Carbon::parse($weekStr)->startOfWeek();
                    }
                    $to = $from->copy()->endOfWeek();
                    break;

                case 'tanggal':
                default:
                    $fromStr = $request->input('date_from', Carbon::now()->toDateString());
                    $toStr = $request->input('date_to', $fromStr);
                    $from = Carbon::parse($fromStr)->startOfDay();
                    $to = Carbon::parse($toStr)->endOfDay();
                    break;
            }

            $isWeekly = ($filterType === 'minggu');
            $timeColumn = $isWeekly
                ? DB::raw("DAYOFWEEK(park_subarea_histories.created_at) as day_num")
                : DB::raw("HOUR(park_subarea_histories.created_at) as hour");

            $queryBuilder = ParkSubareaHistory::join('park_subareas', 'park_subarea_histories.park_subarea_id', '=', 'park_subareas.park_subarea_id')
                ->join('park_areas', 'park_subareas.park_area_id', '=', 'park_areas.park_area_id')
                ->select(
                    $timeColumn,
                    'park_subarea_histories.status',
                    'park_subarea_histories.current_count',
                    'park_subarea_histories.max_slots'
                )
                ->whereBetween('park_subarea_histories.created_at', [$from, $to]);

            $areaId = $request->input('area_id');
            if ($areaId && $areaId !== 'all') {
                $queryBuilder->where('park_areas.park_area_id', $areaId);
            }

            $records = $queryBuilder->get();

            $labels = collect();
            $dataPoints = collect();
            $backgroundColors = collect();
            $drillDates = [];

            $colors = [
                'banyak' => '#28a745',
                'terbatas' => '#ffc107',
                'penuh' => '#dc3545',
            ];

            if ($isWeekly) {
                // DAYOFWEEK: 1=Sun, 2=Mon, ..., 7=Sat → map to Mon=0 .. Sun=6
                $dayLabels = ['Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu', 'Minggu'];
                $dayToIndex = [2 => 0, 3 => 1, 4 => 2, 5 => 3, 6 => 4, 7 => 5, 1 => 6];

                for ($i = 0; $i < 7; $i++) {
                    $labels->push($dayLabels[$i]);
                    $drillDates[] = $from->copy()->addDays($i)->format('Y-m-d');

                    $targetDayNum = array_search($i, $dayToIndex);
                    $dayRecords = $records->filter(fn($r) => $r->day_num == $targetDayNum);

                    if ($dayRecords->isNotEmpty()) {
                        $totalAvailable = $dayRecords->sum(fn($r) => max(0, $r->max_slots - $r->current_count));
                        $avgAvailable = $totalAvailable / $dayRecords->count();

                        $statusCounts = $dayRecords->countBy('status');
                        $majorityStatus = $statusCounts->keys()->first();
                        $maxCount = 0;
                        foreach ($statusCounts as $status => $count) {
                            if ($count > $maxCount) {
                                $maxCount = $count;
                                $majorityStatus = $status;
                            }
                        }

                        $dataPoints->push(round($avgAvailable, 1));
                        $backgroundColors->push($colors[$majorityStatus] ?? '#e8e8e8');
                    } else {
                        $dataPoints->push(0);
                        $backgroundColors->push('#e8e8e8');
                    }
                }
            } else {
                // Hourly mode (daily drill-down)
                for ($hour = 0; $hour < 24; $hour++) {
                    $labels->push(sprintf('%02d:00', $hour));

                    $hourRecords = $records->filter(fn($r) => $r->hour == $hour);

                    if ($hourRecords->isNotEmpty()) {
                        $totalAvailable = $hourRecords->sum(fn($r) => max(0, $r->max_slots - $r->current_count));
                        $avgAvailable = $totalAvailable / $hourRecords->count();

                        $statusCounts = $hourRecords->countBy('status');
                        $majorityStatus = $statusCounts->keys()->first();
                        $maxCount = 0;
                        foreach ($statusCounts as $status => $count) {
                            if ($count > $maxCount) {
                                $maxCount = $count;
                                $majorityStatus = $status;
                            }
                        }

                        $dataPoints->push(round($avgAvailable, 1));
                        $backgroundColors->push($colors[$majorityStatus] ?? '#e8e8e8');
                    } else {
                        $dataPoints->push(0);
                        $backgroundColors->push('#e8e8e8');
                    }
                }
            }

            $response = [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Rata-rata Slot Tersedia',
                    'data' => $dataPoints,
                    'backgroundColor' => $backgroundColors,
                    'borderColor' => $backgroundColors,
                    'borderWidth' => 1,
                ]],
                'filter_type' => $filterType,
            ];

            if ($isWeekly) {
                $response['drill_dates'] = $drillDates;
            }

            return response()->json($response);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'Failed to load detection chart data'], 500);
        }
    }

    /**
     * Fetch top users leaderboard based on lifetime points.
     */
    public function getLeaderboard(): JsonResponse
    {
        try {
            $leaders = User::select('user_id', 'name', 'avatar', 'lifetime_points') // id is mapped to user_id usually, verify model
                ->where('role', 'user')
                ->whereNotNull('email_verified_at')
                ->orderBy('lifetime_points', 'desc')
                ->take(10)
                ->get()
                ->map(function ($user) {
                    $user->avatar = $user->avatar ? asset('storage/'.$user->avatar) : null;

                    return $user;
                });

            return response()->json($leaders);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'Failed to load leaderboard'], 500);
        }
    }

    /**
     * Fetch realtime validation logs with optional area filtering.
     */
    public function getRealtimeValidations(Request $request): JsonResponse
    {
        try {
            $areaId = $request->input('area_id');

            $query = UserValidation::with(['user:user_id,name,avatar', 'parkSubarea:park_subarea_id,park_subarea_name,park_area_id'])
                ->latest()
                ->take(20);

            if ($areaId && $areaId != 'all') {
                $query->whereHas('parkSubarea', function ($q) use ($areaId) {
                    $q->where('park_area_id', $areaId);
                });
            }

            $validations = $query->get()->map(function ($v) {
                return [
                    'avatar' => $v->user->avatar ? asset('storage/'.$v->user->avatar) : null,
                    'username' => $v->user->name ?? 'Unknown',
                    'status' => $v->user_validation_content, // banyak, terbatas, penuh
                    'area' => $v->parkSubarea->parkArea->park_area_name ?? '-',
                    'subarea' => $v->parkSubarea->park_subarea_name ?? '-',
                    'time' => $v->created_at->diffForHumans(),
                    'timestamp' => $v->created_at->format('d M Y H:i'),
                ];
            });

            return response()->json($validations);

        } catch (Exception $e) {
            Log::error($e->getMessage());

            return response()->json(['error' => 'Failed to load realtime data'], 500);
        }
    }
}
