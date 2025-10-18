<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route Registrasi
Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
Route::post('/verify-register-otp', [AuthController::class, 'verifyRegisterOtp'])->name('otp_register.verify'); 
Route::post('/resend-register-otp', [AuthController::class, 'resendRegisterOtp'])->name('otp_register.resend');

// Route Login
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::middleware('setDBConnByRole', 'auth:sanctum')->group(function () {
    // Route Logout (Protected, untuk mencabut token)
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    // placeholder
});
