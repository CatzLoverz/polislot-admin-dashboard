<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FeedbackCategory;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class FeedbackCategoryController extends Controller
{
    /**
     * Ambil semua kategori feedback untuk dropdown.
     * * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            // Ambil id dan nama kategori saja
            $categories = FeedbackCategory::select('fbk_category_id', 'fbk_category_name')->get();
            
            return $this->sendSuccess('Data kategori berhasil diambil.', $categories);
        } catch (\Exception $e) {
            Log::error('[API FeedbackCategoryController@index] Gagal: ' . $e->getMessage());
            return $this->sendError('Gagal mengambil kategori.', 500);
        }
    }
}