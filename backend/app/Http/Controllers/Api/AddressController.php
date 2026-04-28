<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerAddress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $customer = $request->user();
            
            if (!$customer) {
                \Log::warning('Address index called without authenticated customer');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }
            
            $addresses = $customer->activeAddresses()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'label' => $address->label,
                        'full_address' => $address->full_address,
                        'formatted_address' => $address->formatted_address,
                        'city' => $address->city,
                        'province' => $address->province,
                        'contact_person' => $address->contact_person,
                        'contact_phone' => $address->contact_phone,
                        'delivery_notes' => $address->delivery_notes,
                        'icon' => $address->icon,
                        'is_default' => $address->is_default,
                        'coordinates' => $address->latitude && $address->longitude ? [
                            'lat' => (float) $address->latitude,
                            'lng' => (float) $address->longitude,
                        ] : null,
                        'created_at' => $address->created_at->format('M d, Y'),
                    ];
                });

            \Log::info('Addresses fetched successfully', [
                'customer_id' => $customer->id,
                'count' => $addresses->count(),
            ]);

            return response()->json([
                'success' => true,
                'data' => [
                    'addresses' => $addresses,
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch addresses', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $request->user()->id ?? null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch addresses',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'label' => 'required|string|max:255',
                'full_address' => 'required|string|max:500',
                'street' => 'nullable|string|max:255',
                'barangay' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'contact_person' => 'nullable|string|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'delivery_notes' => 'nullable|string|max:500',
                'is_default' => 'boolean',
            ]);

            if ($validator->fails()) {
                \Log::warning('Address validation failed', [
                    'errors' => $validator->errors()->toArray(),
                    'input' => $request->except(['password'])
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $customer = $request->user();

            DB::beginTransaction();

            // If this is set as default, unset other defaults
            if ($request->boolean('is_default')) {
                $customer->addresses()->update(['is_default' => false]);
            }

            $addressData = $request->only([
                'label',
                'full_address',
                'street',
                'barangay',
                'city',
                'province',
                'postal_code',
                'latitude',
                'longitude',
                'contact_person',
                'contact_phone',
                'delivery_notes',
                'is_default',
            ]);

            $address = $customer->addresses()->create($addressData);

            DB::commit();

            \Log::info('Address created successfully', [
                'customer_id' => $customer->id,
                'address_id' => $address->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Address added successfully',
                'data' => [
                    'address' => [
                        'id' => $address->id,
                        'label' => $address->label,
                        'full_address' => $address->full_address,
                        'formatted_address' => $address->formatted_address,
                        'city' => $address->city,
                        'province' => $address->province,
                        'contact_person' => $address->contact_person,
                        'contact_phone' => $address->contact_phone,
                        'delivery_notes' => $address->delivery_notes,
                        'icon' => $address->icon,
                        'is_default' => $address->is_default,
                        'coordinates' => $address->latitude && $address->longitude ? [
                            'lat' => (float) $address->latitude,
                            'lng' => (float) $address->longitude,
                        ] : null,
                        'created_at' => $address->created_at->format('M d, Y'),
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to add address', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'customer_id' => $request->user()->id ?? null,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to add address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $customer = $request->user();
            $address = $customer->addresses()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'label' => 'required|string|max:255',
                'full_address' => 'required|string|max:500',
                'street' => 'nullable|string|max:255',
                'barangay' => 'nullable|string|max:255',
                'city' => 'required|string|max:255',
                'province' => 'required|string|max:255',
                'postal_code' => 'nullable|string|max:10',
                'latitude' => 'nullable|numeric|between:-90,90',
                'longitude' => 'nullable|numeric|between:-180,180',
                'contact_person' => 'nullable|string|max:255',
                'contact_phone' => 'nullable|string|max:20',
                'delivery_notes' => 'nullable|string|max:500',
                'is_default' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            DB::beginTransaction();

            // If this is set as default, unset other defaults
            if ($request->boolean('is_default')) {
                $customer->addresses()->where('id', '!=', $id)->update(['is_default' => false]);
            }

            $address->update($request->all());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'data' => [
                    'address' => [
                        'id' => $address->id,
                        'label' => $address->label,
                        'full_address' => $address->full_address,
                        'formatted_address' => $address->formatted_address,
                        'city' => $address->city,
                        'province' => $address->province,
                        'contact_person' => $address->contact_person,
                        'contact_phone' => $address->contact_phone,
                        'delivery_notes' => $address->delivery_notes,
                        'icon' => $address->icon,
                        'is_default' => $address->is_default,
                        'coordinates' => $address->latitude && $address->longitude ? [
                            'lat' => (float) $address->latitude,
                            'lng' => (float) $address->longitude,
                        ] : null,
                        'created_at' => $address->created_at->format('M d, Y'),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $customer = $request->user();
            $address = $customer->addresses()->findOrFail($id);

            $address->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Address removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setDefault(Request $request, $id): JsonResponse
    {
        try {
            $customer = $request->user();
            $address = $customer->activeAddresses()->findOrFail($id);

            DB::beginTransaction();

            // Unset all other defaults
            $customer->addresses()->update(['is_default' => false]);
            
            // Set this one as default
            $address->update(['is_default' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Default address updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default address',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}