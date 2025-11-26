<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\InfoBoardController;
use App\Http\Controllers\Api\UserTierController;
use App\Http\Controllers\Api\UserRewardController;
use App\Http\Controllers\Api\FeedbackController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route Registrasi
Route::post('/register', [AuthController::class, 'register']);
//  Memverifikasi Kode OTP yang dikirim pengguna
Route::post('/verify-register-otp', [AuthController::class, 'verifyRegisterOtp']);
// Mengirim ulang Kode OTP 
Route::post('/resend-register-otp', [AuthController::class, 'resendRegisterOtp']);

// Route Login
Route::post('/login', [AuthController::class, 'login']);
// Mengirim Kode OTP ke email pengguna
Route::post('/password/send-reset-otp', [AuthController::class, 'sendResetOtp']);
// Mengirim ulang Kode OTP 
Route::post('/password/resend-reset-otp', [AuthController::class, 'resendResetOtp']);
//  Memverifikasi Kode OTP yang dikirim pengguna
Route::post('/password/verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
//  Mengatur ulang password baru (setelah OTP diverifikasi)
Route::post('/password/reset', [AuthController::class, 'resetPassword']);

Route::middleware('setDBConnByRole', 'auth:sanctum', 'throttle:100,1')->group(function () {
    // Route Logout (Protected, untuk mencabut token)
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route Profil
    Route::get('/profile', [ProfileController::class, 'show']); 
    Route::put('/profile', [ProfileController::class, 'update'])
    ->middleware('throttle:10,1');
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
});
