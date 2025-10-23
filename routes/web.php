<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\Admin\InfoBoardController;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Controllers\Web\Admin\ParkAreaController;
use App\Http\Controllers\Web\Admin\TierController;


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
    Route::post('resend-register-otp', [AuthController::class, 'resendOtp'])->name('resendRegisterOtp');

    // Rute Forgot Password
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password/send-otp', [AuthController::class, 'sendResetOtp'])->name('password.send.otp');
    Route::get('/password/verify-otp', [AuthController::class, 'showVerifyResetOtpForm'])->name('password.otp.verify.show');
    Route::post('/password/verify-otp', [AuthController::class, 'verifyResetOtp'])->name('password.otp.verify');
    Route::get('/reset-password', [AuthController::class, 'showResetPasswordForm'])->name('password.reset.show');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.reset.submit');
    Route::post('/resend-reset-otp', [AuthController::class, 'resendResetOtp'])->name('resendResetOtp');
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
        
        Route::resource('park', ParkAreaController::class);
        // Route info_board
        Route::resource('info_board', InfoBoardController::class)->except(['show']);
        // Route tiers
        Route::resource('tiers', TierController::class)->except(['show']);
    });
});