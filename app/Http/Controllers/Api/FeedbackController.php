<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PointService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

class FeedbackController extends Controller
{
    protected $pointService;

    public function __construct(PointService $pointService)
    {
        $this->pointService = $pointService;
    }

    /**
     * Simpan feedback baru dari pengguna.
     */
    public function store(Request $request)
{
    try {
        $request->validate([
            'category' => 'required|string|max:255',
            'feedback_type' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        // 🔐 SECURITY: Batasi 1 feedback per hari
        $alreadySent = DB::table('feedback')
            ->where('user_id', Auth::id())
            ->whereDate('created_at', now()->toDateString())
            ->exists();

        if ($alreadySent) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu sudah mengirim feedback hari ini. Coba lagi besok ya!',
            ], 429); // Too Many Requests
        }

        DB::beginTransaction();

        $cleanDescription = strip_tags($request->description);
        $cleanTitle = strip_tags($request->title);

        DB::table('feedback')->insert([
            'user_id' => Auth::id(),
            'category' => strip_tags($request->category),
            'feedback_type' => strip_tags($request->feedback_type),
            'title' => $cleanTitle,
            'description' => $cleanDescription,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pointResult = $this->pointService->addPoints(
            Auth::id(),
            'FEEDBACK_SUBMIT',
            'Poin dari feedback: ' . $request->title
        );

        DB::commit();

        $message = 'Feedback berhasil dikirim.';
        if ($pointResult && $pointResult['points'] > 0) {
            $message = "Feedback berhasil dikirim! Kamu mendapatkan {$pointResult['points']} poin.";
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'points_earned' => $pointResult['points'] ?? 0,
        ], 201);

    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Data tidak valid.',
            'errors' => $e->errors(),
        ], 422);

    } catch (Exception $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Terjadi kesalahan saat mengirim feedback.',
        ], 500);
    }
}
}