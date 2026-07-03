<?php

use Illuminate\Support\Facades\Route;
use Modules\Access\app\Http\Controllers\Api\V1\PermissionController;
use Modules\Access\app\Http\Controllers\Api\V1\RoleController;
use Modules\Access\app\Http\Controllers\Api\V1\UserController;

Route::middleware(['auth.app'])->prefix('api/v1/access')->group(function () {
    // User
    Route::get('/user', [UserController::class, 'index'])->name('api.v1.access.user.index');
    Route::post('/user/store', [UserController::class, 'store'])->name('api.v1.access.user.store');
    Route::get('/user/{id}', [UserController::class, 'show'])->name('api.v1.access.user.show');
    Route::put('/user/{id}/update', [UserController::class, 'update'])->name('api.v1.access.user.update');
    Route::delete('/user/{id}/delete', [UserController::class, 'delete'])->name('api.v1.access.user.delete');
    Route::post('/user/delete_selected', [UserController::class, 'deleteSelected'])->name('api.v1.access.user.delete_selected');

    // Role
    Route::get('/role', [RoleController::class, 'index'])->name('api.v1.access.role.index');
    Route::post('/role/store', [RoleController::class, 'store'])->name('api.v1.access.role.store');
    Route::get('/role/{id}', [RoleController::class, 'show'])->name('api.v1.access.role.show');
    Route::put('/role/{id}/update', [RoleController::class, 'update'])->name('api.v1.access.role.update');
    Route::delete('/role/{id}/delete', [RoleController::class, 'delete'])->name('api.v1.access.role.delete');
    Route::post('/role/delete_selected', [RoleController::class, 'deleteSelected'])->name('api.v1.access.role.delete_selected');

    // Permission
    Route::get('/permission', [PermissionController::class, 'index'])->name('api.v1.access.permission.index');
    Route::post('/permission/store', [PermissionController::class, 'store'])->name('api.v1.access.permission.store');
    Route::get('/permission/{id}', [PermissionController::class, 'show'])->name('api.v1.access.permission.show');
    Route::put('/permission/{id}/update', [PermissionController::class, 'update'])->name('api.v1.access.permission.update');
    Route::delete('/permission/{id}/delete', [PermissionController::class, 'delete'])->name('api.v1.access.permission.delete');
    Route::post('/permission/delete_selected', [PermissionController::class, 'deleteSelected'])->name('api.v1.access.permission.delete_selected');
    Route::post('/permission/sync', [PermissionController::class, 'syncFromRoutes'])->name('api.v1.access.permission.sync');
});
