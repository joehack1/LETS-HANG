<?php

use App\Http\Controllers\Api\V1\PaymentController;
use App\Http\Controllers\Api\V1\PlanController;
use Illuminate\Support\Facades\Route;

Route::get('/plans', [PlanController::class, 'index']);

Route::post('/payments/mpesa/callback', [PaymentController::class, 'mpesaCallback']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/checkout', [PaymentController::class, 'checkout']);
    Route::get('/payments/{id}', [PaymentController::class, 'show']);
    Route::get('/subscription/status', [PaymentController::class, 'status']);
});
