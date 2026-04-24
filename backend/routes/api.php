<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LaundryController;
use App\Http\Controllers\Api\BranchController;
use App\Http\Controllers\Api\PickupController;
use App\Http\Controllers\Api\ServiceController;
use App\Http\Controllers\Api\AddOnController;
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
use App\Http\Controllers\Admin\NotificationController as AdminNotificationController;
use App\Http\Controllers\Staff\NotificationController as StaffNotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

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

    // Services (Public)
    Route::get('/services', [ServiceController::class, 'index']);

    // Service Status (Public)
    Route::get('/service-status', [ServiceStatusController::class, 'index']);
    Route::get('/service-config', [ServiceStatusController::class, 'getServiceConfig']);
    Route::post('/check-delivery-fee', [ServiceStatusController::class, 'checkDeliveryFee']);
    Route::get('/service-areas/{branchId}', [ServiceStatusController::class, 'getServiceAreas']);

    // Add-ons (Public)
    Route::get('/addons', [AddOnController::class, 'index']);
    Route::get('/addons/{id}', [AddOnController::class, 'show']);

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

    Route::get('/health', function () {
        return response()->json([
            'success' => true,
            'message' => 'WashBox API is running',
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
            'mail_driver' => config('mail.default'),
            'app_env' => app()->environment(),
        ]);
    });

    // Test endpoint for mobile connectivity
    Route::get('/test', function () {
        return response()->json([
            'success' => true,
            'message' => 'API connection successful',
            'server_time' => now()->format('Y-m-d H:i:s'),
        ]);
    });

    // Debug endpoint for promotions
    Route::get('/debug/promotions', function () {
        $promotions = \App\Models\Promotion::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->get();
            
        return response()->json([
            'success' => true,
            'debug' => true,
            'count' => $promotions->count(),
            'current_time' => now()->toIso8601String(),
            'promotions' => $promotions->map(function($p) {
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'is_active' => $p->is_active,
                    'start_date' => $p->start_date,
                    'end_date' => $p->end_date,
                    'type' => $p->type,
                ];
            })
        ]);
    });

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
            Route::post('/{id}/read', [NotificationController::class, 'markAsRead']);
            Route::delete('/{id}', [NotificationController::class, 'destroy']);
            Route::delete('/clear-read', [NotificationController::class, 'clearRead']);
        });
    });

    // ========================================
    // STAFF ROUTES
    // ========================================

    Route::middleware(['auth:sanctum'])->prefix('staff')->group(function () {
        Route::prefix('pickups')->group(function () {
            Route::put('/{id}/accept', [PickupController::class, 'accept']);
            Route::put('/{id}/en-route', [PickupController::class, 'markEnRoute']);
            Route::put('/{id}/picked-up', [PickupController::class, 'markPickedUp']);
            Route::put('/{id}/link-order', [PickupController::class, 'linkLaundry']);
            Route::post('/{id}/upload-proof', [PickupController::class, 'uploadProof'])->middleware('throttle:10,1'); // 10 uploads per minute
        });
        
        // Staff Location Tracking
        Route::prefix('location-tracking')->group(function () {
            Route::post('/start', [LocationTrackingController::class, 'startTracking']);
            Route::post('/update', [LocationTrackingController::class, 'updateLocation']);
            Route::post('/stop', [LocationTrackingController::class, 'stopTracking']);
        });
    });
});

Route::middleware('auth:staff')->group(function () {
    Route::get('/staff/notifications', [StaffNotificationController::class, 'index']);
    Route::get('/staff/notifications/unread-count', [StaffNotificationController::class, 'unreadCount']);
    Route::post('/staff/notifications/{id}/read', [StaffNotificationController::class, 'markAsRead']);
    Route::post('/staff/notifications/read-all', [StaffNotificationController::class, 'markAllAsRead']);
    Route::delete('/staff/notifications/{id}', [StaffNotificationController::class, 'delete']);
    Route::delete('/staff/notifications/clear-read', [StaffNotificationController::class, 'clearRead']);
});

Route::middleware('auth:admin')->group(function () {
    Route::get('/admin/notifications', [AdminNotificationController::class, 'index']);
    Route::get('/admin/notifications/unread-count', [AdminNotificationController::class, 'unreadCount']);
    Route::post('/admin/notifications/{id}/read', [AdminNotificationController::class, 'markAsRead']);
    Route::post('/admin/notifications/read-all', [AdminNotificationController::class, 'markAllAsRead']);
    Route::delete('/admin/notifications/{id}', [AdminNotificationController::class, 'delete']);
    Route::delete('/admin/notifications/clear-read', [AdminNotificationController::class, 'clearRead']);
});

Route::middleware('auth:sanctum')->group(function () {
    // Tracking endpoints
    Route::prefix('tracking')->group(function () {
        Route::post('/start', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Tracking started', $request->all());
            return response()->json(['success' => true, 'message' => 'Tracking started']);
        })->name('api.tracking.start');

        Route::post('/start-multi', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Multi-tracking started', $request->all());
            return response()->json(['success' => true, 'message' => 'Multi-tracking started']);
        })->name('api.tracking.start-multi');

        Route::post('/stop', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Tracking stopped', $request->all());
            return response()->json(['success' => true, 'message' => 'Tracking stopped']);
        })->name('api.tracking.stop');

        Route::post('/update', function (\Illuminate\Http\Request $request) {
            \Illuminate\Support\Facades\Log::info('Location update', $request->all());

            if ($request->has('active_pickups') && $request->has('location')) {
                foreach ($request->active_pickups as $pickupId) {
                    event(new \App\Events\LocationUpdated($pickupId, $request->location));
                }
            }

            return response()->json(['success' => true, 'message' => 'Location updated']);
        })->name('api.tracking.update');
    });

    // Error reporting endpoint
    Route::post('/errors', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Log::error('Frontend Error', $request->all());
        return response()->json(['success' => true]);
    })->name('api.errors');

    // Performance monitoring endpoint
    Route::post('/performance', function (\Illuminate\Http\Request $request) {
        \Illuminate\Support\Facades\Log::info('Performance Metric', $request->all());
        return response()->json(['success' => true]);
    })->name('api.performance');
});

// Vehicle and Traffic endpoints (mock data for route optimizer)
Route::prefix('vehicles')->group(function () {
    Route::get('/profiles', function () {
        return response()->json([
            'success' => true,
            'vehicles' => [
                ['id' => 1, 'name' => 'Van 1', 'capacity' => 50, 'speed' => 40],
                ['id' => 2, 'name' => 'Motorcycle 1', 'capacity' => 10, 'speed' => 60]
            ]
        ]);
    });
});

Route::prefix('traffic')->group(function () {
    Route::get('/current', function () {
        return response()->json([
            'success' => true,
            'traffic' => ['level' => 'moderate', 'delay_factor' => 1.2]
        ]);
    });
});
