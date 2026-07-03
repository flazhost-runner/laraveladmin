<?php

use Illuminate\Support\Facades\Route;
use Modules\Profile\app\Http\Controllers\Api\V1\ProfileApiController;

Route::middleware(['auth.app'])->prefix('api/v1')->group(function () {
    Route::get('/profile', [ProfileApiController::class, 'index'])->name('api.v1.profile.index');
});
