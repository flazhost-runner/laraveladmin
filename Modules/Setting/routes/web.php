<?php

use Illuminate\Support\Facades\Route;
use Modules\Setting\app\Http\Controllers\Web\V1\SettingController;

Route::middleware(['web', 'auth.app', 'authorize'])->group(function () {
    Route::get('/admin/v1/setting',
        [SettingController::class, 'index']
    )->name('admin.v1.setting.index');

    Route::put('/admin/v1/setting/update',
        [SettingController::class, 'update']
    )->name('admin.v1.setting.update');

    Route::get('/admin/v1/setting/fe-preview/{slug}',
        [SettingController::class, 'fePreview']
    )->name('admin.v1.setting.fe_preview')
        ->where('slug', '[a-z0-9-]+');
});
