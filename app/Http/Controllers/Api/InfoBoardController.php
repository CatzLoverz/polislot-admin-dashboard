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

    // =========================================================================
    // 🛠️ HELPER FUNCTIONS
    // =========================================================================

    /**
     * Format response sukses standar.
     */
    private function sendSuccess($message, $data = null, $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Format response error standar.
     */
    private function sendError($message, $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Format objek InfoBoard agar output API konsisten.
     */
    private function formatInfoBoard($infoBoard): array
    {
        return [
            'judul'   => $infoBoard->info_title ?? 'Pengumuman',
            'isi'     => $infoBoard->info_content ?? 'Tidak ada detail',
            'tanggal' => $infoBoard->updated_at ? $infoBoard->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }
}