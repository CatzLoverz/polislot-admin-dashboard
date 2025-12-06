<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\JsonResponse;
use Exception;

class FeedbackController extends Controller
{
    /**
     * Mempproses penyimpanan feedback baru dari pengguna.
     * * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            return DB::transaction(function () use ($request) {
                // 1. Validasi Input
                $validatedData = $request->validate([
                    'category'      => 'required|exists:feedback_categories,fbk_category_id',
                    'title'         => 'required|string|max:255',
                    'description'   => 'nullable|string',
                ]);

                $user = Auth::user();

                // 2. Simpan Feedback ke Database
                // Menggunakan strip_tags untuk sanitasi input string dasar
                $feedback = Feedback::create([
                    'fbk_category_id'      => $validatedData['category'],
                    'feedback_title'       => strip_tags($validatedData['title']),
                    'feedback_description' => strip_tags($validatedData['description'] ?? ''),
                ]);

                Log::info('[API FeedbackController@store] Sukses: Feedback tersimpan.');

                // 3. Kembalikan Response Sukses
                return $this->sendSuccess('Feedback berhasil dikirim. Terima kasih atas masukan Anda!', [
                    'feedback_id' => $feedback->feedback_id
                ], 201);
            });

        } catch (ValidationException $e) {
            Log::warning('[API FeedbackController@store] Gagal: Validasi error.', ['errors' => $e->errors()]);
            // Menggunakan helper dari Base Controller
            return $this->sendValidationError($e);

        } catch (Exception $e) {
            Log::error('[API FeedbackController@store] Gagal: Error sistem.', ['error' => $e->getMessage()]);
            // Menggunakan helper dari Base Controller
            return $this->sendError('Terjadi kesalahan saat mengirim feedback.', 500);
        }
    }
}