<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use Illuminate\Foundation\Configuration\Middleware;

Route::get('/', function () {
    return view('test');
});

Route::middleware('guest')->group(function () {
    // Rute Login
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.show');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');

    // Rute Registrasi
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register.show');
    Route::post('register', [AuthController::class, 'register'])->name('register.attempt');

    // Rute Verifikasi Register OTP
    Route::get('register-otp-verification', [AuthController::class, 'showRegisterOtpForm'])->name('otp_register.show');
    Route::post('register-otp-verification', [AuthController::class, 'verifyRegisterOtp'])->name('otp_register.verify');

    // Rute Forgot Password
    Route::get('forgot-pass', [AuthController::class, 'showForgotPasswordForm'])->name('forgot_pass.show');
    Route::post('forgot-pass', [AuthController::class, 'sendResetOtp'])->name('forgot_pass.otp');

    Route::post('resend-otp' ,[AuthController::class, 'resendOtp'])->name('resendOtp');
});

Route::middleware(['auth', 'setDBConnByRole'])->group(function () {
    // Rute Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rute Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('admin')->as('admin.')->middleware(['can:access-admin-features'])->group(function () {
        // placeholder
    });
});