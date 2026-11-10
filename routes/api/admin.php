<?php

use App\Http\Controllers\Api\V1\Admin\ActivityController as AdminActivityController;
use App\Http\Controllers\Api\V1\Admin\CommunityController as AdminCommunityController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\ReportController as AdminReportController;
use App\Http\Controllers\Api\V1\Admin\UserController as AdminUserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'role:admin,moderator'])->prefix('admin')->group(function () {
    Route::get('/dashboard', DashboardController::class);

    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}/status', [AdminUserController::class, 'updateStatus']);
    Route::put('/users/{id}/role', [AdminUserController::class, 'updateRole'])->middleware('role:admin');

    Route::get('/activities', [AdminActivityController::class, 'index']);
    Route::put('/activities/{public_id}/status', [AdminActivityController::class, 'updateStatus']);

    Route::get('/communities', [AdminCommunityController::class, 'index']);
    Route::delete('/communities/{slug}', [AdminCommunityController::class, 'suspend']);

    Route::get('/reports', [AdminReportController::class, 'index']);
    Route::put('/reports/{id}', [AdminReportController::class, 'resolve']);
});
