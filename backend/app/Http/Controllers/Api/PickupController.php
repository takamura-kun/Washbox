<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PickupRequest;
use App\Models\Notification;  // ✅ ADDED
use App\Services\ServiceAvailabilityService; // ✅ ADDED
use App\Services\SecureFileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PickupController extends Controller
{
    /**
     * Get all pickup requests for authenticated customer
     *
     * GET /api/v1/pickups
     */
    public function index(Request $request)
    {
        try {
            $customer = $request->user();

            $pickups = PickupRequest::where('customer_id', $customer->id)
                ->with(['branch', 'service', 'assignedStaff'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedPickups = $pickups->map(function ($pickup) {
                return [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'pickup_address' => $pickup->pickup_address,
                    'preferred_date' => $pickup->preferred_date->format('Y-m-d'),
                    'preferred_time' => $pickup->preferred_time,
                    'notes' => $pickup->notes,
                    'phone_number' => $pickup->phone_number,
                    'estimated_weight' => $pickup->estimated_weight,
                    'service_name' => $pickup->service->name ?? null,
                    'branch_id' => $pickup->branch_id,
                    'branch' => [
                        'id' => $pickup->branch->id,
                        'name' => $pickup->branch->name,
                        'address' => $pickup->branch->address ?? null,
                        'phone' => $pickup->branch->phone ?? null,
                    ],
                    'created_at' => $pickup->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'pickups' => $formattedPickups,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pickups: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pickup requests',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific pickup request details
     *
     * GET /api/v1/pickups/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $customer = $request->user();

            $pickup = PickupRequest::where('customer_id', $customer->id)
                ->where('id', $id)
                ->with(['branch', 'service', 'assignedStaff', 'laundry'])
                ->first();

            if (!$pickup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup request not found',
                ], 404);
            }

            $responseData = [
                'pickup' => [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'pickup_address' => $pickup->pickup_address,
                    'latitude' => $pickup->latitude,
                    'longitude' => $pickup->longitude,
                    'preferred_date' => $pickup->preferred_date->format('Y-m-d'),
                    'preferred_time' => $pickup->preferred_time,
                    'notes' => $pickup->notes,
                    'service' => $pickup->service ? [
                        'id' => $pickup->service->id,
                        'name' => $pickup->service->name,
                    ] : null,
                    'branch' => [
                        'id' => $pickup->branch->id,
                        'name' => $pickup->branch->name,
                        'address' => $pickup->branch->address ?? null,
                        'phone' => $pickup->branch->phone ?? null,
                    ],
                    'assigned_staff' => $pickup->assignedStaff ? [
                        'id' => $pickup->assignedStaff->id,
                        'name' => $pickup->assignedStaff->name,
                    ] : null,
                    'laundries_id' => $pickup->laundries_id,
                    'customer_proof_photo_url' => $pickup->customer_proof_photo_url,
                    'customer_proof_uploaded_at' => $pickup->customer_proof_uploaded_at ? $pickup->customer_proof_uploaded_at->toIso8601String() : null,
                    'pickup_proof_photo_url' => $pickup->pickup_proof_photo_url,
                    'proof_uploaded_at' => $pickup->proof_uploaded_at ? $pickup->proof_uploaded_at->toIso8601String() : null,
                    'accepted_at' => $pickup->accepted_at ? $pickup->accepted_at->toIso8601String() : null,
                    'en_route_at' => $pickup->en_route_at ? $pickup->en_route_at->toIso8601String() : null,
                    'picked_up_at' => $pickup->picked_up_at ? $pickup->picked_up_at->toIso8601String() : null,
                    'created_at' => $pickup->created_at->toIso8601String(),
                ],
            ];

            // Include linked laundry details if exists
            if ($pickup->laundry) {
                $responseData['laundry'] = [
                    'id' => $pickup->laundry->id,
                    'status' => $pickup->laundry->status,
                    'total_amount' => $pickup->laundry->total_amount,
                    'payment_status' => $pickup->laundry->payment_status,
                    'created_at' => $pickup->laundry->created_at->toIso8601String(),
                    'updated_at' => $pickup->laundry->updated_at->toIso8601String(),
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $responseData
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pickup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pickup request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
 * Create new pickup request
 *
 * POST /api/v1/pickups
 */
public function store(Request $request)
{
    try {
        // Check if pickup service is enabled
        if (!ServiceAvailabilityService::isPickupEnabled()) {
            return response()->json([
                'success' => false,
                'message' => 'Pickup service is currently disabled. Please contact us directly or visit our branch.',
                'service_status' => ServiceAvailabilityService::getServiceStatus()
            ], 503); // Service Unavailable
        }

        $customer = $request->user();

        // Check if customer proof photo is required
        $requireProof = (bool) \App\Models\SystemSetting::get('require_customer_proof_photo', true);

        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'pickup_address' => 'required|string|max:500',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'preferred_date' => 'required|date|after_or_equal:today',
            'preferred_time' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'service_id' => 'nullable|exists:services,id',
            'service_type' => 'nullable|in:pickup_only,delivery_only,both',
            'phone_number' => 'required|string|max:20',
            'customer_proof_photo' => $requireProof ? 'required|image|mimes:jpeg,png,jpg|max:5120' : 'nullable|image|mimes:jpeg,png,jpg|max:5120',
        ]);

        // Validate service type availability
        $serviceType = $validated['service_type'] ?? 'both'; // Default to both
        if (!ServiceAvailabilityService::isServiceTypeAvailable($serviceType)) {
            return response()->json([
                'success' => false,
                'message' => ServiceAvailabilityService::getDisabledServiceMessage($serviceType),
                'service_status' => ServiceAvailabilityService::getServiceStatus()
            ], 400);
        }

        // Store customer proof photo securely
        $customerProofFilename = null;
        if ($request->hasFile('customer_proof_photo')) {
            try {
                $uploadResult = SecureFileUploadService::uploadImage(
                    $request->file('customer_proof_photo'),
                    'customer-pickup-proofs'
                );
                $customerProofFilename = $uploadResult['filename'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed: ' . $e->getMessage(),
                ], 400);
            }
        }

        // Calculate pickup and delivery fees using the proper DeliveryFee model
        $deliveryFee = \App\Models\DeliveryFee::getOrCreateForBranch($validated['branch_id']);
        $fees = $deliveryFee->calculateFee($serviceType);

        $pickup = PickupRequest::create([
            'customer_id' => $customer->id,
            'branch_id' => $validated['branch_id'],
            'pickup_address' => $validated['pickup_address'],
            'latitude' => $validated['latitude'] ?? 0,
            'longitude' => $validated['longitude'] ?? 0,
            'preferred_date' => $validated['preferred_date'],
            'preferred_time' => $validated['preferred_time'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'phone_number' => $validated['phone_number'],
            'service_id' => $validated['service_id'] ?? null,
            'service_type' => $serviceType,
            'pickup_fee' => $fees['pickup_fee'],
            'delivery_fee' => $fees['delivery_fee'],
            'customer_proof_photo' => $customerProofFilename,
            'customer_proof_uploaded_at' => now(),
            'status' => 'pending',
        ]);

        // 🔔 Observer will automatically trigger AdminNotification::notifyNewPickupRequest()

        return response()->json([
            'success' => true,
            'message' => 'Pickup request created successfully! We will contact you shortly.',
            'data' => [
                'pickup' => [
                    'id' => $pickup->id,
                    'status' => $pickup->status,
                    'preferred_date' => $pickup->preferred_date->format('Y-m-d'),
                    'preferred_time' => $pickup->preferred_time,
                    'service_type' => $pickup->service_type,
                    'pickup_fee' => $pickup->pickup_fee,
                    'delivery_fee' => $pickup->delivery_fee,
                    'total_fee' => $pickup->pickup_fee + $pickup->delivery_fee,
                    'customer_proof_photo_url' => $pickup->customer_proof_photo_url,
                ],
            ]
        ], 201);
    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Validation failed',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error creating pickup request: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Failed to create pickup request',
            'error' => $e->getMessage(),
        ], 500);
    }
}
    /**
     * Cancel pickup request (Customer)
     *
     * PUT /api/v1/pickups/{id}/cancel
     */
    public function cancel(Request $request, $id)
    {
        try {
            $customer = $request->user();

            $pickup = PickupRequest::where('customer_id', $customer->id)
                ->where('id', $id)
                ->first();

            if (!$pickup) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup request not found',
                ], 404);
            }

            // Only allow cancellation of pending requests (before admin/staff confirmation)
            if ($pickup->status !== 'pending') {
                $statusMessages = [
                    'accepted' => 'This pickup has been accepted by our staff and cannot be cancelled. Please contact us directly.',
                    'en_route' => 'Our driver is already on the way. Please contact us directly to make changes.',
                    'picked_up' => 'Your laundry has already been picked up and is being processed.',
                    'cancelled' => 'This pickup request has already been cancelled.',
                    'delivered' => 'This pickup has already been completed.',
                ];
                
                return response()->json([
                    'success' => false,
                    'message' => $statusMessages[$pickup->status] ?? 'Cannot cancel pickup in current status',
                ], 400);
            }

            // Cancel the pickup
            $pickup->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $request->input('reason', 'Cancelled by customer'),
                'cancelled_by' => null, // Customer cancel, no user_id needed
            ]);

            // Create notification for admin/staff
            Notification::createPickupCancelled($pickup, 'customer');

            return response()->json([
                'success' => true,
                'message' => 'Pickup request cancelled successfully',
                'data' => [
                    'pickup' => [
                        'id' => $pickup->id,
                        'status' => $pickup->status,
                        'cancelled_at' => $pickup->cancelled_at->toIso8601String(),
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling pickup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel pickup request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Accept pickup request (Staff/Owner)
     *
     * PUT /api/v1/staff/pickups/{id}/accept
     */
    public function accept(Request $request, $id)
    {
        try {
            $pickup = PickupRequest::findOrFail($id);

            if ($pickup->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup request is not in pending status',
                ], 400);
            }

            $pickup->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'assigned_to' => $request->user()->id ?? null,
            ]);

            // ✅ CREATE NOTIFICATION
            Notification::createPickupAccepted($pickup);

            return response()->json([
                'success' => true,
                'message' => 'Pickup request accepted successfully',
                'data' => [
                    'pickup' => [
                        'id' => $pickup->id,
                        'status' => $pickup->status,
                        'accepted_at' => $pickup->accepted_at->toIso8601String(),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error accepting pickup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to accept pickup request',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark pickup as en route (Rider is on the way)
     *
     * PUT /api/v1/staff/pickups/{id}/en-route
     */
    public function markEnRoute(Request $request, $id)
    {
        try {
            $pickup = PickupRequest::findOrFail($id);

            if ($pickup->status !== 'accepted') {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup must be accepted first',
                ], 400);
            }

            $pickup->update([
                'status' => 'en_route',
                'en_route_at' => now(),
            ]);

            // ✅ CREATE NOTIFICATION
            Notification::createPickupEnRoute($pickup);

            return response()->json([
                'success' => true,
                'message' => 'Pickup marked as en route',
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking pickup en route: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update pickup status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mark pickup as picked up (Laundry collected and arrived at shop)
     * Automatically creates a laundry order
     *
     * PUT /api/v1/staff/pickups/{id}/picked-up
     */
    public function markPickedUp(Request $request, $id)
    {
        try {
            $pickup = PickupRequest::findOrFail($id);

            if (!in_array($pickup->status, ['accepted', 'en_route'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status transition',
                ], 400);
            }

            // Require proof photo before marking as picked up
            if (!$pickup->hasProofPhoto()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please upload proof photo before marking as picked up',
                ], 400);
            }

            // Start database transaction
            \DB::beginTransaction();

            // Update pickup status
            $pickup->update([
                'status' => 'picked_up',
                'picked_up_at' => now(),
            ]);

            // Automatically create laundry order from pickup
            $laundry = \App\Models\Laundry::create([
                'customer_id' => $pickup->customer_id,
                'branch_id' => $pickup->branch_id,
                'pickup_request_id' => $pickup->id,
                'service_id' => $pickup->service_id,
                'status' => 'pending',
                'delivery_address' => $pickup->pickup_address, // Same address for delivery
                'delivery_latitude' => $pickup->latitude,
                'delivery_longitude' => $pickup->longitude,
                'phone_number' => $pickup->phone_number,
                'notes' => $pickup->notes,
                'pickup_fee' => $pickup->pickup_fee ?? 0,
                'delivery_fee' => $pickup->delivery_fee ?? 0,
                'service_type' => $pickup->service_type ?? 'both',
            ]);

            // Link laundry to pickup
            $pickup->update(['laundries_id' => $laundry->id]);

            \DB::commit();

            // ✅ CREATE NOTIFICATION with proof photo
            Notification::createPickupProofUploaded($pickup);

            return response()->json([
                'success' => true,
                'message' => 'Pickup marked as picked up. Laundry order created automatically.',
                'data' => [
                    'pickup_id' => $pickup->id,
                    'laundry_id' => $laundry->id,
                    'laundry_status' => $laundry->status,
                ]
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Error marking pickup as picked up: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to mark pickup as picked up',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload pickup proof photo (Staff - when laundry arrives at shop)
     *
     * POST /api/v1/staff/pickups/{id}/upload-proof
     */
    public function uploadProof(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'proof_photo' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
            ]);

            $pickup = PickupRequest::findOrFail($id);

            // Check if pickup is en_route (laundry should be arriving/arrived at shop)
            if (!in_array($pickup->status, ['en_route', 'picked_up'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pickup must be en route or picked up to upload proof',
                ], 400);
            }

            // Store the proof image securely
            try {
                $uploadResult = SecureFileUploadService::uploadImage(
                    $request->file('proof_photo'),
                    'pickup-proofs'
                );
                $filename = $uploadResult['filename'];
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed: ' . $e->getMessage(),
                ], 400);
            }

            // Update pickup request with proof photo
            $pickup->update([
                'pickup_proof_photo' => $filename,
                'proof_uploaded_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pickup proof uploaded successfully. You can now mark as picked up.',
                'data' => [
                    'proof_photo_url' => $pickup->pickup_proof_photo_url,
                    'uploaded_at' => $pickup->proof_uploaded_at->toIso8601String(),
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error uploading pickup proof: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload pickup proof',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**laundry
     * Link pickup to laundry (after creating laundry from pickup)
     *
     * PUT /api/v1/staff/pickups/{id}/link-laundry
     */
    public function linkLaundry(Request $request, $id)
    {
        try {
            $request->validate([
                'laundries_id' => 'required|exists:laundries,id',
            ]);

            $pickup = PickupRequest::findOrFail($id);

            $pickup->update([
                'laundries_id' => $request->laundries_id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Laundry linked to pickup request successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error linking laundry to pickup: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to link laundry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getByIds(Request $request)
    {
        try {
            $pickupIds = $request->input('pickup_ids', []);
            
            if (empty($pickupIds)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No pickup IDs provided'
                ], 400);
            }
            
            $pickups = PickupRequest::with(['customer', 'branch'])
                ->whereIn('id', $pickupIds)
                ->get()
                ->map(function ($pickup) {
                    return [
                        'id' => $pickup->id,
                        'pickup_latitude' => $pickup->latitude,
                        'pickup_longitude' => $pickup->longitude,
                        'customer_name' => $pickup->customer->name ?? 'Unknown',
                        'address' => $pickup->address,
                        'status' => $pickup->status,
                        'branch_id' => $pickup->branch_id,
                    ];
                });
            
            return response()->json([
                'success' => true,
                'pickups' => $pickups
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching pickups by IDs: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch pickups',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
