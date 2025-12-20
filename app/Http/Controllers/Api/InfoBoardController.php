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
     * Ambil data info board dari yang terbaru.
     * * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Ambil semua data, urutkan dari yang paling baru dibuat
            $infoBoards = InfoBoard::orderBy('created_at', 'desc')->get();

            if ($infoBoards->isEmpty()) {
                return $this->sendSuccess('Tidak ada pengumuman tersedia.', []);
            }

            // Format data (Mapping collection)
            $formattedData = $infoBoards->map(function ($item) {
                return [
                    'info_id'      => $item->info_id,
                    'info_title'   => $item->info_title,
                    'info_content' => $item->info_content,
                    'created_at'   => $item->created_at,
                    'updated_at'   => $item->updated_at,
                ];
            });
            
            return $this->sendSuccess('Data info board berhasil diambil.', $formattedData, 200);

        } catch (\Exception $e) {
            Log::error('[API InfoBoardController@index] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan saat mengambil info board.', 500);
        }
    }
}