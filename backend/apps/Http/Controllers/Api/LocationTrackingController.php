<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\LocationUpdate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LocationTrackingController extends Controller
{
    // Start tracking session
    public function startTracking(Request $request)
    {
        $request->validate([
            'pickup_request_id' => 'required|exists:pickup_requests,id',
            'user_type' => 'required|in:staff,customer'
        ]);

        $pickupRequest = PickupRequest::findOrFail($request->pickup_request_id);
        
        // Verify user has permission to track this pickup
        if ($request->user_type === 'customer' && $pickupRequest->customer_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Store tracking session in cache
        $trackingKey = "tracking_{$request->pickup_request_id}_{$request->user_type}_" . Auth::id();
        Cache::put($trackingKey, [
            'pickup_request_id' => $request->pickup_request_id,
            'user_id' => Auth::id(),
            'user_type' => $request->user_type,
            'started_at' => now()
        ], 3600); // 1 hour

        return response()->json(['success' => true]);
    }

    // Update location
    public function updateLocation(Request $request)
    {
        $request->validate([
            'pickup_request_id' => 'required|exists:pickup_requests,id',
            'user_type' => 'required|in:staff,customer',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric'
        ]);

        // Store location update
        LocationUpdate::create([
            'pickup_request_id' => $request->pickup_request_id,
            'user_id' => Auth::id(),
            'user_type' => $request->user_type ?? 'customer',
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'speed' => $request->speed,
            'heading' => $request->heading,
            'timestamp' => now()
        ]);

        // Cache latest location for quick access
        $locationKey = "location_{$request->pickup_request_id}_{$request->user_type}";
        Cache::put($locationKey, [
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'updated_at' => now()
        ], 300); // 5 minutes

        // Broadcast location update to pickup-specific channel
        event(new \App\Events\LocationUpdated($request->pickup_request_id, [
            'user_id' => Auth::id(),
            'user_type' => $request->user_type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'accuracy' => $request->accuracy,
            'timestamp' => now()->toIso8601String()
        ]));

        return response()->json(['success' => true]);
    }

    // Get current locations for pickup request
    public function getLocations($pickupRequestId)
    {
        $pickupRequest = PickupRequest::findOrFail($pickupRequestId);
        
        // Verify user has permission
        if ($pickupRequest->customer_id !== Auth::id() && 
            !Auth::user()->hasRole(['admin', 'staff'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get latest locations from cache
        $staffLocation = null;
        $customerLocation = null;

        if ($pickupRequest->assigned_to) {
            $staffLocation = Cache::get("location_{$pickupRequestId}_staff");
        }

        $customerLocation = Cache::get("location_{$pickupRequestId}_customer");

        return response()->json([
            'success' => true,
            'data' => [
                'pickup_request' => $pickupRequest->load(['customer', 'assignedStaff']),
                'staff_location' => $staffLocation,
                'customer_location' => $customerLocation,
                'last_updated' => now()
            ]
        ]);
    }

    // Stop tracking
    public function stopTracking(Request $request)
    {
        $request->validate([
            'pickup_request_id' => 'required|exists:pickup_requests,id',
            'user_type' => 'required|in:staff,customer'
        ]);

        // Remove tracking session from cache
        $trackingKey = "tracking_{$request->pickup_request_id}_{$request->user_type}_" . Auth::id();
        Cache::forget($trackingKey);

        return response()->json(['success' => true]);
    }

    // Get tracking history
    public function getTrackingHistory($pickupRequestId)
    {
        $pickupRequest = PickupRequest::findOrFail($pickupRequestId);
        
        // Verify permission
        if ($pickupRequest->customer_id !== Auth::id() && 
            !Auth::user()->hasRole(['admin', 'staff'])) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $history = LocationUpdate::forPickup($pickupRequestId)
            ->with('user')
            ->orderBy('timestamp', 'desc')
            ->paginate(50);

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }
}