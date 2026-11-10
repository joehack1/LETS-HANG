<?php

use App\Http\Controllers\Api\V1\PeopleController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/people', [PeopleController::class, 'index']);
    Route::get('/people/{public_id}', [PeopleController::class, 'show']);
});
