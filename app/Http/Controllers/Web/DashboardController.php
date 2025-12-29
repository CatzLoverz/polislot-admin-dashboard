<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ParkArea;
use App\Models\ParkSubarea;
use App\Models\User;
use App\Models\UserReward;
use App\Models\UserValidation;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    /**
     * Display the dashboard index page with summary statistics.
     *
     * @return \Illuminate\View\View
     */
    public function index()
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChartData(Request $request)
    {
        try {
            $period = $request->input('period', 'day'); // day, week, month

            // 1. Determine Date Grouping Format
            if ($period === 'month') {
                $dateFormat = '%Y-%m'; 
            } elseif ($period === 'week') {
                $dateFormat = '%Y-%u';
            } else {
                $dateFormat = '%Y-%m-%d';
            }

            // 2. Query Aggregate Data (Group by Date AND Area)
            $query = UserValidation::join('park_subareas', 'user_validations.park_subarea_id', '=', 'park_subareas.park_subarea_id')
                ->join('park_areas', 'park_subareas.park_area_id', '=', 'park_areas.park_area_id')
                ->select(
                    DB::raw("DATE_FORMAT(user_validations.created_at, '$dateFormat') as date"),
                    'park_areas.park_area_name as area_name',
                    DB::raw('count(*) as aggregate')
                )
                // Filter time range (optional optimization, e.g. last 30 days)
                ->when($period == 'day', function($q) {
                    $q->where('user_validations.created_at', '>=', Carbon::now()->subDays(30));
                })
                ->when($period == 'week', function($q) {
                    $q->where('user_validations.created_at', '>=', Carbon::now()->subWeeks(12));
                })
                ->when($period == 'month', function($q) {
                    $q->where('user_validations.created_at', '>=', Carbon::now()->subMonths(12));
                })
                ->groupBy('date', 'area_name')
                ->orderBy('date', 'asc')
                ->get();
            
            // 3. Process Data for Chart.js
            // Get unique labels (dates) for the X-axis
            $labels = $query->pluck('date')->unique()->values();

            // Group by Area to build datasets
            $groupedByArea = $query->groupBy('area_name');
            $datasets = [];

            // Pre-defined colors for lines (can be expanded)
            $colors = ['#1d7af3', '#f3545d', '#59d05d', '#ffad46', '#6861ce', '#f0f0f0'];
            $colorIndex = 0;

            foreach ($groupedByArea as $areaName => $items) {
                // Map data to the master labels to ensure alignment (fill 0 if missing)
                $dataPoints = $labels->map(function($date) use ($items) {
                    $record = $items->firstWhere('date', $date);
                    return $record ? $record->aggregate : 0;
                });

                $datasets[] = [
                    'label' => $areaName,
                    'data' => $dataPoints,
                    'borderColor' => $colors[$colorIndex % count($colors)],
                    'backgroundColor' => 'transparent',
                    'borderWidth' => 2,
                    'pointBorderColor' => "#FFF",
                    'pointBackgroundColor' => $colors[$colorIndex % count($colors)],
                    'pointRadius' => 4,
                    'fill' => false,
                ];
                $colorIndex++;
            }

            return response()->json([
                'labels' => $labels,
                'datasets' => $datasets,
                'period' => $period
            ]);

        } catch (\Exception $e) {
            Log::error('[WEB DashboardController@getChartData] Error: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to load chart data'], 500);
        }
    }

    /**
     * Fetch top users leaderboard based on lifetime points.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLeaderboard()
    {
        try {
            $leaders = User::select('user_id', 'name', 'avatar', 'lifetime_points') // id is mapped to user_id usually, verify model
                ->whereNotNull('email_verified_at')
                ->orderBy('lifetime_points', 'desc')
                ->take(10)
                ->get()
                ->map(function ($user) {
                    $user->avatar = $user->avatar ? asset('storage/'.$user->avatar) : null;

                    return $user;
                });

            return response()->json($leaders);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load leaderboard'], 500);
        }
    }

    /**
     * Fetch realtime validation logs with optional area filtering.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getRealtimeValidations(Request $request)
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
                    'avatar' => $v->user->avatar ? asset('storage/' . $v->user->avatar) : null,
                    'username' => $v->user->name ?? 'Unknown',
                    'status' => $v->user_validation_content, // banyak, terbatas, penuh
                    'area' => $v->parkSubarea->parkArea->park_area_name ?? '-',
                    'subarea' => $v->parkSubarea->park_subarea_name ?? '-',
                    'time' => $v->created_at->diffForHumans(),
                    'timestamp' => $v->created_at->format('d M Y H:i'),
                ];
            });

            return response()->json($validations);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to load realtime data'], 500);
        }
    }
}
