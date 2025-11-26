<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Exception;

class InfoBoardController extends Controller
{
    /**
     * Ambil 1 data info board terbaru 
     */
    public function showLatest(): JsonResponse
    {
        try {

            $user = Auth::user();
            Log::info('[API InfoBoardController@showLatest] Mengambil data info_board terbaru.', [
                'user_id' => $user->user_id ?? null
            ]);

            // Pastikan nama tabel benar: info_board atau info_boards ?
            $latest = DB::table('info_board')
                ->orderByDesc(DB::raw('COALESCE(updated_at, created_at)'))
                ->first();

            if (!$latest) {
                Log::warning('[API InfoBoardController@showLatest] Tidak ada data info board');

                return response()->json([
                    'status' => 'success',
                    'message' => 'Tidak ada pengumuman tersedia.',
                    'data' => null
                ], 200);
            }

            // Format data
            $data = [
    'judul' => $latest->title ?? 'Pengumuman',
    'isi' => $latest->content ?? 'Tidak ada detail',
    'tanggal' => ($latest->updated_at ?? $latest->created_at) ?? date('Y-m-d'),
];


            Log::info('[API InfoBoardController@showLatest] Berhasil mengambil data', [
                'data' => $data
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Data info board terbaru berhasil diambil.',
                'data' => $data
            ], 200);

        } catch (Exception $e) {

            Log::error('[API InfoBoardController@showLatest] Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat mengambil info board.',
                'data' => null,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
