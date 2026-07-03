<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\app\Http\Controllers\Api\V1\SettingApiController;

Route::middleware(['auth.app'])->prefix('api/v1')->group(function () {
    Route::get('/setting', [SettingApiController::class, 'index'])->name('api.v1.setting.index');
});
