<?php

use App\Http\Controllers\Api\V1\CommunityController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/communities', [CommunityController::class, 'index']);
    Route::post('/communities', [CommunityController::class, 'store']);
    Route::get('/communities/{slug}', [CommunityController::class, 'show']);
    Route::put('/communities/{slug}', [CommunityController::class, 'update']);
    Route::delete('/communities/{slug}', [CommunityController::class, 'destroy']);

    Route::post('/communities/{slug}/join', [CommunityController::class, 'join']);
    Route::post('/communities/{slug}/leave', [CommunityController::class, 'leave']);
    Route::get('/communities/{slug}/members', [CommunityController::class, 'members']);
    Route::post('/communities/{slug}/members/{user_id}/approve', [CommunityController::class, 'approveMember']);
    Route::put('/communities/{slug}/members/{user_id}/role', [CommunityController::class, 'updateMemberRole']);

    Route::get('/communities/{slug}/posts', [CommunityController::class, 'posts']);
    Route::post('/communities/{slug}/posts', [CommunityController::class, 'createPost']);
    Route::delete('/communities/{slug}/posts/{post_id}', [CommunityController::class, 'deletePost']);
    Route::post('/communities/{slug}/posts/{post_id}/comments', [CommunityController::class, 'createComment']);

    Route::get('/communities/{slug}/activities', [CommunityController::class, 'communityActivities']);
});
