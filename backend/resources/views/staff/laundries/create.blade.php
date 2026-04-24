@extends('staff.layouts.staff')

@section('page-title', isset($pickup) ? 'Create Laundry from Pickup #' . $pickup->id : 'Create New Laundry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/laundry.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dark-mode-fixes.css') }}">
@endpush

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 page-header">
        <div>
            <h2 class="fw-bold">
                @if(isset($pickup))
                    Create Laundry from Pickup #{{ $pickup->id }}
                @else
                    Create New Laundry
                @endif
            </h2>
            <p class="text-muted">
                @if(isset($pickup))
                    Laundry has been picked up - Create the laundry now
                @else
                    Add a new laundry service
                @endif
            </p>
        </div>
        <a href="{{ isset($pickup) ? route('staff.pickups.show', $pickup) : route('staff.laundries.index') }}"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Pickup Info Alert --}}
    @if(isset($pickup))
        <div class="pickup-alert">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h6 class="mb-1">
                        <i class="bi bi-truck me-2"></i> Pickup Request #{{ $pickup->id }}
                    </h6>
                    <p class="mb-0">
                        <strong>Customer:</strong> {{ $pickup->customer->name }} |
                        <strong>Address:</strong> {{ $pickup->pickup_address }} |
                        <strong>Service Type:</strong>
                        <span class="badge bg-primary">
                            {{ $pickup->service_type == 'both' ? 'Pickup + Delivery' : ucwords(str_replace('_', ' ', $pickup->service_type)) }}
                        </span>
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <strong>Quoted Fees:</strong>
                    <span class="text-success fs-5">
                        ₱{{ number_format(($pickup->pickup_fee ?? 0) + ($pickup->delivery_fee ?? 0), 2) }}
                    </span>
                    <br>
                    <small class="text-muted">
                        (Pickup: ₱{{ number_format($pickup->pickup_fee ?? 0, 2) }} + Delivery: ₱{{ number_format($pickup->delivery_fee ?? 0, 2) }})
                    </small>
                </div>
            </div>
        </div>
    @endif

    <form action="{{ route('staff.laundries.store') }}" method="POST" id="laundryForm">
        @csrf

        @if(isset($pickup))
            <input type="hidden" name="pickup_request_id" value="{{ $pickup->id }}">
        @endif

        <div class="row g-4">
            {{-- Left Column --}}
            <div class="col-lg-8">
                {{-- Customer Information Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-2">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
                    </div>
                    <div class="card-body">
                        @if(isset($pickup))
                            <input type="hidden" name="customer_id" value="{{ $pickup->customer_id }}">
                            <div class="alert alert-light border">
                                <div class="row">
                                    <div class="col-md-6"><strong>Name:</strong> {{ $pickup->customer->name }}</div>
                                    <div class="col-md-6"><strong>Phone:</strong> {{ $pickup->customer->phone ?? 'N/A' }}</div>
                                    <div class="col-md-12 mt-2"><strong>Pickup Address:</strong> {{ $pickup->pickup_address }}</div>
                                </div>
                            </div>
                        @else
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Select Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required id="customerSelect">
                                    <option value="">Choose customer...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}
                                            data-phone="{{ $customer->phone ?? 'N/A' }}"
                                            data-address="{{ $customer->address ?? 'N/A' }}">
                                            {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="customerInfo" class="customer-info-card d-none">
                                    <div class="row g-2">
                                        <div class="col-md-6"><small class="d-block">Phone</small><strong id="customerPhone">-</strong></div>
                                        <div class="col-md-6"><small class="d-block">Address</small><strong id="customerAddress">-</strong></div>
                                    </div>
                                </div>
                            </div>
                            <div class="accordion mt-3" id="newCustomerAccordion">
                                <div class="accordion-item border-0">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#newCustomerForm">
                                            <i class="bi bi-plus-circle me-2 text-primary"></i><strong>Add New Customer</strong>
                                        </button>
                                    </h2>
                                    <div id="newCustomerForm" class="accordion-collapse collapse" data-bs-parent="#newCustomerAccordion">
                                        <div class="accordion-body">
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small">Full Name *</label>
                                                    <input type="text" id="newCustomerName" class="form-control form-control-sm" placeholder="Enter name">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small">Phone Number</label>
                                                    <input type="text" id="newCustomerPhone" class="form-control form-control-sm" placeholder="0912-345-6789">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label small">Address</label>
                                                    <textarea id="newCustomerAddress" class="form-control form-control-sm" rows="2" placeholder="Enter address"></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <button type="button" class="btn btn-sm btn-primary" onclick="window.laundryManager.addNewCustomer()">
                                                        <i class="bi bi-plus me-1"></i>Add Customer
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Service Details Card --}}
                <div class="card border-0 shadow-sm rounded-4 mb-2">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-droplet me-2"></i>Service Details</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Branch (Staff branch is auto-assigned) --}}
                            @if(!isset($pickup))
                                <input type="hidden" name="branch_id" value="{{ auth()->user()->branch_id }}">
                            @else
                                <input type="hidden" name="branch_id" value="{{ $pickup->branch_id }}">
                            @endif

                            {{-- Service --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                                <select name="service_id" class="form-select @error('service_id') is-invalid @enderror" id="serviceSelect">
                                    <option value="">Select service...</option>

                                    {{-- Drop Off --}}
                                    @php $dropOff = $services->whereIn('category', ['drop_off'])->whereNotIn('service_type', ['addon']); @endphp
                                    @if($dropOff->count())
                                        <optgroup label="🛍 Drop Off">
                                        @foreach($dropOff as $service)
                                            @php
                                                $pricingType  = $service->pricing_type ?? 'per_load';
                                                $displayPrice = $pricingType === 'per_piece'
                                                    ? ($service->price_per_piece ?? 0)
                                                    : ($service->price_per_load  ?? 0);
                                                $priceUnit    = $pricingType === 'per_piece' ? 'piece' : 'load';
                                            @endphp
                                            <option value="{{ $service->id }}"
                                                    {{ (isset($pickup) && $pickup->service_id == $service->id) || old('service_id') == $service->id ? 'selected' : '' }}
                                                    data-price-per-load="{{ $service->price_per_load ?? 0 }}"
                                                    data-price-per-piece="{{ $service->price_per_piece ?? 0 }}"
                                                    data-pricing-type="{{ $pricingType }}"
                                                    data-service-type="{{ $service->service_type }}"
                                                    data-category="{{ $service->category }}"
                                                    data-min-weight="{{ $service->min_weight }}"
                                                    data-max-weight="{{ $service->max_weight }}"
                                                    data-turnaround-time="{{ $service->turnaround_time }}">
                                                {{ $service->name }}
                                                (₱{{ number_format($displayPrice, 2) }}/{{ $priceUnit }}{{ $pricingType !== 'per_piece' && $service->max_weight ? ', up to '.$service->max_weight.'kg' : '' }})
                                            </option>
                                        @endforeach
                                        </optgroup>
                                    @endif

                                    {{-- Self Service --}}
                                    @php $selfService = $services->where('category', 'self_service'); @endphp
                                    @if($selfService->count())
                                        <optgroup label="🧺 Self Service">
                                        @foreach($selfService as $service)
                                            @php
                                                $ssPt    = $service->pricing_type ?? 'per_load';
                                                $ssPrice = $ssPt === 'per_piece' ? ($service->price_per_piece ?? 0) : ($service->price_per_load ?? 0);
                                                $ssUnit  = $ssPt === 'per_piece' ? 'piece' : 'load';
                                            @endphp
                                            <option value="{{ $service->id }}"
                                                    {{ (isset($pickup) && $pickup->service_id == $service->id) || old('service_id') == $service->id ? 'selected' : '' }}
                                                    data-price-per-load="{{ $service->price_per_load ?? 0 }}"
                                                    data-price-per-piece="{{ $service->price_per_piece ?? 0 }}"
                                                    data-pricing-type="{{ $ssPt }}"
                                                    data-service-type="{{ $service->service_type }}"
                                                    data-category="{{ $service->category }}"
                                                    data-min-weight=""
                                                    data-max-weight=""
                                                    data-turnaround-time="{{ $service->turnaround_time }}">
                                                {{ $service->name }}
                                                (₱{{ number_format($ssPrice, 2) }}/{{ $ssUnit }})
                                            </option>
                                        @endforeach
                                        </optgroup>
                                    @endif
                                </select>
                                @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <div id="serviceDescription" class="service-description"></div>
                            </div>

                            {{-- Weight Input --}}
                            <div class="col-md-6" id="weightContainer">
                                <label class="form-label fw-semibold">Weight (kg) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="weight" step="0.1" min="0"
                                        class="form-control @error('weight') is-invalid @enderror"
                                        value="{{ old('weight') }}"
                                        placeholder="0.0"
                                        id="weightInput">
                                    <span class="input-group-text">kg</span>
                                </div>
                                @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted" id="weightHelp">Enter laundry weight</small>
                            </div>

                            {{-- Loads Input --}}
                            <div class="col-md-6 d-none" id="loadsContainer">
                                <label class="form-label fw-semibold">Number of Loads <span class="text-danger">*</span></label>
                                <input type="number" name="number_of_loads" min="1"
                                    class="form-control @error('number_of_loads') is-invalid @enderror"
                                    value="{{ old('number_of_loads', 1) }}"
                                    placeholder="1"
                                    id="loadsInput">
                                @error('number_of_loads')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted" id="loadsHelp">Number of loads/pieces</small>
                            </div>

                            {{-- Extra Weight Warning --}}
                            <div class="col-12" id="extraWeightWarning" style="display: none;">
                                <div class="extra-weight-warning">
                                    <i class="bi bi-exclamation-triangle"></i>
                                    <strong>Extra Load Required:</strong>
                                    <span id="extraWeightMessage"></span>
                                    <span id="autoExtraLoad" class="auto-adjust"></span>
                                </div>
                            </div>

                            {{-- Promotion --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apply Promotion (Optional)</label>
                                <select name="promotion_id" class="form-select" id="promotionSelect">
                                    <option value="">Select promotion...</option>
                                    @foreach($promotions as $promotion)
                                        @php
                                            $promoText = $promotion->name;
                                            if ($promotion->application_type === 'per_load_override') {
                                                $promoText .= ' - ₱' . number_format($promotion->display_price, 2) . '/load';
                                            } elseif ($promotion->discount_type === 'percentage') {
                                                $promoText .= ' - ' . $promotion->discount_value . '% OFF';
                                            } elseif ($promotion->discount_type === 'fixed') {
                                                $promoText .= ' - ₱' . number_format($promotion->discount_value, 2) . ' OFF';
                                            }
                                        @endphp
                                        <option value="{{ $promotion->id }}"
                                            data-application-type="{{ $promotion->application_type }}"
                                            data-display-price="{{ $promotion->display_price }}"
                                            data-discount-type="{{ $promotion->discount_type }}"
                                            data-discount-value="{{ $promotion->discount_value }}">
                                            {{ $promoText }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="promotionDescription"></small>
                            </div>

                            {{-- Add-ons with Quantity Controls --}}
                            <div class="col-12 mt-3">
                                <label class="form-label fw-semibold">Add-ons (Optional)</label>
                                <p class="text-muted small mb-2">Select additional services and specify quantities</p>
                                <div class="row g-2" id="addonsContainer">
                                    @foreach($addons as $addon)
                                        <div class="col-md-6 col-lg-4">
                                            <div class="addon-item" data-addon-id="{{ $addon->id }}">
                                                <div class="addon-header">
                                                    <span class="addon-name">{{ $addon->name }}</span>
                                                    <span class="text-success fw-bold">₱{{ number_format($addon->price, 2) }}</span>
                                                </div>
                                                <div class="addon-description">
                                                    {{ $addon->description }}
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input addon-checkbox" type="checkbox"
                                                            name="addons[{{ $addon->id }}][id]" value="{{ $addon->id }}"
                                                            id="addon{{ $addon->id }}"
                                                            data-price="{{ $addon->price }}"
                                                            data-name="{{ $addon->name }}">
                                                        <label class="form-check-label" for="addon{{ $addon->id }}">
                                                            <small>Select</small>
                                                        </label>
                                                    </div>
                                                    <div class="quantity-control">
                                                        <button type="button" class="quantity-btn minus-btn" disabled>
                                                            <i class="bi bi-dash"></i>
                                                        </button>
                                                        <input type="number" name="addons[{{ $addon->id }}][quantity]"
                                                            class="addon-quantity" value="1" min="1" max="99"
                                                            data-addon-id="{{ $addon->id }}"
                                                            id="quantity{{ $addon->id }}"
                                                            disabled>
                                                        <button type="button" class="quantity-btn plus-btn" disabled>
                                                            <i class="bi bi-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="addon-price-total text-end mt-2" id="total{{ $addon->id }}">
                                                    Total: ₱0.00
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column - Pickup & Delivery Fees, Additional Notes, Actions --}}
            <div class="col-lg-4">
                <div class="row g-3">
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100 @if(isset($pickup)) border-warning border-2 @endif">
                            <div class="card-header @if(isset($pickup)) bg-warning bg-opacity-10 @endif">
                                <h6 class="mb-0">
                                    <i class="bi bi-truck me-2"></i>
                                    Pickup & Delivery Fees
                                    @if(isset($pickup))
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="bi bi-exclamation-triangle"></i> ENTER FEES!
                                        </span>
                                    @endif
                                </h6>
                            </div>
                            <div class="card-body">
                                @if(isset($pickup))
                                    <div class="btn-group-sm d-flex flex-wrap gap-2 mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary" onclick="window.laundryManager.setFees(50, 0)"><i class="bi bi-arrow-down-circle"></i> ₱50</button>
                                        <button type="button" class="btn btn-sm btn-outline-success" onclick="window.laundryManager.setFees(0, 50)"><i class="bi bi-arrow-up-circle"></i> ₱50</button>
                                        <button type="button" class="btn btn-sm btn-outline-info" onclick="window.laundryManager.setFees(50, 50)"><i class="bi bi-arrow-left-right"></i> Both</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.laundryManager.setFees(0, 0)"><i class="bi bi-x-circle"></i> None</button>
                                    </div>
                                @endif

                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">
                                            <i class="bi bi-arrow-down-circle text-primary"></i> Pickup Fee
                                            @if(isset($pickup)) <span class="text-danger">*</span> @endif
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" name="pickup_fee" step="0.01" min="0"
                                                   class="form-control @error('pickup_fee') is-invalid @enderror"
                                                   value="{{ isset($pickup) && $pickup->pickup_fee ? $pickup->pickup_fee : old('pickup_fee', isset($pickup) ? 50.00 : 0) }}"
                                                   placeholder="50.00" id="pickupFeeInput">
                                        </div>
                                        @error('pickup_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">
                                            <i class="bi bi-arrow-up-circle text-success"></i> Delivery Fee
                                            @if(isset($pickup)) <span class="text-danger">*</span> @endif
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">₱</span>
                                            <input type="number" name="delivery_fee" step="0.01" min="0"
                                                   class="form-control @error('delivery_fee') is-invalid @enderror"
                                                   value="{{ isset($pickup) && $pickup->delivery_fee ? $pickup->delivery_fee : old('delivery_fee', isset($pickup) ? 50.00 : 0) }}"
                                                   placeholder="50.00" id="deliveryFeeInput">
                                        </div>
                                        @error('delivery_fee')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                @if(!isset($pickup))
                                    <div class="alert alert-info mt-3 mb-0 small">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Walk-in:</strong> Only add if customer requests service.
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 h-100">
                            <div class="card-header">
                                <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Additional Notes</h6>
                            </div>
                            <div class="card-body">
                                <textarea name="notes" class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                    rows="4" placeholder="Special instructions, stain notes, etc...">{{ $pickup->notes ?? old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="accordion accordion-flush" id="quickTipsAccordion">
                            <div class="accordion-item quick-tips-accordion">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#quickTipsContent">
                                        <i class="bi bi-lightbulb text-warning me-2"></i>
                                        <span class="fw-semibold">Quick Tips</span>
                                    </button>
                                </h2>
                                <div id="quickTipsContent" class="accordion-collapse collapse" data-bs-parent="#quickTipsAccordion">
                                    <div class="accordion-body quick-tips-content">
                                        <ul class="quick-tips-list mb-0">
                                            <li>Per-load services: Price is fixed per load (e.g., ₱200/8kg)</li>
                                            <li>Extra weight beyond max limit requires extra load(s)</li>
                                            <li>Add-ons like detergent, fabcon are additional charges</li>
                                            <li>Special items (comforters) are priced per piece</li>
                                            <li>Per-kg services: Price varies with weight</li>
                                            <li>Self-service: Customer operates machines</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

        </div>

        {{-- Below Form - Laundry Summary & Quick Tips (Full Width) --}}
        <div class="row g-4 mt-2">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-calculator me-2"></i>Laundry Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            {{-- Service Charges Section --}}
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2 small" id="serviceChargesTitle">Service Charges</h6>
                                <div id="serviceBreakdown">
                                    <div id="serviceBaseInfo">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted small">Base Service:</span>
                                            <strong class="small" id="servicePriceDisplay">₱0.00</strong>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted small">Quantity:</span>
                                            <strong class="small" id="quantityDisplay">0</strong>
                                        </div>
                                    </div>
                                    <div id="loadsBreakdown" style="display: none;">
                                        <div class="mb-1 small" id="loadsBreakdownList"></div>
                                    </div>
                                    <div class="d-flex justify-content-between pt-1 border-top">
                                        <strong class="text-muted small">Subtotal:</strong>
                                        <strong class="small" id="serviceSubtotalDisplay">₱0.00</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Pickup & Delivery Fees --}}
                            <div class="col-md-6">
                                <h6 class="text-muted mb-2 small border-top pt-2">Pickup & Delivery</h6>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted small"><i class="bi bi-arrow-down-circle text-primary"></i> Pickup:</span>
                                    <strong class="small" id="pickupFeeDisplay">₱0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between small">
                                    <span class="text-muted small"><i class="bi bi-arrow-up-circle text-success"></i> Delivery:</span>
                                    <strong class="small" id="deliveryFeeDisplay">₱0.00</strong>
                                </div>
                                <div class="d-flex justify-content-between pt-1 border-top mt-1">
                                    <strong class="small">Total Fees:</strong>
                                    <strong class="text-success small" id="totalFeesDisplay">₱0.00</strong>
                                </div>
                            </div>

                            {{-- Extra Loads & Add-ons Section (swapped with Pickup) --}}
                            <div class="col-md-6">
                                <div id="extraLoadsSection" class="mb-2" style="display: none;">
                                    <h6 class="text-muted mb-1 small">Extra Loads</h6>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted small">Extra load(s):</span>
                                        <span class="text-muted small" id="extraLoadsCount">0</span>
                                    </div>
                                    <div class="d-flex justify-content-between small">
                                        <span class="text-muted small">Extra charge:</span>
                                        <span class="text-danger small" id="extraLoadsCharge">₱0.00</span>
                                    </div>
                                </div>

                                <div id="addonsSection" style="display:none;">
                                    <h6 class="text-muted mb-1 small">Add-ons</h6>
                                    <div id="addonsList" class="addons-summary small"></div>
                                    <div class="d-flex justify-content-between pt-1 border-top">
                                        <strong class="small">Add-ons Total:</strong>
                                        <strong class="text-success small" id="addonsTotalDisplay">₱0.00</strong>
                                    </div>
                                </div>

                                <div id="promotionSection" style="display:none;">
                                    <h6 class="text-muted mb-1 small mt-2">Promotion</h6>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted small">Discount Applied:</span>
                                        <span class="text-success small" id="promotionDiscountDisplay">₱0.00</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Grand Total --}}
                            <div class="col-md-6">
                                <div class="p-2" style="background: rgba(61, 59, 107, 0.08); border-radius: 0.5rem;">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-bold small">Grand Total:</span>
                                        <strong class="fw-bold" style="color: #3D3B6B; font-size: 1.1rem;" id="totalDisplay">₱0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header">
                        <h6 class="mb-0"><i class="bi bi-gear me-2"></i>Actions</h6>
                    </div>
                    <div class="card-body d-flex flex-column gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-circle me-2"></i>Create Laundry
                        </button>
                        <a href="{{ isset($pickup) ? route('staff.pickups.show', $pickup) : route('staff.laundries.index') }}"
                           class="btn btn-outline-secondary w-100">
                            <i class="bi bi-x-circle me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <input type="hidden" name="_weight_fallback" value="0" id="weightFallback">
    </form>
</div>

@push('scripts')
    <script src="{{ asset('assets/js/laundry-create.js') }}"></script>
    {{-- NOTE: Do NOT call initAddonQuantityControls() here.
         LaundryCreateManager already calls it inside initialize() on DOMContentLoaded.
         A second call would register the click listener twice, causing every +/- button
         to fire twice — doubling the quantity jump and duplicating the add-on summary rows. --}}
@endpush
@endsection
