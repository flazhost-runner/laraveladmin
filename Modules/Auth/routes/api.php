<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\Api\V1\AuthController;

Route::prefix('api/v1/auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api.v1.auth.login');
    Route::post('/register', [AuthController::class, 'register'])->name('api.v1.auth.register');
    Route::middleware('auth.app')->group(function () {
        Route::get('/me', [AuthController::class, 'me'])->name('api.v1.auth.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.v1.auth.logout');
    });
});
