<?php

use App\Http\Controllers\Api\V1\InterestController;
use App\Http\Controllers\Api\V1\ProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::post('/profile/photos', [ProfileController::class, 'uploadPhoto']);
    Route::delete('/profile/photos/{id}', [ProfileController::class, 'deletePhoto']);
    Route::put('/profile/preferences', [ProfileController::class, 'updatePreferences']);
});

Route::get('/interests', [InterestController::class, 'index']);
