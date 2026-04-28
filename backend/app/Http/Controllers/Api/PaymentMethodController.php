<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CustomerPaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentMethodController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $customer = $request->user();
            
            $paymentMethods = $customer->activePaymentMethods()
                ->orderBy('is_default', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'type' => $method->type,
                        'type_display' => $method->type_display,
                        'name' => $method->name,
                        'icon' => $method->icon,
                        'is_default' => $method->is_default,
                        'created_at' => $method->created_at->format('M d, Y'),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => [
                    'payment_methods' => $paymentMethods,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment methods',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|in:cash,gcash,bank_transfer,credit_card',
                'name' => 'required|string|max:255',
                'details' => 'nullable|array',
                'is_default' => 'boolean',
            ]);

            if ($validator->fails()) {
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
                $customer->paymentMethods()->update(['is_default' => false]);
            }

            $paymentMethod = $customer->paymentMethods()->create([
                'type' => $request->type,
                'name' => $request->name,
                'details' => $request->details,
                'is_default' => $request->boolean('is_default'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment method added successfully',
                'data' => [
                    'payment_method' => [
                        'id' => $paymentMethod->id,
                        'type' => $paymentMethod->type,
                        'type_display' => $paymentMethod->type_display,
                        'name' => $paymentMethod->name,
                        'icon' => $paymentMethod->icon,
                        'is_default' => $paymentMethod->is_default,
                        'created_at' => $paymentMethod->created_at->format('M d, Y'),
                    ],
                ],
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $customer = $request->user();
            $paymentMethod = $customer->paymentMethods()->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'details' => 'nullable|array',
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
                $customer->paymentMethods()->where('id', '!=', $id)->update(['is_default' => false]);
            }

            $paymentMethod->update([
                'name' => $request->name,
                'details' => $request->details,
                'is_default' => $request->boolean('is_default'),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
                'data' => [
                    'payment_method' => [
                        'id' => $paymentMethod->id,
                        'type' => $paymentMethod->type,
                        'type_display' => $paymentMethod->type_display,
                        'name' => $paymentMethod->name,
                        'icon' => $paymentMethod->icon,
                        'is_default' => $paymentMethod->is_default,
                        'created_at' => $paymentMethod->created_at->format('M d, Y'),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $customer = $request->user();
            $paymentMethod = $customer->paymentMethods()->findOrFail($id);

            $paymentMethod->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => 'Payment method removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function setDefault(Request $request, $id): JsonResponse
    {
        try {
            $customer = $request->user();
            $paymentMethod = $customer->activePaymentMethods()->findOrFail($id);

            DB::beginTransaction();

            // Unset all other defaults
            $customer->paymentMethods()->update(['is_default' => false]);
            
            // Set this one as default
            $paymentMethod->update(['is_default' => true]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Default payment method updated successfully',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to set default payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}