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
    // Rute Registrasi
    Route::get('register', [AuthController::class, 'showRegisterForm'])->name('register.show');
    Route::post('register', [AuthController::class, 'register'])->name('register.attempt');

    // Rute Login
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login.show');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');

    // Rute Verifikasi OTP
    Route::get('otp-verification', [AuthController::class, 'showOtpForm'])->name('otp.show');
    Route::post('otp-verification', [AuthController::class, 'verifyOtp'])->name('otp.verify');
});

Route::middleware(['auth', 'setDBConnByRole', 'forcePasswordChange'])->group(function () {
    // Rute Logout
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Rute Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Rute Profil
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware(['can:access-admin-features'])->group(function () {
        // placeholder
    });
});