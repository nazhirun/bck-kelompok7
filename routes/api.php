<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BarangController;
use App\Http\Controllers\API\PenjualanController;

// Rute untuk autentikasi untuk fluter
Route::prefix('auth')->group(function () {
    // Rute publik (tidak perlu autentikasi)
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('resend-otp', [AuthController::class, 'resendOtp']);

    // Rute untuk reset password
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);

    // Rute yang memerlukan autentikasi
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('profile', [AuthController::class, 'profile']);
    });
});

// Rute bawaan yang memerlukan autentikasi
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Route untuk API Barang
// PERHATIAN: Urutan rute penting! Rute spesifik harus ditempatkan sebelum rute dengan parameter
Route::get('/barang/image/{filename}', [BarangController::class, 'showImage']);
Route::get('/barang', [BarangController::class, 'index']);
Route::get('/barang/{id}', [BarangController::class, 'show']);

// Route untuk API Penjualan
Route::get('/penjualan/faktur/{faktur}', [PenjualanController::class, 'getByFaktur']);
Route::apiResource('penjualan', PenjualanController::class);