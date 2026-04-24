<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\InventoryItem;
use App\Services\LaundryInventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class LaundryController extends Controller
{
    /**
     * Get all laundries for authenticated customer
     *
     * GET /api/v1/laundries
     */
    public function index(Request $request)
    {
        try {
            $customer = $request->user();

            $laundries = Laundry::where('customer_id', $customer->id)
                ->with(['branch', 'service'])
                ->orderBy('created_at', 'desc')
                ->get();

            $formattedLaundries = $laundries->map(function ($laundry) {
                return [
                    'id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'status' => $laundry->status,
                    'service_name' => $laundry->service->name ?? 'Laundry Service',
                    'branch_name' => $laundry->branch->name ?? 'Branch',
                    'weight' => (float) $laundry->weight,
                    'total_amount' => (float) $laundry->total_amount,
                    'estimated_completion' => $laundry->delivery_date
                        ? $laundry->delivery_date
                        : $laundry->created_at->addDays(2)->toIso8601String(),
                    'created_at' => $laundry->created_at->toIso8601String(),
                    'updated_at' => $laundry->updated_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'laundries' => $formattedLaundries,
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching laundries: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch laundries',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get specific laundry details
     *
     * GET /api/v1/laundries/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $customer = $request->user();

            // Try to find by tracking number first, then by ID
            $laundry = Laundry::where('customer_id', $customer->id)
                ->where(function ($query) use ($id) {
                    $query->where('tracking_number', $id)
                          ->orWhere('id', $id);
                })
                ->with(['branch', 'service', 'addons', 'promotion'])
                ->first();

            if (!$laundry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laundry not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'laundry' => [
                        'id' => $laundry->id,
                        'tracking_number' => $laundry->tracking_number,
                        'status' => $laundry->status,
                        'payment_status' => $laundry->payment_status ?? 'unpaid',
                        'service_name' => $laundry->service->name ?? 'Laundry Service',
                        'service_type' => $laundry->service->service_type ?? 'regular_clothes',
                        'pricing_type' => $laundry->service->pricing_type ?? 'per_load',
                        'number_of_loads' => $laundry->number_of_loads ?? 1,
                        'price_per_load' => (float) ($laundry->service->price_per_load ?? 0),
                        'service_price_per_piece' => (float) ($laundry->service->price_per_piece ?? 0),
                        'branch_id' => $laundry->branch_id,
                        'branch_name' => $laundry->branch->name ?? 'Branch',
                        'branch_address' => $laundry->branch->address ?? null,
                        'branch_phone' => $laundry->branch->phone ?? null,
                        'weight' => (float) $laundry->weight,
                        'price_per_piece' => (float) $laundry->price_per_piece,
                        'subtotal' => (float) $laundry->subtotal,
                        'promotion_name' => $laundry->promotion->name ?? null,
                        'promotion_discount' => (float) ($laundry->discount_amount ?? 0),
                        'promotion_price_per_load' => $laundry->promotion ? (float) ($laundry->promotion->display_price ?? 0) : null,
                        'addons' => $laundry->addons->map(function ($addon) {
                            return [
                                'name' => $addon->name,
                                'quantity' => $addon->pivot->quantity,
                                'price' => (float) $addon->pivot->price_at_purchase,
                                'total' => (float) ($addon->pivot->price_at_purchase * $addon->pivot->quantity),
                            ];
                        }),
                        'pickup_fee' => (float) ($laundry->pickup_fee ?? 0),
                        'delivery_fee' => (float) ($laundry->delivery_fee ?? 0),
                        'discount_amount' => (float) ($laundry->discount_amount ?? 0),
                        'total_amount' => (float) $laundry->total_amount,
                        'notes' => $laundry->notes,
                        'estimated_completion' => $laundry->delivery_date
                            ? $laundry->delivery_date
                            : $laundry->created_at->addDays(2)->toIso8601String(),
                        'created_at' => $laundry->created_at->toIso8601String(),
                        'updated_at' => $laundry->updated_at->toIso8601String(),
                    ],
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching laundry: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch laundry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create new laundry (self-service)
     *
     * POST /api/v1/laundries
     */
    public function store(Request $request, LaundryInventoryService $inventoryService)
    {
        try {
            $customer = $request->user();

            $validated = $request->validate([
                'branch_id' => 'required|exists:branches,id',
                'service_id' => 'required|exists:services,id',
                'weight' => 'nullable|numeric|min:0.1|max:1000',
                'number_of_pieces' => 'nullable|integer|min:1',
                'pickup_address' => 'nullable|string|max:500',
                'delivery_address' => 'nullable|string|max:500',
                'pickup_date' => 'nullable|date|after_or_equal:today',
                'delivery_date' => 'nullable|date|after_or_equal:today',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Validate and fetch service
            $service = \App\Models\Service::findOrFail($validated['service_id']);
            $pricingType = $service->pricing_type ?? 'per_load';
            
            // Determine quantity based on pricing type
            if ($pricingType === 'per_piece') {
                $quantity = (int) ($validated['number_of_pieces'] ?? 1);
                $weight = 0;
            } else {
                $weight = (float) ($validated['weight'] ?? 0);
                $quantity = 1;
                
                // Validate weight against service limits
                if ($service->min_weight && $weight < $service->min_weight) {
                    return response()->json([
                        'success' => false,
                        'message' => "Weight must be at least {$service->min_weight} kg for this service",
                        'errors' => ['weight' => ["Minimum weight is {$service->min_weight} kg"]],
                    ], 422);
                }

                if ($service->max_weight && $weight > $service->max_weight) {
                    return response()->json([
                        'success' => false,
                        'message' => "Weight cannot exceed {$service->max_weight} kg per load for this service",
                        'errors' => ['weight' => ["Maximum weight is {$service->max_weight} kg per load"]],
                    ], 422);
                }
            }

            // Calculate pricing based on service pricing type
            $unitPrice = $pricingType === 'per_piece'
                ? (float) ($service->price_per_piece ?? 0)
                : (float) ($service->price_per_load ?? 0);
            
            $subtotal = $unitPrice * $quantity;
            $discountAmount = 0; // TODO: Apply promotions if any
            $totalAmount = $subtotal - $discountAmount;

            // Generate unique tracking number
            $trackingNumber = $this->generateTrackingNumber();

            // Create laundry
            $laundry = Laundry::create([
                'customer_id' => $customer->id,
                'branch_id' => $validated['branch_id'],
                'service_id' => $validated['service_id'],
                'created_by' => $customer->id,
                'tracking_number' => $trackingNumber,
                'weight' => $weight,
                'number_of_loads' => $quantity,
                'price_per_piece' => $pricingType === 'per_piece' ? $unitPrice : 0,
                'price_per_load' => $pricingType === 'per_piece' ? 0 : $unitPrice,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => 'received',
                'payment_status' => 'unpaid',
                'received_at' => now(),
                'pickup_address' => $validated['pickup_address'] ?? null,
                'delivery_address' => $validated['delivery_address'] ?? null,
                'pickup_date' => $validated['pickup_date'] ?? null,
                'delivery_date' => $validated['delivery_date'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // ========================================================================
            // INVENTORY SYSTEM INTEGRATION
            // ========================================================================
            
            // Calculate loads needed based on weight and service max_weight_per_load
            if ($pricingType === 'per_load' && $service->max_weight_per_load) {
                $loadsNeeded = $inventoryService->calculateLoadsNeeded($weight, $service);
                $laundry->update(['number_of_loads' => $loadsNeeded]);
            }

            // Check if all inventory items are in stock
            if (!$inventoryService->hasAllItemsInStock($laundry)) {
                $lowStockItems = $inventoryService->getLowStockItems($laundry);
                $laundry->delete();
                
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient inventory for this service',
                    'low_stock_items' => $lowStockItems->map(fn($item) => [
                        'name' => $item['item']->name,
                        'quantity_needed' => $item['quantity_needed'],
                        'current_stock' => $item['current_stock'],
                        'unit' => $item['item']->unit,
                    ]),
                ], 400);
            }

            // Calculate inventory cost
            $itemsCost = $inventoryService->calculateItemsCost($laundry);
            $laundry->update(['inventory_cost' => $itemsCost]);

            // Deduct inventory
            $inventoryService->deductInventory($laundry);

            // ========================================================================
            // END INVENTORY SYSTEM INTEGRATION
            // ========================================================================

            // Load relationships
            $laundry->load(['service', 'branch']);

            // TODO: Send notification to customer
            // TODO: Send notification to branch staff

            return response()->json([
                'success' => true,
                'message' => 'Laundry created successfully',
                'data' => [
                    'laundry' => [
                        'id' => $laundry->id,
                        'tracking_number' => $laundry->tracking_number,
                        'status' => $laundry->status,
                        'service_name' => $laundry->service->name,
                        'branch_name' => $laundry->branch->name,
                        'number_of_loads' => $laundry->number_of_loads,
                        'inventory_cost' => (float) $laundry->inventory_cost,
                        'total_amount' => (float) $laundry->total_amount,
                        'profit' => (float) ($laundry->total_amount - $laundry->inventory_cost),
                        'created_at' => $laundry->created_at->toIso8601String(),
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
            Log::error('Error creating laundry: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create laundry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get laundry receipt for customer
     *
     * GET /api/v1/laundries/{id}/receipt
     */
    public function receipt(Request $request, $id)
    {
        try {
            $customer = $request->user();

            $laundry = Laundry::where('customer_id', $customer->id)
                ->where(function ($query) use ($id) {
                    $query->where('tracking_number', $id)
                          ->orWhere('id', $id);
                })
                ->with(['branch', 'service', 'customer', 'addons', 'promotion', 'staff'])
                ->first();

            if (!$laundry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laundry not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'receipt' => [
                        'id' => $laundry->id,
                        'tracking_number' => $laundry->tracking_number,
                        'status' => $laundry->status,
                        'payment_status' => $laundry->payment_status ?? 'unpaid',
                        'payment_method' => $laundry->payment_method,
                        'paid_at' => $laundry->paid_at?->toIso8601String(),
                        'customer' => [
                            'name' => $laundry->customer->name,
                            'phone' => $laundry->customer->phone,
                            'address' => $laundry->customer->address,
                        ],
                        'branch' => [
                            'name' => $laundry->branch->name,
                            'address' => $laundry->branch->address,
                            'phone' => $laundry->branch->phone,
                        ],
                        'service' => [
                            'name' => $laundry->service->name,
                            'type' => $laundry->service->service_type,
                            'pricing_type' => $laundry->service->pricing_type,
                        ],
                        'staff' => $laundry->staff ? $laundry->staff->name : null,
                        'items' => [
                            'weight' => (float) $laundry->weight,
                            'number_of_loads' => $laundry->number_of_loads ?? 1,
                            'price_per_load' => (float) ($laundry->service->price_per_load ?? 0),
                            'price_per_piece' => (float) ($laundry->service->price_per_piece ?? 0),
                        ],
                        'pricing' => [
                            'subtotal' => (float) $laundry->subtotal,
                            'addons_total' => (float) ($laundry->addons_total ?? 0),
                            'pickup_fee' => (float) ($laundry->pickup_fee ?? 0),
                            'delivery_fee' => (float) ($laundry->delivery_fee ?? 0),
                            'discount_amount' => (float) ($laundry->discount_amount ?? 0),
                            'total_amount' => (float) $laundry->total_amount,
                        ],
                        'addons' => $laundry->addons->map(function ($addon) {
                            return [
                                'name' => $addon->name,
                                'quantity' => $addon->pivot->quantity,
                                'price' => (float) $addon->pivot->price_at_purchase,
                                'total' => (float) ($addon->pivot->price_at_purchase * $addon->pivot->quantity),
                            ];
                        }),
                        'promotion' => $laundry->promotion ? [
                            'name' => $laundry->promotion->name,
                            'discount_type' => $laundry->promotion->discount_type,
                            'discount_value' => $laundry->promotion->discount_value,
                        ] : null,
                        'timeline' => [
                            'created_at' => $laundry->created_at->toIso8601String(),
                            'received_at' => $laundry->received_at?->toIso8601String(),
                            'ready_at' => $laundry->ready_at?->toIso8601String(),
                            'completed_at' => $laundry->completed_at?->toIso8601String(),
                        ],
                        'notes' => $laundry->notes,
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching laundry receipt: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch receipt',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel laundry
     *
     * PUT /api/v1/laundries/{id}/cancel
     */
    public function cancel(Request $request, $id, LaundryInventoryService $inventoryService)
    {
        try {
            $customer = $request->user();

            $laundry = Laundry::where('customer_id', $customer->id)
                ->where(function ($query) use ($id) {
                    $query->where('tracking_number', $id)
                          ->orWhere('id', $id);
                })
                ->first();

            if (!$laundry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laundry not found',
                ], 404);
            }

            // Only allow cancellation of certain statuses
            if (!in_array($laundry->status, ['received', 'processing', 'washing'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot cancel laundry in current status. Laundry is already being processed.',
                ], 400);
            }

            // ========================================================================
            // INVENTORY SYSTEM INTEGRATION - RESTORE INVENTORY ON CANCELLATION
            // ========================================================================
            
            // If laundry was already processed, restore inventory
            if (in_array($laundry->status, ['processing', 'washing']) && $laundry->inventory_cost) {
                $itemsNeeded = $inventoryService->getItemsNeeded($laundry);
                
                foreach ($itemsNeeded as $itemId => $quantity) {
                    $item = \App\Models\InventoryItem::find($itemId);
                    if ($item) {
                        $item->addStock($quantity, 'laundry_cancelled_' . $laundry->id);
                    }
                }
                
                Log::info("Inventory restored for cancelled laundry #{$laundry->tracking_number}");
            }
            
            // ========================================================================
            // END INVENTORY SYSTEM INTEGRATION
            // ========================================================================

            $laundry->update([
                'status' => 'cancelled',
            ]);

            // TODO: Send cancellation notification to customer
            // TODO: Send cancellation notification to branch

            return response()->json([
                'success' => true,
                'message' => 'Laundry cancelled successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error cancelling laundry: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel laundry',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Set payment method for laundry
     *
     * POST /api/v1/laundries/{id}/payment-method
     */
    public function setPaymentMethod(Request $request, $id)
    {
        try {
            $customer = $request->user();

            $validated = $request->validate([
                'payment_method' => 'required|in:cash,gcash',
            ]);

            $laundry = Laundry::where('customer_id', $customer->id)
                ->where(function ($query) use ($id) {
                    $query->where('tracking_number', $id)
                          ->orWhere('id', $id);
                })
                ->with(['customer', 'branch'])
                ->first();

            if (!$laundry) {
                return response()->json([
                    'success' => false,
                    'message' => 'Laundry not found',
                ], 404);
            }

            // Only allow setting payment method for ready orders
            if (!in_array($laundry->status, ['ready', 'ready_for_pickup'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method can only be set for ready orders',
                ], 400);
            }

            // Check if already paid
            if ($laundry->payment_status === 'paid') {
                return response()->json([
                    'success' => false,
                    'message' => 'This order is already paid',
                ], 400);
            }

            $laundry->update([
                'payment_method' => $validated['payment_method'],
            ]);

            // Send notifications for all payment methods
            $this->sendPaymentMethodNotifications($laundry, $validated['payment_method']);

            return response()->json([
                'success' => true,
                'message' => 'Payment method set successfully',
                'data' => [
                    'payment_method' => $laundry->payment_method,
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error setting payment method: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to set payment method',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send notifications when customer selects a payment method
     */
    private function sendPaymentMethodNotifications(Laundry $laundry, string $paymentMethod): void
    {
        try {
            $customerName = $laundry->customer->name ?? 'Customer';
            $branchName = $laundry->branch->name ?? 'Branch';
            $amount = number_format($laundry->total_amount, 2);
            
            // Determine notification details based on payment method
            $paymentMethodDisplay = match($paymentMethod) {
                'cash' => 'Cash',
                'gcash' => 'GCash',
                'bank_transfer' => 'Bank Transfer',
                'credit_card' => 'Credit Card',
                default => ucfirst($paymentMethod),
            };

            $notificationTitle = "{$paymentMethodDisplay} Payment Selected";
            $notificationMessage = "{$customerName} selected {$paymentMethodDisplay} payment for laundry #{$laundry->tracking_number} (₱{$amount}) at {$branchName}";
            $staffMessage = "{$customerName} will pay ₱{$amount} via {$paymentMethodDisplay} for laundry #{$laundry->tracking_number}";
            
            // Notification for Admin
            \App\Models\AdminNotification::create([
                'type' => 'payment_method_selected',
                'title' => $notificationTitle,
                'message' => $notificationMessage,
                'icon' => 'credit-card',
                'color' => 'info',
                'link' => route('admin.laundries.show', $laundry->id),
                'data' => [
                    'laundries_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'customer_name' => $customerName,
                    'amount' => $laundry->total_amount,
                    'payment_method' => $paymentMethod,
                ],
                'branch_id' => $laundry->branch_id,
            ]);

            // Notification for Branch Staff
            \App\Services\NotificationService::sendToBranchStaff(
                $laundry->branch_id,
                'payment_method_selected',
                $notificationTitle,
                $staffMessage,
                $laundry->id,
                null,
                $laundry->customer_id,
                [
                    'laundries_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'customer_name' => $customerName,
                    'amount' => $laundry->total_amount,
                    'payment_method' => $paymentMethod,
                ]
            );

            Log::info("Payment method notification sent for laundry #{$laundry->tracking_number} - Method: {$paymentMethod}");
        } catch (\Exception $e) {
            Log::error('Error sending payment method notifications: ' . $e->getMessage());
        }
    }

    /**
     * Send notifications when customer selects cash payment (deprecated - use sendPaymentMethodNotifications)
     */
    private function sendCashPaymentNotifications(Laundry $laundry): void
    {
        try {
            $customerName = $laundry->customer->name ?? 'Customer';
            $branchName = $laundry->branch->name ?? 'Branch';
            $amount = number_format($laundry->total_amount, 2);
            
            // Notification for Admin
            \App\Models\AdminNotification::create([
                'type' => 'cash_payment_selected',
                'title' => 'Cash Payment Selected',
                'message' => "{$customerName} selected cash payment for laundry #{$laundry->tracking_number} (₱{$amount}) at {$branchName}",
                'icon' => 'cash',
                'color' => 'success',
                'link' => route('admin.laundries.show', $laundry->id),
                'data' => [
                    'laundries_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'customer_name' => $customerName,
                    'amount' => $laundry->total_amount,
                    'payment_method' => 'cash',
                ],
                'branch_id' => $laundry->branch_id,
            ]);

            // Notification for Branch Staff
            \App\Services\NotificationService::sendToBranchStaff(
                $laundry->branch_id,
                'cash_payment_selected',
                'Cash Payment Selected',
                "{$customerName} will pay ₱{$amount} in cash for laundry #{$laundry->tracking_number} on pickup",
                $laundry->id,
                null,
                $laundry->customer_id,
                [
                    'laundries_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'customer_name' => $customerName,
                    'amount' => $laundry->total_amount,
                    'payment_method' => 'cash',
                ]
            );

            Log::info("Cash payment notifications sent for laundry #{$laundry->tracking_number}");
        } catch (\Exception $e) {
            Log::error('Error sending cash payment notifications: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique tracking number
     */
    private function generateTrackingNumber(): string
    {
        do {
            $tracking = 'WB-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Laundry::where('tracking_number', $tracking)->exists());

        return $tracking;
    }
}
