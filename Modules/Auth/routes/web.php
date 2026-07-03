<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\app\Http\Controllers\Web\V1\AuthController;

Route::middleware('web')->group(function () {
    Route::get('/auth/login', [AuthController::class, 'loginForm'])->name('web.auth.login');
    Route::post('/auth/login', [AuthController::class, 'login'])->name('web.auth.login.post');
    Route::get('/auth/register', [AuthController::class, 'registerForm'])->name('web.auth.register');
    Route::post('/auth/register', [AuthController::class, 'register'])->name('web.auth.register.post');
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('web.auth.logout');

    Route::get('/admin/v1/auth/reset/req', [AuthController::class, 'resetReqForm'])->name('admin.v1.auth.reset.req');
    Route::post('/admin/v1/auth/reset/request', [AuthController::class, 'requestOtp'])->name('admin.v1.auth.reset.request');
    Route::get('/admin/v1/auth/reset/proc', [AuthController::class, 'resetProcForm'])->name('admin.v1.auth.reset.proc');
    Route::post('/admin/v1/auth/reset/process', [AuthController::class, 'processOtp'])->name('admin.v1.auth.reset.process');
});
