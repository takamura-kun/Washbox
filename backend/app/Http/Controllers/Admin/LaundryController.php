<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Laundry;

use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Promotion;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PickupRequest;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Services\LaundryNotificationService;

use Illuminate\Support\MessageBag;

class LaundryController extends Controller
{
    public function index(Request $request)
    {
        $query = Laundry::with(['customer', 'service', 'branch', 'staff']);

        if ($request->filled('status'))    $query->byStatus($request->status);
        if ($request->filled('branch_id')) $query->byBranch($request->branch_id);
        if ($request->filled('service_id'))$query->where('service_id', $request->service_id);
        if ($request->filled('staff_id'))  $query->byStaff($request->staff_id);
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->whereDate('created_at', '<=', $request->date_to);
        if ($request->filled('search'))    $query->search($request->search);

        $sortBy      = $request->get('sort_by', 'created_at');
        $sortLaundry = $request->get('sort_laundry', 'desc');
        $query->orderBy($sortBy, $sortLaundry);

        $laundries = $query->paginate(15);
        $services  = Service::active()->get();
        $branches  = Branch::active()->get();
        $staff     = User::staff()->active()->get();

        $stats = [
            'total'          => Laundry::count(),
            'pending'        => Laundry::where('status', 'received')->count(),
            'processing'     => Laundry::where('status', 'processing')->count(),
            'ready'          => Laundry::where('status', 'ready')->count(),
            'completed'      => Laundry::where('status', 'completed')->count(),
            'total_revenue'  => Laundry::where('status', 'completed')->sum('total_amount'),
            'today_laundries'=> Laundry::whereDate('created_at', today())->count(),
        ];

        return view('admin.laundries.index', compact('laundries', 'services', 'branches', 'staff', 'stats'));
    }

    public function create(Request $request)
    {
        $pickup = null;
        if ($request->has('pickup_id')) {
            $pickup = PickupRequest::with(['customer', 'branch', 'promotion'])->find($request->pickup_id);
        }

        $promotions = Promotion::active()
            ->valid()
            ->where(function($query) use ($pickup) {
                $query->whereNull('branch_id')
                      ->orWhere('branch_id', $pickup?->branch_id);
            })
            ->orderBy('display_laundry')
            ->get();

        return view('admin.laundries.create', [
            'pickup'     => $pickup,
            'customers'  => Customer::active()->get(),
            'branches'   => Branch::active()->get(),
            'services'   => Service::active()->get(),
            'addons'     => \App\Models\InventoryItem::where('is_active', true)
                ->whereHas('category', function($q) {
                    $q->whereIn('name', ['Detergent', 'Fabric Conditioner', 'Bleach', 'Softener']);
                })
                ->with('category')
                ->orderBy('name')
                ->get(),
            'staff'      => User::staff()->active()->get(),
            'promotions' => $promotions,
            'extraServices' => \App\Models\ExtraServiceSetting::active()->ordered()->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'           => 'required|exists:customers,id',
            'branch_id'             => 'required|exists:branches,id',
            'service_id'            => 'nullable|exists:services,id',
            'weight'                => 'nullable|numeric|min:0',
            'number_of_loads'       => 'required|integer|min:1',
            'pickup_fee'            => 'nullable|numeric|min:0',
            'delivery_fee'          => 'nullable|numeric|min:0',
            'extra_services'        => 'nullable|string',
            'staff_id'              => 'nullable|exists:users,id',
            'promotion_id'          => 'nullable|exists:promotions,id',
            'notes'                 => 'nullable|string|max:1000',
            'addons'                => 'nullable|array',
            'addons.*.id'           => 'required_with:addons|integer|exists:inventory_items,id',
            'addons.*.quantity'     => 'nullable|numeric|min:0.01|max:999',
            'pickup_request_id'     => 'nullable|exists:pickup_requests,id',
        ]);

        // Validate that either service_id is present OR it's a per_load_override promotion
        if (empty($validated['service_id']) && empty($validated['promotion_id'])) {
            return back()->withErrors(['service_id' => 'Either a service or a promotion is required'])->withInput();
        }

        if (empty($validated['service_id']) && !empty($validated['promotion_id'])) {
            $promotion = Promotion::find($validated['promotion_id']);
            if (!$promotion || $promotion->application_type !== 'per_load_override') {
                return back()->withErrors(['promotion_id' => 'Selected promotion cannot be applied without selecting a service.'])->withInput();
            }
        }

        $loads = (int) $validated['number_of_loads'];
        $weight = (isset($validated['weight']) && $validated['weight'] !== null && $validated['weight'] !== '')
            ? (float) $validated['weight']
            : 0;

        // Validate weight against service limits AND check stock availability
        if (!empty($validated['service_id'])) {
            $service = Service::findOrFail($validated['service_id']);
            $pricingType = $service->pricing_type ?? 'per_load';

            // Only validate weight for per_load services (not per_piece)
            if ($pricingType !== 'per_piece') {
                // Check minimum weight
                if ($service->min_weight && $service->min_weight > 0) {
                    if ($weight < $service->min_weight) {
                        $errors = new MessageBag();
                        $errors->add('weight', "Weight must be at least {$service->min_weight} kg for this service");
                        $errors->add('weight_warning', "Laundry cannot be created: Not in minimum kg limit. Service '{$service->name}' requires minimum {$service->min_weight} kg, but {$weight} kg was entered.");
                        return back()->withErrors($errors)->withInput();
                    }
                }

                // Check maximum weight - if exceeded and excess weight is NOT allowed, reject
                if ($service->max_weight && $service->max_weight > 0) {
                    if ($weight > $service->max_weight && !$service->allow_excess_weight) {
                        $errors = new MessageBag();
                        $errors->add('weight', "Weight cannot exceed {$service->max_weight} kg per load for this service");
                        $errors->add('weight_warning', "Laundry cannot be created: Maximum weight exceeded! Service '{$service->name}' allows maximum {$service->max_weight} kg, but {$weight} kg was entered. Either reduce weight or increase number of loads.");
                        return back()->withErrors($errors)->withInput();
                    }
                }
            }

            // Check if service has supplies and if they're in stock
            $supplies = $service->supplies;
            if ($supplies->isNotEmpty()) {
                $insufficientStock = [];
                $outOfStock = [];

                foreach ($supplies as $supply) {
                    $quantityRequired = $supply->pivot->quantity_required * $loads;
                    
                    // Get branch stock for this supply
                    $branchStock = \App\Models\BranchStock::where('branch_id', $validated['branch_id'])
                        ->where('inventory_item_id', $supply->id)
                        ->first();

                    // Only block if a stock record EXISTS and is insufficient.
                    // If no record exists, stock tracking isn't set up yet — allow creation.
                    if (!$branchStock) continue;

                    $currentStock = $branchStock->current_stock;

                    if ($currentStock <= 0) {
                        $outOfStock[] = "{$supply->name} (Required: {$quantityRequired} {$supply->distribution_unit}, Available: 0)";
                    } elseif ($currentStock < $quantityRequired) {
                        $insufficientStock[] = "{$supply->name} (Required: {$quantityRequired} {$supply->distribution_unit}, Available: {$currentStock} {$supply->distribution_unit})";
                    }
                }

                if (!empty($outOfStock) || !empty($insufficientStock)) {
                    $errors = new MessageBag();
                    
                    if (!empty($outOfStock)) {
                        $errors->add('stock_error', 'Cannot create laundry: The following items are OUT OF STOCK:');
                        foreach ($outOfStock as $item) {
                            $errors->add('stock_items', "• {$item}");
                        }
                    }
                    
                    if (!empty($insufficientStock)) {
                        $errors->add('stock_error', 'Cannot create laundry: INSUFFICIENT STOCK for the following items:');
                        foreach ($insufficientStock as $item) {
                            $errors->add('stock_items', "• {$item}");
                        }
                    }
                    
                    $errors->add('stock_solution', 'Please request stock from admin or reduce the number of loads.');
                    return back()->withErrors($errors)->withInput();
                }
            }
        }

        // Check add-ons stock availability
        if (!empty($validated['addons'])) {
            $addonStockErrors = [];
            
            foreach ($validated['addons'] as $addonEntry) {
                $itemId = (int) $addonEntry['id'];
                $quantity = (float) ($addonEntry['quantity'] ?? 1);
                if ($quantity < 0.01) $quantity = 1;

                $item = \App\Models\InventoryItem::find($itemId);
                if (!$item) continue;

                // Get branch stock for this add-on
                $branchStock = \App\Models\BranchStock::where('branch_id', $validated['branch_id'])
                    ->where('inventory_item_id', $itemId)
                    ->first();

                // Only block if a stock record EXISTS and is insufficient.
                if (!$branchStock) continue;

                $currentStock = $branchStock->current_stock;

                if ($currentStock <= 0) {
                    $addonStockErrors[] = "{$item->name} is OUT OF STOCK (Required: {$quantity} {$item->distribution_unit})";
                } elseif ($currentStock < $quantity) {
                    $addonStockErrors[] = "{$item->name} has INSUFFICIENT STOCK (Required: {$quantity} {$item->distribution_unit}, Available: {$currentStock} {$item->distribution_unit})";
                }
            }

            if (!empty($addonStockErrors)) {
                $errors = new MessageBag();
                $errors->add('addon_stock_error', 'Cannot create laundry: Add-on stock issues:');
                foreach ($addonStockErrors as $error) {
                    $errors->add('addon_stock_items', "• {$error}");
                }
                $errors->add('stock_solution', 'Please remove these add-ons or request stock from admin.');
                return back()->withErrors($errors)->withInput();
            }
        }

        $service               = null;
        $serviceSubtotal       = 0;
        $snapshotPricePerPiece = 0;
        $snapshotPricePerLoad  = 0;
        $excessWeight          = 0;
        $excessWeightFee       = 0;

        if (!empty($validated['service_id'])) {
            $service     = Service::findOrFail($validated['service_id']);
            $pricingType = $service->pricing_type ?? 'per_load';

            $unitPrice = $pricingType === 'per_piece'
                ? (float) ($service->price_per_piece ?? 0)
                : (float) ($service->price_per_load  ?? 0);

            $snapshotPricePerPiece = $pricingType === 'per_piece' ? $unitPrice : 0;
            $snapshotPricePerLoad  = $pricingType === 'per_piece' ? 0 : $unitPrice;
            $serviceSubtotal       = $unitPrice * $loads;

            // Calculate excess weight fee if applicable
            if ($pricingType !== 'per_piece' && $service->max_weight && $service->max_weight > 0 && $service->allow_excess_weight) {
                if ($weight > $service->max_weight) {
                    $excessWeight = $weight - $service->max_weight;
                    $excessWeightFee = $excessWeight * ($service->excess_weight_charge_per_kg ?? 0);
                }
            }
        }

        // ── Promotion ────────────────────────────────────────────────────────
        $discountAmount         = 0;
        $promotionOverrideTotal = null;
        $promotionPricePerLoad  = null;
        $finalSubtotal          = $serviceSubtotal;

        if (!empty($validated['promotion_id'])) {
            $promotion = Promotion::find($validated['promotion_id']);
            if ($promotion) {
                if ($promotion->application_type === 'per_load_override') {
                    $promotionPricePerLoad  = $promotion->display_price;
                    $promotionOverrideTotal = $promotion->display_price * $loads;
                    $discountAmount         = 0;
                    $finalSubtotal          = $promotionOverrideTotal;
                    if (empty($validated['service_id'])) {
                        $serviceSubtotal = 0;
                    }
                } elseif ($promotion->discount_type === 'percentage' && !empty($validated['service_id'])) {
                    $discountAmount = round(($serviceSubtotal * $promotion->discount_value) / 100, 2);
                    $finalSubtotal  = $serviceSubtotal - $discountAmount;
                }
            }
        }

        $pickupFee   = (float) ($validated['pickup_fee']   ?? 0);
        $deliveryFee = (float) ($validated['delivery_fee'] ?? 0);
        
        $extraServicesTotal = 0;
        $extraServicesNote = '';
        if (!empty($validated['extra_services'])) {
            try {
                $extraServices = json_decode($validated['extra_services'], true);
                if (is_array($extraServices) && !empty($extraServices)) {
                    foreach ($extraServices as $service) {
                        $extraServicesTotal += (float) ($service['price'] ?? 0);
                    }
                    $serviceNames = array_map(function($s) {
                        return ucfirst($s['name'] ?? 'Unknown') . ' (₱' . number_format($s['price'] ?? 0, 2) . ')';
                    }, $extraServices);
                    $extraServicesNote = "\n[Extra Services: " . implode(', ', $serviceNames) . "]";
                }
            } catch (\Exception $e) {
                \Log::error('Extra services JSON parse error: ' . $e->getMessage());
            }
        }

        // ── Add-ons: build pivot data with quantity and calculate total ──────
        $addonsTotal = 0;
        $addonPivot  = [];  // [ inventoryItemId => ['price_at_purchase' => x, 'quantity' => y] ]

        if (!empty($validated['addons'])) {
            foreach ($validated['addons'] as $addonEntry) {
                $itemId   = (int) $addonEntry['id'];
                $quantity = (float) ($addonEntry['quantity'] ?? 1);
                if ($quantity < 0.01) $quantity = 1;

                $item = \App\Models\InventoryItem::find($itemId);
                if (!$item) continue;

                $price = (float) ($item->unit_cost_price ?? 0);
                $lineTotal    = $price * $quantity;
                $addonsTotal += $lineTotal;

                $addonPivot[$itemId] = [
                    'price_at_purchase' => $price,
                    'quantity'          => $quantity,
                ];
            }
        }

        $totalAmount = $finalSubtotal + $pickupFee + $deliveryFee + $addonsTotal + $extraServicesTotal + $excessWeightFee;

        $laundry = DB::transaction(function () use (
            $validated, $service, $loads, $weight,
            $snapshotPricePerLoad, $snapshotPricePerPiece,
            $serviceSubtotal, $discountAmount, $promotionOverrideTotal,
            $promotionPricePerLoad, $pickupFee, $deliveryFee, $extraServicesTotal, $extraServicesNote,
            $addonsTotal, $totalAmount, $finalSubtotal, $addonPivot, $excessWeight, $excessWeightFee
        ) {
            $laundry = Laundry::create([
                'tracking_number'          => $this->generateTrackingNumber(),
                'customer_id'              => $validated['customer_id'],
                'branch_id'                => $validated['branch_id'],
                'service_id'               => $validated['service_id'] ?? null,
                'created_by'               => Auth::id(),
                'staff_id'                 => $validated['staff_id'] ?? null,
                'weight'                   => $weight,
                'excess_weight'            => $excessWeight,
                'excess_weight_fee'        => $excessWeightFee,
                'number_of_loads'          => $loads,
                'price_per_piece'          => $snapshotPricePerPiece,
                'price_per_load'           => $snapshotPricePerLoad,
                'subtotal'                 => $serviceSubtotal,
                'addons_total'             => $addonsTotal + $extraServicesTotal,
                'discount_amount'          => $discountAmount,
                'total_amount'             => $totalAmount,
                'promotion_id'             => $validated['promotion_id'] ?? null,
                'promotion_override_total' => $promotionOverrideTotal,
                'promotion_price_per_load' => $promotionPricePerLoad,
                'pickup_request_id'        => $validated['pickup_request_id'] ?? null,
                'pickup_fee'               => $pickupFee,
                'delivery_fee'             => $deliveryFee,
                'notes'                    => ($validated['notes'] ?? '') . $extraServicesNote,
                'status'                   => 'received',
                'payment_status'           => 'pending',
                'received_at'              => now(),
            ]);

            // Attach inventory items as add-ons
            if (!empty($addonPivot)) {
                $laundry->inventoryItems()->attach($addonPivot);
                
                // Deduct add-on inventory from branch stock
                $this->deductAddonInventory($addonPivot, $validated['branch_id'], $laundry->id);
            }

            $laundry->statusHistories()->create([
                'status'     => 'received',
                'changed_by' => Auth::id(),
                'notes'      => 'Laundry created' . ($service ? '' : ' with fixed price promotion'),
            ]);

            // Automatically deduct supplies from branch stock
            if (!empty($validated['service_id'])) {
                $serviceModel = Service::with('supplies')->find($validated['service_id']);
                if ($serviceModel && $serviceModel->supplies->isNotEmpty()) {
                    $this->deductServiceSupplies($serviceModel, $validated['branch_id'], $loads);
                }
            }

            return $laundry;
        });

        // Notify customer about new laundry
        if ($laundry->customer_id) {
            \App\Services\NotificationService::sendToCustomer(
                $laundry->customer_id,
                'laundry_received',
                'Laundry Received',
                "Your laundry #{$laundry->tracking_number} has been received and is being processed.",
                $laundry->id,
                null,
                [
                    'laundry_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                ]
            );
        }

        return redirect()->route('admin.laundries.show', $laundry)
            ->with('success', 'Laundry created! Tracking #: ' . $laundry->tracking_number);
    }


    public function show(Laundry $laundry)
    {
        $laundry->load([
            'customer',
            'service',
            'branch',
            'staff',
            'createdBy',
            'statusHistories.changedBy',
            'payment',
            'inventoryItems',
            'promotion',
            'pickupRequest',
        ]);

        return view('admin.laundries.show', compact('laundry'));
    }

    public function edit(Laundry $laundry)
    {
        $customers = Customer::all();
        $services  = Service::active()->get();
        $branches  = Branch::active()->get();
        $staff     = User::staff()->active()->get();

        return view('admin.laundries.edit', compact('laundry', 'customers', 'services', 'branches', 'staff'));
    }

    public function update(Request $request, Laundry $laundry)
    {
        $validated = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'branch_id'      => 'required|exists:branches,id',
            'service_id'     => 'required_without:number_of_loads|exists:services,id',
            'number_of_loads'=> 'nullable|integer|min:1',
            'staff_id'       => 'nullable|exists:users,id',
            'weight'         => 'nullable|numeric|min:0',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $weight = (isset($validated['weight']) && $validated['weight'] !== null) ? (float) $validated['weight'] : 0;
        $service     = Service::findOrFail($validated['service_id']);
        $loads       = $validated['number_of_loads'] ?? $laundry->number_of_loads ?? 1;
        $pricingType = $service->pricing_type ?? 'per_load';

        // Validate weight against service limits (only for per-load services)
        if ($pricingType !== 'per_piece') {
            // Check minimum weight
            if ($service->min_weight && $service->min_weight > 0) {
                if ($weight < $service->min_weight) {
                    $errors = new MessageBag();
                    $errors->add('weight', "Weight must be at least {$service->min_weight} kg for this service");
                    $errors->add('weight_warning', "Laundry cannot be updated: Not in minimum kg limit. Service '{$service->name}' requires minimum {$service->min_weight} kg, but {$weight} kg was entered.");
                    return back()->withErrors($errors)->withInput();
                }
            }

            // Check maximum weight
            if ($service->max_weight && $service->max_weight > 0) {
                if ($weight > $service->max_weight) {
                    $errors = new MessageBag();
                    $errors->add('weight', "Weight cannot exceed {$service->max_weight} kg per load for this service");
                    $errors->add('weight_warning', "Laundry cannot be updated: Maximum weight exceeded! Service '{$service->name}' allows maximum {$service->max_weight} kg, but {$weight} kg was entered.");
                    return back()->withErrors($errors)->withInput();
                }
            }
        }

        $unitPrice = $pricingType === 'per_piece'
            ? (float) ($service->price_per_piece ?? 0)
            : (float) ($service->price_per_load  ?? 0);

        $subtotal       = $unitPrice * $loads;
        $discountAmount = $laundry->discount_amount ?? 0;

        // Re-apply promotion if set
        $promo = $laundry->promotion;
        if ($promo && $promo->application_type === 'per_load_override') {
            $subtotal       = $promo->display_price * $loads;
            $discountAmount = 0;
        } elseif ($promo && $promo->discount_type === 'percentage') {
            $discountAmount = round(($subtotal * $promo->discount_value) / 100, 2);
        }

        $pickupFee   = $laundry->pickup_fee   ?? 0;
        $deliveryFee = $laundry->delivery_fee ?? 0;
        $totalAmount = $subtotal - $discountAmount + $pickupFee + $deliveryFee + ($laundry->addons_total ?? 0);

        $validated['weight']          = $weight;
        $validated['price_per_piece'] = $pricingType === 'per_piece' ? $unitPrice : null;
        $validated['price_per_load']  = $pricingType === 'per_piece' ? null       : $unitPrice;
        $validated['number_of_loads'] = $loads;
        $validated['subtotal']        = $subtotal;
        $validated['total_amount']    = $totalAmount;

        $laundry->update($validated);

        return redirect()->route('admin.laundries.show', $laundry)
            ->with('success', 'Laundry updated successfully!');
    }

    public function updateStatus(Request $request, Laundry $laundry)
    {
        $validated = $request->validate([
            'status' => 'required|in:received,processing,ready,paid,completed,cancelled',
            'notes'  => 'nullable|string|max:500',
        ]);

        $laundry->updateStatus($validated['status'], Auth::user(), $validated['notes'] ?? null);

        app(LaundryNotificationService::class)->onStatusChange($laundry, $validated['status']);

        return redirect()->route('admin.laundries.show', $laundry)
            ->with('success', 'Laundry status updated to: ' . ucfirst($validated['status']));
    }

    public function assignStaff(Request $request, Laundry $laundry)
    {
        $validated = $request->validate(['staff_id' => 'required|exists:users,id']);

        $laundry->assignToStaff($validated['staff_id']);
        $staff = User::find($validated['staff_id']);

        return redirect()->route('admin.laundries.show', $laundry)
            ->with('success', 'Laundry assigned to: ' . $staff->name);
    }

    public function destroy(Laundry $laundry)
    {
        if ($laundry->status !== 'cancelled') {
            return redirect()->route('admin.laundries.index')
                ->with('error', 'Only cancelled laundries can be deleted. Please cancel the laundry first.');
        }

        $trackingNumber = $laundry->tracking_number;
        $laundry->delete();

        return redirect()->route('admin.laundries.index')
            ->with('success', "Laundry {$trackingNumber} deleted successfully!");
    }

    private function generateTrackingNumber(): string
    {
        do {
            $tracking = 'WB-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Laundry::where('tracking_number', $tracking)->exists());

        return $tracking;
    }

    public function receipt(Laundry $laundry)
    {
        $laundry->load(['customer', 'service', 'branch', 'staff']);
        return view('admin.laundries.receipt', compact('laundry'));
    }

    public function recordPayment(Request $request, Laundry $laundry)
    {
        DB::transaction(function () use ($request, $laundry) {
            \App\Models\Payment::create([
                'laundries_id'   => $laundry->id,
                'method'         => 'cash',
                'amount'         => $laundry->total_amount,
                'receipt_number' => 'REC-' . now()->format('Ymd') . '-' . $laundry->id,
                'received_by'    => Auth::id(),
                'notes'          => $request->notes ?? 'Paid at counter',
            ]);

            $laundry->update([
                'payment_status' => 'paid',
                'payment_method' => 'cash',
                'paid_at'        => now(),
                'status'         => 'paid',
            ]);

            $laundry->statusHistories()->create([
                'status'     => 'paid',
                'changed_by' => Auth::id(),
                'notes'      => 'Payment recorded successfully',
            ]);

            // Record financial transactions - LaundryObserver handles recordLaundrySale automatically
            $financialService = app(\App\Services\FinancialTransactionService::class);
            $financialService->recordPickupDeliveryFee($laundry);

            app(LaundryNotificationService::class)->onStatusChange($laundry, 'paid');
        });

        return redirect()->route('admin.laundries.show', $laundry)
            ->with('success', 'Payment recorded and Laundry updated!');
    }

    /**
     * Automatically deduct supplies from branch stock when service is used
     */
    private function deductServiceSupplies(Service $service, int $branchId, int $numberOfLoads = 1)
    {
        $supplies = $service->supplies;
        
        if ($supplies->isEmpty()) {
            return;
        }

        foreach ($supplies as $supply) {
            $quantityRequired = $supply->pivot->quantity_required * $numberOfLoads;
            
            // Get or create branch stock for this supply
            $branchStock = \App\Models\BranchStock::firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'inventory_item_id' => $supply->id,
                ],
                [
                    'current_stock' => 0,
                    'reorder_point' => $supply->reorder_point ?? 0,
                    'max_stock_level' => $supply->max_level ?? 0,
                ]
            );

            // Deduct from branch stock
            $previousStock = $branchStock->current_stock;
            $newStock = max(0, $previousStock - $quantityRequired);
            $branchStock->update(['current_stock' => $newStock]);

            // Log the stock history
            \App\Models\StockHistory::create([
                'inventory_item_id' => $supply->id,
                'branch_id' => $branchId,
                'type' => 'usage',
                'quantity' => -$quantityRequired,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'balance_after' => $newStock,
                'reference_type' => 'service_usage',
                'reference_id' => $service->id,
                'performed_by' => Auth::id(),
                'notes' => "Auto-deducted for service: {$service->name} ({$numberOfLoads} load(s))",
            ]);
        }
    }

    /**
     * Deduct add-on inventory items from branch stock
     */
    private function deductAddonInventory(array $addonPivot, int $branchId, int $laundryId)
    {
        foreach ($addonPivot as $itemId => $pivotData) {
            $quantity = $pivotData['quantity'];
            
            $item = \App\Models\InventoryItem::find($itemId);
            if (!$item) continue;
            
            // Get or create branch stock
            $branchStock = \App\Models\BranchStock::firstOrCreate(
                [
                    'branch_id' => $branchId,
                    'inventory_item_id' => $itemId,
                ],
                [
                    'current_stock' => 0,
                    'reorder_point' => $item->reorder_point ?? 0,
                    'max_stock_level' => $item->max_level ?? 0,
                ]
            );

            // Deduct from branch stock
            $previousStock = $branchStock->current_stock;
            $newStock = max(0, $previousStock - $quantity);
            $branchStock->update(['current_stock' => $newStock]);

            // Log the stock history
            \App\Models\StockHistory::create([
                'inventory_item_id' => $itemId,
                'branch_id' => $branchId,
                'type' => 'usage',
                'quantity' => -$quantity,
                'previous_stock' => $previousStock,
                'new_stock' => $newStock,
                'balance_after' => $newStock,
                'reference_type' => 'laundry_addon',
                'reference_id' => $laundryId,
                'performed_by' => Auth::id(),
                'notes' => "Add-on used: {$item->name} ({$quantity} {$item->distribution_unit})",
            ]);
        }
    }
}
