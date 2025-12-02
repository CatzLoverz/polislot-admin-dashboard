<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InfoBoard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class InfoBoardController extends Controller
{
    /**
     * Ambil 1 data info board terbaru.
     * * @return JsonResponse
     */
    public function showLatest(): JsonResponse
    {
        try {
            // Mengambil data terakhir berdasarkan updated_at paling baru
            $latest = InfoBoard::orderBy('updated_at', 'desc')->first();

            if (!$latest) {
                return $this->sendSuccess('Tidak ada pengumuman tersedia.', null);
            }

            // Format data menggunakan helper
            $formattedData = $this->formatInfoBoard($latest);
            Log::info('[API InfoBoardController@showLatest] Sukses: Data info board diambil.');
            return $this->sendSuccess('Data info board terbaru berhasil diambil.', $formattedData);

        } catch (\Exception $e) {
            Log::error('[API InfoBoardController@showLatest] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan saat mengambil info board.', 500);
        }
    }
}