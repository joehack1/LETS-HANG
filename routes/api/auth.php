<?php

use App\Http\Controllers\Api\V1\Auth\EmailVerificationController;
use App\Http\Controllers\Api\V1\Auth\LoginController;
use App\Http\Controllers\Api\V1\Auth\LogoutController;
use App\Http\Controllers\Api\V1\Auth\MeController;
use App\Http\Controllers\Api\V1\Auth\PasswordResetController;
use App\Http\Controllers\Api\V1\Auth\PhoneVerificationController;
use App\Http\Controllers\Api\V1\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

Route::post('/register', RegisterController::class)->middleware('throttle:10,1');
Route::post('/login', LoginController::class)->middleware('throttle:10,1');
Route::post('/forgot-password', [PasswordResetController::class, 'forgot'])->middleware('throttle:5,1');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1');

Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', LogoutController::class);
    Route::get('/me', MeController::class);

    Route::post('/email/verification-notification', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::post('/phone/send-otp', [PhoneVerificationController::class, 'send'])
        ->middleware('throttle:6,1');
    Route::post('/phone/verify-otp', [PhoneVerificationController::class, 'verify'])
        ->middleware('throttle:6,1');
});
