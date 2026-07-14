<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedbackCategory;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class FeedbackCategoryController extends Controller
{
    /**
     * Ambil semua kategori feedback untuk dropdown.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Ambil id dan nama kategori saja
            $categories = FeedbackCategory::select('fbk_category_id', 'fbk_category_name')->orderBy('created_at', 'desc')->get();

            return $this->sendSuccess('Data kategori berhasil diambil.', $categories);
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return $this->sendError('Gagal mengambil kategori.', 500);
        }
    }
}
