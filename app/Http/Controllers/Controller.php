<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    /**
     * Format sukses standar: { status: 'success', message: '...', data: { ... } }
     */
    protected function sendSuccess($message, $data = null, $code = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Format error standar: { status: 'error', message: '...', data: null }
     */
    protected function sendError($message, $code = 400, $data = null, $errorCode = null): JsonResponse
    {
        $response = [
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ];

        if ($errorCode) {
            $response['code'] = $errorCode;
        }

        return response()->json($response, $code);
    }

    /**
     * Handle error validasi Laravel agar pesannya ramah di Frontend
     */
    protected function sendValidationError(ValidationException $e): JsonResponse
    {
        $firstError = collect($e->errors())->flatten()->first();
        
        return response()->json([
            'status' => 'error',
            'message' => $firstError,
            'data' => null,
            'errors' => $e->errors()
        ], 422);
    }

    /**
     * Format User Data Konsisten
     */
    protected function formatUser($user): array
    {
        return [
            'user_id' => (int) $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'avatar' => $user->avatar,
            // Tambahkan field lain jika perlu
        ];
    }
}