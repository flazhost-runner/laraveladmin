<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\app\Http\Controllers\Web\V1\ProfileController;

Route::middleware(['web', 'auth.app'])->prefix('admin/v1')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('admin.v1.profile.index');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('admin.v1.profile.update');
    Route::put('/profile/change-password', [ProfileController::class, 'changePassword'])->name('admin.v1.profile.change_password');
});
