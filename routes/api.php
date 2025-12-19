<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\RewardController;
use App\Http\Controllers\Api\HistoryController;
use App\Http\Controllers\Api\MissionController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FeedbackController;
use App\Http\Controllers\Api\InfoBoardController;
use App\Http\Controllers\Api\SubareaCommentController;
use App\Http\Controllers\Api\UserValidationController;
use App\Http\Controllers\Api\FeedbackCategoryController;
use App\Http\Controllers\Api\MapVisualizationController;

Route::middleware('encryptApi')->group(function () {

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
});

    Route::middleware(['auth:sanctum', 'role:admin,user', 'encryptApi'])->group(function () {

        // Auth Check
        Route::get('/user', [AuthController::class, 'authCheck']); 

        // Route Logout (Protected, untuk mencabut token)
        Route::post('/logout', [AuthController::class, 'logout']);

        // Route Profil
        Route::get('/profile', [ProfileController::class, 'show']); 
        Route::match(['put', 'post'], '/profile', [ProfileController::class, 'update']);

        // Route InfoBoard
        Route::get('/info-board', [InfoBoardController::class, 'index']);

        // Route Mission & Leaderboard
        Route::get('/missions', [MissionController::class, 'index']);
        
        // Route Masukan dan Saran
        Route::get('/feedback-categories', [FeedbackCategoryController::class, 'index']);
        Route::post('/feedback', [FeedbackController::class, 'store']);
        
        // Route rewards
        Route::get('/rewards', [RewardController::class, 'index']);
        Route::post('/rewards/redeem', [RewardController::class, 'redeem']);
        Route::get('/rewards/history', [RewardController::class, 'history']);

        // Route history
        Route::get('/history', [HistoryController::class, 'index']);

        // Route Visualisasi Parkir
        Route::get('/map-visualization/', [MapVisualizationController::class, 'index']);
        Route::get('/map-visualization/{id}', [MapVisualizationController::class, 'show']);

        // Route Validasi Parkir
        Route::post('/validation', [UserValidationController::class, 'store']);

        // Route Komentar Subarea Parkir
        Route::apiResource('comment', SubareaCommentController::class)->only('index', 'store', 'destroy');
        Route::match(['put', 'post'], '/comment/{comment}', [SubareaCommentController::class, 'update']);
    });
