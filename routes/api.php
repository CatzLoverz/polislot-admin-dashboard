<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;

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

Route::middleware('setDBConnByRole', 'auth:sanctum')->group(function () {
    // Route Logout (Protected, untuk mencabut token)
    Route::post('/logout', [AuthController::class, 'logout']);
    // Route Profil
    Route::get('/profile', [ProfileController::class, 'show']); 
    Route::put('/profile', [ProfileController::class, 'update']);
    
    // placeholder
});
