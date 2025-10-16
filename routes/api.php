<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
Route::post('/register-otp-verification', [AuthController::class, 'verifyRegisterOtp'])->name('otp_register.verify'); 

Route::middleware('setDBConnByRole', 'auth:sanctum')->group(function () {
    // placeholder
});
