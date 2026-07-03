<?php

use Illuminate\Support\Facades\Route;
use Modules\Home\app\Http\Controllers\Web\V1\HomeController;

Route::middleware('web')->group(function () {
    Route::get('/', [HomeController::class, 'root'])->name('web.home.root');
    Route::get('/home', [HomeController::class, 'index'])->name('web.home.index');
});
