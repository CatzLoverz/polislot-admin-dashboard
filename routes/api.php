<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;

Route::get('/user', function (Request $request) {
    return $request->user();
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
    
    // placeholder
});
