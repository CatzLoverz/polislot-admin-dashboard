<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller; // Menggunakan Base Controller Anda
use App\Models\UserHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class HistoryController extends Controller
{
    /**
     * Menampilkan riwayat aktivitas poin user.
     * * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $limit = $request->input('limit', 20); // Default 20 item per page

            // Ambil data history dengan pagination
            $histories = UserHistory::where('user_id', $user->user_id)
                ->orderBy('created_at', 'desc')
                ->paginate($limit);

            // Format data agar rapi di frontend
            // getCollection() digunakan karena kita memodifikasi item di dalam paginator
            $formattedData = $histories->getCollection()->map(function ($item) {
                return [
                    'id'          => $item->user_history_id,
                    'type'        => $item->user_history_type, // mission, validation, redeem
                    'title'       => $item->user_history_name,
                    'points'      => $item->user_history_points, // Bisa null
                    'is_negative' => (bool) $item->user_history_is_negative,
                    'date'        => $item->created_at->format('d M Y'),
                    'time'        => $item->created_at->format('H:i'),
                    'timestamp'   => $item->created_at->toIso8601String(),
                ];
            });

            // Susun response data termasuk meta pagination
            $responseData = [
                'list' => $formattedData,
                'pagination' => [
                    'current_page' => $histories->currentPage(),
                    'last_page'    => $histories->lastPage(),
                    'per_page'     => $histories->perPage(),
                    'total'        => $histories->total(),
                ]
            ];

            return $this->sendSuccess('Riwayat aktivitas berhasil diambil.', $responseData);

        } catch (\Exception $e) {
            Log::error('[API HistoryController@index] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return $this->sendError('Gagal memuat riwayat: ' . $e->getMessage(), 500);
        }
    }
}