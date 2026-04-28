<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LocationTrackingController;

// Location Tracking Routes
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('location-tracking')->group(function () {
        Route::post('/start', [LocationTrackingController::class, 'startTracking']);
        Route::post('/update', [LocationTrackingController::class, 'updateLocation']);
        Route::post('/stop', [LocationTrackingController::class, 'stopTracking']);
        Route::get('/pickup/{pickupRequestId}', [LocationTrackingController::class, 'getLocations']);
        Route::get('/history/{pickupRequestId}', [LocationTrackingController::class, 'getTrackingHistory']);
    });
});