<?php

use App\Http\Controllers\Api\V1\BlockController;
use App\Http\Controllers\Api\V1\ConnectionController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/connections', [ConnectionController::class, 'listConnections']);
    Route::delete('/connections/{id}', [ConnectionController::class, 'remove']);

    Route::get('/connections/requests', [ConnectionController::class, 'index']);
    Route::post('/connections/requests', [ConnectionController::class, 'store']);
    Route::post('/connections/requests/{id}/accept', [ConnectionController::class, 'accept']);
    Route::post('/connections/requests/{id}/decline', [ConnectionController::class, 'decline']);
    Route::post('/connections/requests/{id}/cancel', [ConnectionController::class, 'cancel']);

    Route::get('/blocks', [BlockController::class, 'index']);
    Route::post('/blocks', [BlockController::class, 'store']);
    Route::delete('/blocks/{id}', [BlockController::class, 'destroy']);
});
