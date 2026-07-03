<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth.app', 'authorize'])->prefix('admin/v1')->group(function () {
    Route::get('/components', fn () => view('components-module::be.default.index'))->name('admin.v1.components.index');
});
