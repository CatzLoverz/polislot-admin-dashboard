<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\ProfileController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\InfoBoardController;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Controllers\Web\ParkAreaController;
use App\Http\Controllers\Web\TierController;
use App\Http\Controllers\Web\FeedbackCategoryController;
use App\Http\Controllers\Web\FeedbackController;
use App\Http\Controllers\Web\MissionController;
use App\Http\Controllers\Web\RewardController;
use App\Http\Controllers\Web\RewardVerificationController;

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

Route::middleware(['auth', 'role:admin,user'])->group(function () {
    // Rute Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rute Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::prefix('admin/')->as('admin.')->middleware(['role:admin'])->group(function () {
        
        Route::resource('park', ParkAreaController::class);
        // Route info_board
        Route::resource('info-board', InfoBoardController::class)->only(['index', 'store', 'update', 'destroy']);

        // Route Feedback (Masukan dan Saran)
        Route::Resource('feedback-category', FeedbackCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::Resource('feedback', FeedbackController::class)->only(['index', 'store', 'update', 'destroy']);

        // Route Mission
        Route::Resource('missions', MissionController::class)->only(['index', 'store', 'update', 'destroy']);

        // Route Reward
        Route::resource('rewards', RewardController::class)->only(['index', 'store', 'update', 'destroy']);

        // Route Reward Verification
        Route::prefix('rewards/verify')->as('rewards.verify.')->controller(RewardVerificationController::class)->group(function() {
            Route::get('/', 'index')->name('index');       // Halaman antrian
            Route::post('/{id}', 'process')->name('process'); // Proses ACC/Reject
        });

    });

});