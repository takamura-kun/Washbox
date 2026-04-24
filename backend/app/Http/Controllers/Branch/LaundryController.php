<?php

namespace App\Http\Controllers\Branch;

use App\Models\Laundry;
use App\Models\Branch;
use App\Models\Service;
use App\Models\Customer;
use App\Models\PickupRequest;
use App\Models\Promotion;
use App\Models\PromotionUsage;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\MessageBag;

class LaundryController extends Controller
{
    protected $notificationService;

    /**
     * Display laundry list with filters (branch-specific)
     */
    public function index(Request $request)
    {
        $branch = Auth::guard('branch')->user();

        if (!$branch) {
            return redirect()->route('branch.login')
                ->with('error', 'Please login to access this page.');
        }

        $branchId = $branch->id;

        $query = Laundry::with(['customer', 'service', 'branch'])
            ->where('branch_id', $branchId);

        if ($request->filled('search'))     $query->search($request->search);
        if ($request->filled('status'))     $query->byStatus($request->status);
        if ($request->filled('service_id')) $query->where('service_id', $request->service_id);
        if ($request->filled('date_from'))  $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))    $query->whereDate('created_at', '<=', $request->date_to);

        $laundries = $query->latest()->paginate(20);

        $stats = [
            'total'           => Laundry::where('branch_id', $branchId)->count(),
            'received'        => Laundry::where('branch_id', $branchId)->where('status', 'received')->count(),
            'processing'      => Laundry::where('branch_id', $branchId)->where('status', 'processing')->count(),
            'ready'           => Laundry::where('branch_id', $branchId)->where('status', 'ready')->count(),
            'completed'       => Laundry::where('branch_id', $branchId)->where('status', 'completed')->count(),
            'total_revenue'   => Laundry::where('branch_id', $branchId)->where('status', 'completed')->sum('total_amount'),
            'today_laundries' => Laundry::where('branch_id', $branchId)->whereDate('created_at', today())->count(),
        ];

        $services = Service::where('is_active', true)
            ->orderBy('display_laundry')
            ->orderBy('name')
            ->get();

        return view('branch.laundries.index', compact('laundries', 'stats', 'services'));
    }

    /**
     * Show create laundry form (with optional pickup request)
     */
    public function create(Request $request)
    {
        $branch = Auth::guard('branch')->user();

        if (!$branch) {
            return redirect()->route('branch.login')
                ->with('error', 'Please login to access this page.');
        }

        $pickup = null;
        if ($request->has('pickup_id')) {
            $pickup = PickupRequest::with(['customer', 'branch', 'service'])
                ->where('branch_id', $branch->id)
                ->findOrFail($request->pickup_id);
        }

        $customers = Customer::where('is_active', true)
            ->where('preferred_branch_id', $branch->id)
            ->orderBy('name')
            ->get();

        $services = Service::where('is_active', true)
            ->orderBy('display_laundry')
            ->orderBy('name')
            ->get();

        $promotions = Promotion::active()
            ->valid()
            ->where(function ($query) use ($branch, $pickup) {
                $query->whereNull('branch_id')
                      ->orWhere('branch_id', $pickup?->branch_id ?? $branch->id);
            })
            ->orderBy('display_laundry')
            ->get();

        // Get active inventory items for add-ons (detergent, fabric conditioner, bleach, etc.)
        $addons = \App\Models\InventoryItem::where('is_active', true)
            ->whereHas('category', function($q) {
                $q->whereIn('name', ['Detergent', 'Fabric Conditioner', 'Bleach', 'Softener']);
            })
            ->with('category')
            ->orderBy('name')
            ->get();
        
        $currentBranch = $branch;

        // Get all active branches for the branch dropdown
        $branches = Branch::where('is_active', true)->get();

        // Get staff members for the current branch
        $staffMembers = User::where('branch_id', $branch->id)
            ->where('role', 'staff')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('branch.laundries.create', compact(
            'customers', 'services', 'currentBranch', 'pickup',
            'promotions', 'addons', 'branches',
        ) + ['staff' => $staffMembers,
             'routePrefix' => 'branch',
             'layout'      => 'branch.layouts.app',
             'extraServices' => \App\Models\ExtraServiceSetting::active()->ordered()->get(),
        ]);
    }

    /**
     * Store new laundry
     */
   public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id'       => 'required|exists:customers,id',
            'service_id'        => 'nullable|exists:services,id',
            'weight'            => 'nullable|numeric|min:0|max:1000',
            'number_of_loads'   => 'required|integer|min:1',
            'pickup_date'       => 'nullable|date|after_or_equal:today',
            'delivery_date'     => 'nullable|date|after_or_equal:today',
            'notes'             => 'nullable|string|max:1000',
            'pickup_request_id' => 'nullable|exists:pickup_requests,id',
            'pickup_fee'        => 'nullable|numeric|min:0',
            'delivery_fee'      => 'nullable|numeric|min:0',
            'extra_services'    => 'nullable|string',
            'addons'            => 'nullable|array',
            'addons.*.id'       => 'required_with:addons|integer|exists:inventory_items,id',
            'addons.*.quantity' => 'nullable|numeric|min:0.01|max:999',
            'promotion_id'      => 'nullable|integer|exists:promotions,id',
        ]);

        $branch = Auth::guard('branch')->user();

        if (!$branch) {
            return back()->with('error', 'Please login to access this feature.');
        }

        if (empty($validated['service_id']) && empty($validated['promotion_id'])) {
            return back()->withErrors(['service_id' => 'Either a service or a promotion is required'])->withInput();
        }

        if (empty($validated['service_id']) && !empty($validated['promotion_id'])) {
            $promo = Promotion::find($validated['promotion_id']);
            if (!$promo || $promo->application_type !== 'per_load_override') {
                return back()->withErrors(['promotion_id' => 'Selected promotion cannot be applied without selecting a service.'])->withInput();
            }
        }

        $loads = (int) ($validated['number_of_loads'] ?? 1);
        $weight = (float) ($validated['weight'] ?? 0);

        // Validate weight against service limits AND check stock availability
        if (!empty($validated['service_id'])) {
            $service = Service::findOrFail($validated['service_id']);
            $pricingType = $service->pricing_type ?? 'per_load';

            // Only validate for per_load services (not per_piece)
            if ($pricingType === 'per_load') {
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
                    $branchStock = \App\Models\BranchStock::where('branch_id', $branch->id)
                        ->where('inventory_item_id', $supply->id)
                        ->first();

                    $currentStock = $branchStock ? $branchStock->current_stock : 0;

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
                $branchStock = \App\Models\BranchStock::where('branch_id', $branch->id)
                    ->where('inventory_item_id', $itemId)
                    ->first();

                $currentStock = $branchStock ? $branchStock->current_stock : 0;

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

        DB::beginTransaction();

        try {
            $pickupRequest = null;
            if (!empty($validated['pickup_request_id'])) {
                $pickupRequest = PickupRequest::find($validated['pickup_request_id']);
            }

            $service               = null;
            $pricingType           = 'per_load';
            $unitPrice             = 0;
            $loads                 = (int) ($validated['number_of_loads'] ?? 1);
            $weight                = (float) ($validated['weight'] ?? 0);
            $snapshotPricePerPiece = 0;
            $snapshotPricePerLoad  = 0;
            $serviceSubtotal       = 0;

            if (!empty($validated['service_id'])) {
                $service     = Service::findOrFail($validated['service_id']);
                $pricingType = $service->pricing_type ?? 'per_load';

                // Weight validation already done before transaction, just use the values
                $unitPrice = $pricingType === 'per_piece'
                    ? (float) ($service->price_per_piece ?? 0)
                    : (float) ($service->price_per_load  ?? 0);

                $snapshotPricePerPiece = $pricingType === 'per_piece' ? $unitPrice : 0;
                $snapshotPricePerLoad  = $pricingType === 'per_piece' ? 0 : $unitPrice;
                $serviceSubtotal       = $unitPrice * $loads;
            }


            // Excess weight fee
            $excessWeight    = 0;
            $excessWeightFee = 0;
            if ($service && ($service->pricing_type ?? 'per_load') === 'per_load'
                && $service->allow_excess_weight
                && $service->excess_weight_charge_per_kg > 0
                && $service->max_weight > 0
                && $weight > ($service->max_weight * $loads)) {
                $excessWeight    = $weight - ($service->max_weight * $loads);
                $excessWeightFee = round($excessWeight * (float) $service->excess_weight_charge_per_kg, 2);
            }
            // ── Promotion ──────────────────────────────────────────────────
            $promotion              = null;
            $promotionDiscount      = 0;
            $promotionOverrideTotal = null;
            $promotionPricePerLoad  = null;
            $finalServiceSubtotal   = $serviceSubtotal;

            if (!empty($validated['promotion_id'])) {
                $promotion = Promotion::find($validated['promotion_id']);

                if ($promotion && $promotion->isValid) {
                    $laundryData = [
                        'subtotal'   => $serviceSubtotal,
                        'service_id' => $service?->id,
                        'branch_id'  => $branch->id,
                        'weight'     => $weight,
                        'loads'      => $loads,
                    ];

                    if ($promotion->isApplicableTo($laundryData)) {
                        if ($promotion->application_type === 'per_load_override') {
                            $promotionPricePerLoad  = $promotion->display_price;
                            $promotionOverrideTotal = $promotion->display_price * $loads;
                            $promotionDiscount      = 0;
                            $finalServiceSubtotal   = $promotionOverrideTotal;
                            if (empty($validated['service_id'])) {
                                $serviceSubtotal = 0;
                            }
                        } elseif ($promotion->discount_type === 'percentage' && !empty($validated['service_id'])) {
                            $effect               = $promotion->calculateEffect($serviceSubtotal, $loads);
                            $promotionDiscount    = $effect['discount_amount'];
                            $finalServiceSubtotal = $effect['final_subtotal'];
                        }
                    }
                }
            }

            // ── Add-ons: build pivot data with quantity and calculate total ──
            $addonsTotal = 0;
            $addonPivot  = [];  // [ inventoryItemId => ['price_at_purchase' => x, 'quantity' => y] ]

            if (!empty($validated['addons'])) {
                foreach ($validated['addons'] as $addonEntry) {
                    $itemId   = (int) $addonEntry['id'];
                    $quantity = (float) ($addonEntry['quantity'] ?? 1);
                    if ($quantity < 0.01) $quantity = 1;

                    $item = \App\Models\InventoryItem::find($itemId);
                    if (!$item) continue;

                    // Use unit cost price for add-ons (can be changed to a markup price later)
                    $price = (float) ($item->unit_cost_price ?? 0);
                    $lineTotal    = $price * $quantity;
                    $addonsTotal += $lineTotal;

                    $addonPivot[$itemId] = [
                        'price_at_purchase' => $price,
                        'quantity'          => $quantity,
                    ];
                }
            }

            // ── Fees ───────────────────────────────────────────────────────
            $pickupFee = $request->filled('pickup_fee')
                ? (float) $validated['pickup_fee']
                : (float) ($pickupRequest->pickup_fee ?? 0);

            $deliveryFee = $request->filled('delivery_fee')
                ? (float) $validated['delivery_fee']
                : (float) ($pickupRequest->delivery_fee ?? 0);

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

            $totalAmount    = $finalServiceSubtotal + $addonsTotal + $pickupFee + $deliveryFee + $extraServicesTotal + $excessWeightFee;
            $trackingNumber = $this->generateTrackingNumber();

            $laundry = Laundry::create([
                'tracking_number'          => $trackingNumber,
                'customer_id'              => $validated['customer_id'],
                'branch_id'                => $branch->id,
                'service_id'               => $service?->id,
                'staff_id'                 => null,
                'created_by'               => $branch->id,
                'weight'                   => $weight,
                'excess_weight'            => $excessWeight,
                'excess_weight_fee'        => $excessWeightFee,
                'number_of_loads'          => $loads,
                'price_per_piece'          => $snapshotPricePerPiece,
                'price_per_load'           => $snapshotPricePerLoad,
                'subtotal'                 => $serviceSubtotal,
                'addons_total'             => $addonsTotal + $extraServicesTotal,
                'discount_amount'          => $promotionDiscount,
                'promotion_id'             => $promotion?->id,
                'promotion_override_total' => $promotionOverrideTotal,
                'promotion_price_per_load' => $promotionPricePerLoad,
                'pickup_fee'               => $pickupFee,
                'delivery_fee'             => $deliveryFee,
                'total_amount'             => $totalAmount,
                'status'                   => 'received',
                'payment_status'           => 'pending',
                'received_at'              => now(),
                'notes'                    => ($validated['notes'] ?? '') . $extraServicesNote,
                'pickup_request_id'        => $validated['pickup_request_id'] ?? null,
            ]);

            // Attach inventory items as add-ons
            if (!empty($addonPivot)) {
                $laundry->inventoryItems()->attach($addonPivot);
                
                // Deduct add-on inventory from branch stock
                $this->deductAddonInventory($addonPivot, $branch->id, $laundry->id);
            }

            $laundry->statusHistories()->create([
                'status'     => 'received',
                'changed_by' => $branch->id,
                'notes'      => 'Laundry created'
                    . ($service ? '' : ' with fixed price promotion')
                    . ($pickupRequest ? ' from pickup #' . $pickupRequest->id : '')
                    . ($addonsTotal > 0 ? ' with ' . count($addonPivot) . ' add-on(s)' : ''),
            ]);

            if ($promotion && $promotionDiscount > 0) {
                PromotionUsage::create([
                    'promotion_id'    => $promotion->id,
                    'laundries_id'    => $laundry->id,
                    'user_id'         => $branch->id,
                    'discount_amount' => $promotionDiscount,
                    'original_amount' => $serviceSubtotal,
                    'final_amount'    => $finalServiceSubtotal,
                    'code_used'       => $promotion->promo_code,
                    'applied_at'      => now(),
                ]);

                $promotion->incrementUsage();
            }

            if ($pickupRequest) {
                $pickupRequest->update([
                    'laundries_id' => $laundry->id,
                    'pickup_fee'   => $pickupFee,
                    'delivery_fee' => $deliveryFee,
                    'status'       => 'picked_up',
                    'picked_up_at' => now(),
                ]);
            }

            DB::commit();

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

            return redirect()
                ->route('branch.laundries.show', $laundry)
                ->with('success', 'Laundry created successfully! Tracking #: ' . $trackingNumber);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating laundry: ' . $e->getMessage())->withInput();
        }
    }
    
    public function show(Laundry $laundry)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.login')->with('error', 'Please login to access this page.');
        }
        if ($laundry->branch_id != $branch->id) {
            abort(403, 'Unauthorized: This laundry belongs to a different branch.');
        }
        $laundry->load(['customer', 'service', 'branch', 'staff', 'pickupRequest', 'inventoryItems', 'promotion']);
        return view('branch.laundries.show', compact('laundry'));
    }

    public function edit(Laundry $laundry)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.login')->with('error', 'Please login to access this page.');
        }
        if ($laundry->branch_id != $branch->id) {
            abort(403, 'Unauthorized: This laundry belongs to a different branch.');
        }
        if (in_array($laundry->status, ['completed', 'cancelled'])) {
            return redirect()->route('branch.laundries.show', $laundry)
                ->with('error', 'Cannot edit completed or cancelled laundries.');
        }
        $customers = Customer::where('is_active', true)
            ->where('preferred_branch_id', $branch->id)
            ->orderBy('name')->get();
        $services = Service::where('is_active', true)->orderBy('name')->get();
        return view('branch.laundries.edit', compact('laundry', 'customers', 'services'));
    }

    public function update(Request $request, Laundry $laundry)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return back()->with('error', 'Please login to access this feature.');
        }
        if ($laundry->branch_id != $branch->id) {
            abort(403, 'Unauthorized: This laundry belongs to a different branch.');
        }
        if (in_array($laundry->status, ['completed', 'cancelled'])) {
            return redirect()->route('branch.laundries.show', $laundry)
                ->with('error', 'Cannot edit completed or cancelled laundries.');
        }

        $validated = $request->validate([
            'customer_id'     => 'required|exists:customers,id',
            'service_id'      => 'required_without:number_of_loads|exists:services,id',
            'number_of_loads' => 'nullable|integer|min:1',
            'weight'          => 'nullable|numeric|min:0|max:1000',
            'pickup_date'     => 'nullable|date',
            'delivery_date'   => 'nullable|date',
            'notes'           => 'nullable|string|max:1000',
        ]);

        $weight = (isset($validated['weight']) && $validated['weight'] !== null) ? (float) $validated['weight'] : 0;
        $service = Service::findOrFail($validated['service_id']);
        $pricingType = $service->pricing_type ?? 'per_load';

        // Validate weight against service limits (only for per_load services)
        if ($pricingType === 'per_load') {
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

        $pricePerPiece  = $laundry->price_per_piece;
        $subtotal       = $laundry->subtotal;
        $discountAmount = $laundry->discount_amount ?? 0;

        if ($validated['number_of_loads'] ?? null) {
            $promo = $laundry->promotion;
            if ($promo && $promo->application_type === 'per_load_override') {
                $subtotal       = $promo->display_price * $validated['number_of_loads'];
                $discountAmount = 0;
            }
        } elseif ($laundry->service_id != ($validated['service_id'] ?? $laundry->service_id)) {
            $unitPrice     = $pricingType === 'per_piece'
                ? (float) ($service->price_per_piece ?? 0)
                : (float) ($service->price_per_load  ?? 0);
            $pricePerPiece = $pricingType === 'per_piece' ? $unitPrice : null;
            $loads         = $validated['number_of_loads'] ?? $laundry->number_of_loads ?? 1;
            $subtotal      = $unitPrice * $loads;
        }

        $pickupFee   = $laundry->pickup_fee   ?? 0;
        $deliveryFee = $laundry->delivery_fee ?? 0;
        $addonsTotal = $laundry->addons_total ?? 0;
        $totalAmount = $subtotal - $discountAmount + $pickupFee + $deliveryFee + $addonsTotal;

        $laundry->update([
            'customer_id'     => $validated['customer_id'],
            'service_id'      => $validated['service_id'],
            'weight'          => $weight,
            'number_of_loads' => $validated['number_of_loads'] ?? null,
            'price_per_piece' => $pricePerPiece,
            'subtotal'        => $subtotal,
            'total_amount'    => $totalAmount,
            'pickup_date'     => $validated['pickup_date']   ?? null,
            'delivery_date'   => $validated['delivery_date'] ?? null,
            'notes'           => $validated['notes']         ?? null,
        ]);

        return redirect()->route('branch.laundries.show', $laundry)
            ->with('success', 'Laundry updated successfully!');
    }

    public function updateStatus(Request $request, Laundry $laundry)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return back()->with('error', 'Please login to access this feature.');
        }
        if ($laundry->branch_id != $branch->id) {
            abort(403, 'Unauthorized: This laundry belongs to a different branch.');
        }

        $validated = $request->validate([
            'status' => 'required|in:received,processing,ready,completed',
            'notes'  => 'nullable|string|max:500',
        ]);

        $oldStatus = $laundry->status;
        $newStatus = $validated['status'];
        $laundry->update(['status' => $newStatus]);

        if ($newStatus === 'processing' && !$laundry->processing_at) $laundry->update(['processing_at' => now()]);
        elseif ($newStatus === 'ready'   && !$laundry->ready_at)      $laundry->update(['ready_at'      => now()]);
        elseif ($newStatus === 'completed' && !$laundry->completed_at) $laundry->update(['completed_at' => now()]);

        $laundry->statusHistories()->create([
            'status'     => $newStatus,
            'changed_by' => $branch->id,
            'notes'      => $validated['notes'] ?? "Status changed from {$oldStatus} to {$newStatus}",
        ]);

        // Send push notification to customer
        if ($laundry->customer_id) {
            app(\App\Services\LaundryNotificationService::class)->onStatusChange($laundry, $newStatus);
        }

        return back()->with('success', 'Laundry status updated to: ' . ucfirst($newStatus));
    }

    public function receipt(Laundry $laundry)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.login')->with('error', 'Please login to access this page.');
        }
        if ($laundry->branch_id != $branch->id) {
            abort(403, 'Unauthorized: This laundry belongs to a different branch.');
        }
        $laundry->load(['customer', 'service', 'branch', 'staff', 'pickupRequest', 'promotion']);
        return view('branch.laundries.receipt', compact('laundry'));
    }

    public function recordPayment(Request $request, Laundry $laundry)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.laundries.show', $laundry)
                ->with('error', 'Please login to access this feature.');
        }
        if ($laundry->branch_id != $branch->id) {
            abort(403, 'Unauthorized: This laundry belongs to a different branch.');
        }

        // Update payment status
        $laundry->update([
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        // Create status history
        $laundry->statusHistories()->create([
            'status' => $laundry->status,
            'changed_by' => $branch->id,
            'notes' => 'Payment recorded',
        ]);

        return redirect()->route('branch.laundries.show', $laundry)
            ->with('success', 'Payment recorded successfully!');
    }

    private function generateTrackingNumber(): string
    {
        do {
            $tracking = 'WB-' . date('Ymd') . '-' . strtoupper(Str::random(4));
        } while (Laundry::where('tracking_number', $tracking)->exists());
        return $tracking;
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
                'performed_by' => auth()->user()->id ?? null,
                'notes' => "Add-on used: {$item->name} ({$quantity} {$item->distribution_unit})",
            ]);
        }
    }
}
