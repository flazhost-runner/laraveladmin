<?php

use Illuminate\Support\Facades\Route;
use Modules\Dashboard\app\Http\Controllers\Web\V1\DashboardController;

Route::middleware(['web', 'auth.app'])->prefix('admin/v1')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.v1.dashboard.index');
});
