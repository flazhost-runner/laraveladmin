<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\app\Http\Controllers\Web\V1\MediaController;

Route::middleware(['web', 'auth.app', 'authorize'])
    ->prefix('admin/v1/media')
    ->group(function () {
        Route::get('list', [MediaController::class, 'list'])
            ->name('admin.v1.media.list');

        Route::post('upload', [MediaController::class, 'upload'])
            ->name('admin.v1.media.upload');

        Route::post('delete', [MediaController::class, 'delete'])
            ->name('admin.v1.media.delete');
    });
