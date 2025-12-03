<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InfoBoardController;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Controllers\Web\ParkAreaController;
use App\Http\Controllers\Web\TierController;
use App\Http\Controllers\Web\RewardController;
use App\Http\Controllers\Web\RewardVerificationController;
use App\Http\Controllers\Web\FeedbackController;
use App\Http\Controllers\Web\MissionController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    // Rute Login
    Route::get('/login-form', [AuthController::class, 'loginForm'])->name('login.form');
    Route::post('/login-attempt', [AuthController::class, 'login'])->name('login.attempt');

    // Rute Forgot Password
    // Form & pengiriman OTP forgot password
    Route::get('/forgot-form', [AuthController::class, 'forgotPasswordForm'])->name('forgot.form');
    Route::post('/forgot-attempt', [AuthController::class, 'forgotPasswordVerify'])->name('forgot.attempt');

    // Form verifikasi OTP forgot password
    Route::get('/forgot-otp-form', [AuthController::class, 'forgotPasswordOtpForm'])->name('forgot_otp.form');
    Route::post('/forgot-otp-verify', [AuthController::class, 'forgotPasswordOtpVerify'])->name('forgot_otp.verify');
    Route::post('/forgot-otp-resend', [AuthController::class, 'forgotPasswordOtpResend'])->name('forgot_otp.resend');

    // Form & pengiriman reset password 
    Route::get('/reset-pass-form', [AuthController::class, 'resetPasswordForm'])->name('reset_pass.form');
    Route::post('/reset-pass-attempt', [AuthController::class, 'resetPassword'])->name('reset_pass.attempt');
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
        Route::resource('info-board', InfoBoardController::class)->except(['create', 'show', 'edit']);
        // Route tiers
        Route::resource('tiers', TierController::class)->except(['show']);
        // Route Reward
        Route::resource('rewards', RewardController::class)->except(['show']);
         // Route Verifikasi Kode Reward
        Route::prefix('reward-verification')->as('reward_verification.')->group(function () {
            Route::get('/', [RewardVerificationController::class, 'index'])->name('index');
            Route::post('/{userReward}/verify', [RewardVerificationController::class, 'verify'])->name('verify');
            Route::post('/search', [RewardVerificationController::class, 'search'])->name('search');
        });
        // Route Feedback(Masukan dan Saran)
        Route::Resource('feedback', FeedbackController::class)->except('show');
        // Route Missions
        Route::Resource('missions', MissionController::class)->except('show');
    });
});