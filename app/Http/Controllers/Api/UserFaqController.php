<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserFaq;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class UserFaqController extends Controller
{
    /**
     * Ambil semua data FAQ dari yang terbaru.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $faqs = UserFaq::orderBy('created_at', 'desc')->get();

            if ($faqs->isEmpty()) {
                return $this->sendSuccess('Tidak ada FAQ tersedia.', []);
            }

            // Format data (Mapping collection)
            $formattedData = $faqs->map(function ($item) {
                return [
                    'faq_id'       => $item->faq_id,
                    'faq_question' => $item->faq_question,
                    'faq_answer'   => $item->faq_answer,
                    'created_at'   => $item->created_at,
                    'updated_at'   => $item->updated_at,
                ];
            });

            return $this->sendSuccess('Data FAQ berhasil diambil.', $formattedData, 200);

        } catch (\Exception $e) {
            Log::error('[API UserFaqController@index] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            return $this->sendError('Terjadi kesalahan saat mengambil data FAQ.', 500);
        }
    }
}