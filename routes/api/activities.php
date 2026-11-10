<?php

use App\Http\Controllers\Api\V1\ActivityController;
use App\Models\ActivityCategory;
use Illuminate\Support\Facades\Route;

Route::get('/activity-categories', function () {
    return response()->json(['data' => ActivityCategory::orderBy('name')->get()]);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/activities', [ActivityController::class, 'index']);
    Route::post('/activities', [ActivityController::class, 'store']);
    Route::get('/activities/{public_id}', [ActivityController::class, 'show']);
    Route::put('/activities/{public_id}', [ActivityController::class, 'update']);
    Route::delete('/activities/{public_id}', [ActivityController::class, 'destroy']);

    Route::post('/activities/{public_id}/join', [ActivityController::class, 'join']);
    Route::post('/activities/{public_id}/leave', [ActivityController::class, 'leave']);
    Route::get('/activities/{public_id}/participants', [ActivityController::class, 'participants']);
    Route::post('/activities/{public_id}/participants/{user_id}/approve', [ActivityController::class, 'approve']);
    Route::post('/activities/{public_id}/participants/{user_id}/reject', [ActivityController::class, 'reject']);
    Route::post('/activities/{public_id}/participants/{user_id}/attendance', [ActivityController::class, 'markAttendance']);

    Route::post('/activities/{public_id}/save', [ActivityController::class, 'save']);
    Route::delete('/activities/{public_id}/save', [ActivityController::class, 'unsave']);
});
