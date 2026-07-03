<?php

use Illuminate\Support\Facades\Route;
use Modules\Access\app\Http\Controllers\Web\V1\PermissionController;
use Modules\Access\app\Http\Controllers\Web\V1\RoleController;
use Modules\Access\app\Http\Controllers\Web\V1\RolePermissionController;
use Modules\Access\app\Http\Controllers\Web\V1\UserController;

Route::middleware(['web', 'auth.app', 'authorize'])->prefix('admin/v1/access')->group(function () {
    // User
    Route::get('/user', [UserController::class, 'index'])->name('admin.v1.access.user.index');
    Route::get('/user/create', [UserController::class, 'create'])->name('admin.v1.access.user.create');
    Route::post('/user/store', [UserController::class, 'store'])->name('admin.v1.access.user.store');
    Route::get('/user/{id}/edit', [UserController::class, 'edit'])->name('admin.v1.access.user.edit');
    Route::put('/user/{id}/update', [UserController::class, 'update'])->name('admin.v1.access.user.update');
    Route::delete('/user/{id}/delete', [UserController::class, 'delete'])->name('admin.v1.access.user.delete');
    Route::post('/user/delete_selected', [UserController::class, 'deleteSelected'])->name('admin.v1.access.user.delete_selected');

    // Role
    Route::get('/role', [RoleController::class, 'index'])->name('admin.v1.access.role.index');
    Route::get('/role/create', [RoleController::class, 'create'])->name('admin.v1.access.role.create');
    Route::post('/role/store', [RoleController::class, 'store'])->name('admin.v1.access.role.store');
    Route::get('/role/{id}/edit', [RoleController::class, 'edit'])->name('admin.v1.access.role.edit');
    Route::put('/role/{id}/update', [RoleController::class, 'update'])->name('admin.v1.access.role.update');
    Route::delete('/role/{id}/delete', [RoleController::class, 'delete'])->name('admin.v1.access.role.delete');
    Route::post('/role/delete_selected', [RoleController::class, 'deleteSelected'])->name('admin.v1.access.role.delete_selected');

    // Role <-> Permission
    Route::get('/role/{id}/permission', [RolePermissionController::class, 'index'])->name('admin.v1.access.role.permission');
    Route::get('/role/{id}/permission/{permission_id}/assign', [RolePermissionController::class, 'assign'])->name('admin.v1.access.role.permission.assign');
    Route::post('/role/{id}/permission/assign_selected', [RolePermissionController::class, 'assignSelected'])->name('admin.v1.access.role.permission.assign_selected');
    Route::get('/role/{id}/permission/{permission_id}/unassign', [RolePermissionController::class, 'unassign'])->name('admin.v1.access.role.permission.unassign');
    Route::post('/role/{id}/permission/unassign_selected', [RolePermissionController::class, 'unassignSelected'])->name('admin.v1.access.role.permission.unassign_selected');

    // Permission
    Route::get('/permission', [PermissionController::class, 'index'])->name('admin.v1.access.permission.index');
    Route::get('/permission/create', [PermissionController::class, 'create'])->name('admin.v1.access.permission.create');
    Route::post('/permission/store', [PermissionController::class, 'store'])->name('admin.v1.access.permission.store');
    Route::get('/permission/{id}/edit', [PermissionController::class, 'edit'])->name('admin.v1.access.permission.edit');
    Route::put('/permission/{id}/update', [PermissionController::class, 'update'])->name('admin.v1.access.permission.update');
    Route::delete('/permission/{id}/delete', [PermissionController::class, 'delete'])->name('admin.v1.access.permission.delete');
    Route::post('/permission/delete_selected', [PermissionController::class, 'deleteSelected'])->name('admin.v1.access.permission.delete_selected');
    Route::post('/permission/sync', [PermissionController::class, 'syncFromRoutes'])->name('admin.v1.access.permission.sync');
});
