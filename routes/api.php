<?php

use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/profile.php';
    require __DIR__.'/api/people.php';
    require __DIR__.'/api/connections.php';
    require __DIR__.'/api/activities.php';
    require __DIR__.'/api/communities.php';
    require __DIR__.'/api/messaging.php';
    require __DIR__.'/api/safety.php';
    require __DIR__.'/api/admin.php';
});
