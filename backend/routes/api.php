<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaundryController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\PickupController;
use App\Http\Controllers\Api\ServiceController;

use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\PaymentProofController;
use App\Http\Controllers\Api\CustomerRatingController;
use App\Http\Controllers\Api\BranchRatingController;
use App\Http\Controllers\Api\PaymentMethodController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\LocationTrackingController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\ServiceStatusController;
use App\Http\Controllers\Api\InventoryController as ApiInventoryController;
use App\Http\Controllers\Branch\NotificationController as BranchNotificationController;
use App\Http\Controllers\Api\AdminNotificationController;
use App\Http\Controllers\Api\VehicleController;
use App\Http\Controllers\Api\TrafficController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Admin Dashboard API Routes (no v1 prefix)
Route::get('/api/vehicles/profiles', [VehicleController::class, 'profiles']);
Route::get('/api/traffic/current', [TrafficController::class, 'current']);

Route::prefix('v1')->group(function () {

    // ========================================
    // PUBLIC ROUTES (No Authentication Required)
    // ========================================

    // Authentication (Rate Limited)
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->middleware('throttle:3,1');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->middleware('throttle:3,1');

    // Branches (Public)
    Route::prefix('branches')->group(function () {
        Route::get('/', [BranchController::class, 'index']);
        Route::get('/nearest', [BranchController::class, 'nearest']);
        Route::get('/{id}', [BranchController::class, 'show']);
        Route::get('/{branch}/operating-hours', [BranchController::class, 'operatingHours']);
    });

    // Customer's Registered Branch (Requires Authentication) - Must be outside branches prefix to avoid route conflicts
    Route::middleware('auth:sanctum')->get('/my-branch', [BranchController::class, 'getMyBranch']);

    // Services (Public)
    Route::get('/services', [ServiceController::class, 'index']);

    // Service Status (Public)
    Route::get('/service-status', [ServiceStatusController::class, 'index']);
    Route::get('/service-config', [ServiceStatusController::class, 'getServiceConfig']);
    Route::post('/check-delivery-fee', [ServiceStatusController::class, 'checkDeliveryFee']);
    Route::get('/service-areas/{branchId}', [ServiceStatusController::class, 'getServiceAreas']);

    // Inventory (Public)
    Route::prefix('inventory')->group(function () {
        Route::get('/', [ApiInventoryController::class, 'index']);
        Route::get('/low-stock', [ApiInventoryController::class, 'lowStock']);
        Route::get('/out-of-stock', [ApiInventoryController::class, 'outOfStock']);
        Route::get('/stats', [ApiInventoryController::class, 'stats']);
        Route::get('/branches/{branch}', [ApiInventoryController::class, 'byBranch']);
        Route::get('/{item}', [ApiInventoryController::class, 'show']);
        Route::post('/check-stock', [ApiInventoryController::class, 'checkStock']);
    });

    // Promotions (Public)
    Route::prefix('promotions')->group(function () {
        Route::get('/', [PromotionController::class, 'index']);
        Route::get('/featured', [PromotionController::class, 'featured']);
        Route::get('/validate-code', [PromotionController::class, 'validateCode']);
        Route::get('/applicable', [PromotionController::class, 'applicable']);
        Route::get('/{id}', [PromotionController::class, 'show']);
    });

    // Public Ratings/Feedbacks
    Route::get('/ratings/public', [CustomerRatingController::class, 'publicRatings']);

    // ========================================
    // PROTECTED ROUTES (Require Authentication)
    // ========================================

    Route::middleware('auth:sanctum')->group(function () {

        // Authentication & Profile
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);
        Route::get('/profile', [CustomerController::class, 'getProfile']);
        Route::put('/profile', [CustomerController::class, 'updateProfile']);
        Route::put('/password', [AuthController::class, 'changePassword']);
        Route::delete('/account', [AuthController::class, 'deleteAccount']);

        // 2FA Management
        Route::post('/2fa/enable', [AuthController::class, 'enable2FA']);
        Route::post('/2fa/disable', [AuthController::class, 'disable2FA']);

        // Customer Statistics & Info
        Route::prefix('customer')->group(function () {
            Route::get('/stats', [CustomerController::class, 'getStats']);
            Route::get('/active-laundries', [CustomerController::class, 'getActiveLaundries']);
            Route::get('/latest-pickup', [CustomerController::class, 'getLatestPickup']);

            // Push Notification Token & Preferences
            Route::post('/fcm-token', [CustomerController::class, 'updateFcmToken']);
            Route::put('/notification-preferences', [CustomerController::class, 'updateNotificationPreferences']);

            // Payment Proofs (GCash)
    Route::get('/gcash/qr/{branchId?}', [PaymentProofController::class, 'getGCashQR']);
    Route::post('/laundries/{laundry}/payment-proof', [PaymentProofController::class, 'store'])->middleware('throttle:5,1'); // 5 uploads per minute
    Route::get('/laundries/{laundry}/payment-proof', [PaymentProofController::class, 'show']);

    // Customer Ratings (Laundry)
            Route::get('/ratings', [CustomerRatingController::class, 'index']);
            Route::post('/ratings', [CustomerRatingController::class, 'store']);
            Route::get('/ratings/check/{laundryId}', [CustomerRatingController::class, 'check']);
            Route::put('/ratings/{id}', [CustomerRatingController::class, 'update']);
            Route::delete('/ratings/{id}', [CustomerRatingController::class, 'destroy']);
            Route::get('/unrated-laundries', [CustomerRatingController::class, 'unratedLaundries']);

            // Branch Ratings (separate from laundry ratings)
            Route::post('/branch-ratings', [CustomerRatingController::class, 'storeBranchRating']);

            // Branch Ratings
            Route::get('/branches', [BranchRatingController::class, 'branches']);
            Route::get('/branch-ratings', [BranchRatingController::class, 'index']);
            Route::post('/branch-ratings', [BranchRatingController::class, 'store']);
            Route::get('/branch-ratings/stats', [BranchRatingController::class, 'stats']);
        });

        // Payment Methods
        Route::prefix('payment-methods')->group(function () {
            Route::get('/', [PaymentMethodController::class, 'index']);
            Route::post('/', [PaymentMethodController::class, 'store']);
            Route::put('/{id}', [PaymentMethodController::class, 'update']);
            Route::delete('/{id}', [PaymentMethodController::class, 'destroy']);
            Route::post('/{id}/set-default', [PaymentMethodController::class, 'setDefault']);
        });

        // Saved Addresses
        Route::prefix('addresses')->group(function () {
            Route::get('/', [AddressController::class, 'index']);
            Route::post('/', [AddressController::class, 'store']);
            Route::put('/{id}', [AddressController::class, 'update']);
            Route::delete('/{id}', [AddressController::class, 'destroy']);
            Route::post('/{id}/set-default', [AddressController::class, 'setDefault']);
        });

        // Laundries
        Route::prefix('laundries')->group(function () {
            Route::get('/', [LaundryController::class, 'index']);
            Route::post('/', [LaundryController::class, 'store']);
            Route::get('/{id}', [LaundryController::class, 'show']);
            Route::get('/{id}/receipt', [LaundryController::class, 'receipt']);
            Route::put('/{id}/cancel', [LaundryController::class, 'cancel']);
            Route::post('/{id}/payment-method', [LaundryController::class, 'setPaymentMethod']);
        });

        // Pickup Requests (Customer)
        Route::prefix('pickups')->group(function () {
            Route::get('/', [PickupController::class, 'index']);
            Route::post('/', [PickupController::class, 'store'])->middleware('throttle:10,1'); // 10 pickups per minute
            Route::get('/{id}', [PickupController::class, 'show']);
            Route::put('/{id}/cancel', [PickupController::class, 'cancel']);
        });

        // Location Tracking
        Route::prefix('location-tracking')->group(function () {
            Route::post('/start', [LocationTrackingController::class, 'startTracking']);
            Route::post('/update', [LocationTrackingController::class, 'updateLocation']);
            Route::post('/stop', [LocationTrackingController::class, 'stopTracking']);
            Route::get('/pickup/{pickupRequestId}', [LocationTrackingController::class, 'getLocations']);
            Route::get('/history/{pickupRequestId}', [LocationTrackingController::class, 'getTrackingHistory']);
        });

        // Dashboard Real-time Data
        Route::prefix('dashboard')->group(function () {
            Route::get('/stats', [DashboardController::class, 'getStats']);
            Route::get('/recent-pickups', [DashboardController::class, 'getRecentPickups']);
            Route::get('/active-orders', [DashboardController::class, 'getActiveOrders']);
            Route::get('/daily-stats', [DashboardController::class, 'getDailyStats']);
            Route::get('/data', [DashboardController::class, 'getDashboardData']);
            Route::post('/refresh', [DashboardController::class, 'forceRefresh']);
            Route::get('/cache-status', [DashboardController::class, 'getCacheStatus']);
            Route::post('/clear-cache', [DashboardController::class, 'clearCache']);
        });

        // Analytics
        Route::prefix('analytics')->group(function () {
            Route::get('/historical', [AnalyticsController::class, 'historical']);
            Route::get('/customer-behavior', [AnalyticsController::class, 'customerBehavior']);
            Route::get('/realtime', [AnalyticsController::class, 'realtime']);
        });



        // ========================================
        // NOTIFICATIONS
        // ========================================
        Route::prefix('notifications')->group(function () {
            Route::get('/', [NotificationController::class, 'index']);
            Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
            Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
            Route::delete('/clear-read', [NotificationController::class, 'clearRead']);
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
        });
    });

    // ========================================
    // BRANCH ROUTES (web-based, handled via web.php with auth:branch)
    // ========================================
    // Branch pickup and location tracking actions are handled through
    // web routes in routes/web.php under the auth:branch middleware.
});

Route::middleware('auth:branch')->group(function () {
    Route::get('/branch/notifications', [BranchNotificationController::class, 'index']);
    Route::get('/branch/notifications/unread-count', [BranchNotificationController::class, 'getUnreadCount']);
    Route::post('/branch/notifications/mark-all-read', [BranchNotificationController::class, 'markAllAsRead']);
    Route::delete('/branch/notifications/clear-read', [BranchNotificationController::class, 'deleteAllRead']);
    Route::post('/branch/notifications/{id}/read', [BranchNotificationController::class, 'markAsRead']);
    Route::delete('/branch/notifications/{id}', [BranchNotificationController::class, 'destroy']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/notifications', [AdminNotificationController::class, 'index']);
    Route::get('/admin/notifications/unread-count', [AdminNotificationController::class, 'unreadCount']);
    Route::post('/admin/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead']);
    Route::delete('/admin/notifications/clear-read', [AdminNotificationController::class, 'clearRead']);
    Route::post('/admin/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead']);
    Route::delete('/admin/notifications/{id}', [AdminNotificationController::class, 'delete']);
});


