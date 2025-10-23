<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


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
Route::post('/password/send-reset-otp', [AuthController::class, 'sendResetOtp'])->name('password.send_otp');
// Mengirim ulang Kode OTP 
Route::post('/password/resend-reset-otp', [AuthController::class, 'resendResetOtp'])->name('password.resend_otp');
//  Memverifikasi Kode OTP yang dikirim pengguna
Route::post('/password/verify-reset-otp', [AuthController::class, 'verifyResetOtp'])->name('password.verify_otp');
//  Mengatur ulang password baru (setelah OTP diverifikasi)
Route::post('/password/reset', [AuthController::class, 'resetPassword'])->name('password.reset');

Route::middleware('setDBConnByRole', 'auth:sanctum')->group(function () {
    // Route Logout (Protected, untuk mencabut token)
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // placeholder
});
