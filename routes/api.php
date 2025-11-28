<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\InfoBoardController;
use App\Http\Controllers\Api\UserTierController;
use App\Http\Controllers\Api\UserRewardController;
use App\Http\Controllers\Api\FeedbackController;



Route::middleware('encryptApi')->group(function () {
    // Auth Check
    Route::get('/user', function (Request $request) {
        $user = $request->user();
        return response()->json([
            'status' => 'success',
            'message' => 'Data profil berhasil diambil.',
            'data' => [
                'user_id' => (int) $user->user_id,
                'name' => $user->name,
                'email' => $user->email,
                'email_verified_at' => $user->email_verified_at,
                'role' => $user->role,
                'avatar' => $user->avatar,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ]
        ]);
    })->middleware('auth:sanctum');

    // Rute Login
    Route::post('/login-attempt', [AuthController::class, 'login']);

    // Rute Registrasi
    Route::post('/register-attempt', [AuthController::class, 'register']);
    Route::post('/register-otp-verify', [AuthController::class, 'registerOtpVerify']);
    Route::post('/register-otp-resend', [AuthController::class, 'registerOtpResend']);

    // Rute Forgot Password
    Route::post('/forgot-attempt', [AuthController::class, 'forgotPasswordVerify']);
    Route::post('/forgot-otp-verify', [AuthController::class, 'forgotPasswordOtpVerify']);
    Route::post('/forgot-otp-resend', [AuthController::class, 'forgotPasswordOtpResend']);
    Route::post('/reset-pass-attempt', [AuthController::class, 'resetPassword']);

    Route::middleware('setDBConnByRole', 'auth:sanctum')->group(function () {
        // Route Logout (Protected, untuk mencabut token)
        Route::post('/logout', [AuthController::class, 'logout']);
        // Route Profil
        Route::get('/profile', [ProfileController::class, 'show']); 
        Route::put('/profile', [ProfileController::class, 'update']);
        // Route InfoBoard
        Route::get('/info-board/latest', [InfoBoardController::class, 'showLatest']);
        // Route Tiers
        Route::get('/user/tier', [UserTierController::class, 'show']);
        Route::post('/user/tier/update', [UserTierController::class, 'updateTier'])
        ->middleware('throttle:5,1');
        // Route Leadboard
        Route::get('/user/leaderboard', [UserTierController::class, 'leaderboard']);
        // Route Masukan dan Saran
        Route::post('/user/feedback', [FeedbackController::class, 'store'])
        ->middleware('throttle:3,1');
        // Route Reward dan Riwayat Penukaran
        Route::prefix('rewards')->group(function () {
            // Katalog reward
            Route::get('/', [UserRewardController::class, 'index']);
            // Tukar reward (generate voucher code)
            Route::post('/exchange', [UserRewardController::class, 'exchange'])
            ->middleware('throttle:3,1');
            // Riwayat reward user
            Route::get('/my-rewards', [UserRewardController::class, 'myRewards']);
            // Cek detail voucher
            Route::post('/check-voucher', [UserRewardController::class, 'checkVoucher']);
        });
        // placeholder
    });
});