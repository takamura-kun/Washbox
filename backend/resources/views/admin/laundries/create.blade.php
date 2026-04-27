@extends('admin.layouts.app')

@section('page-title', isset($pickup) ? 'Create Laundry from Pickup #' . $pickup->id : 'Create New Laundry')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/laundry.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/dark-mode-fixes.css') }}">
@endpush

@section('content')
<div class="container-fluid px-3 py-2">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-2 page-header">
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
        <a href="{{ isset($pickup) ? route('admin.pickups.show', $pickup) : route('admin.laundries.index') }}"
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

    {{-- Weight Validation Warning Alert --}}
    @if($errors->has('weight_warning'))
        <div class="alert alert-warning alert-dismissible fade show border-2 border-warning mb-3" role="alert" style="z-index: 1050; position: relative;">
            <div class="d-flex align-items-start">
                <i class="bi bi-exclamation-triangle-fill me-3" style="font-size: 1.25rem; color: #ff6b6b; flex-shrink: 0;"></i>
                <div style="flex: 1;">
                    @php
                        $warningMsg = $errors->first('weight_warning');
                        $isMinimum = str_contains($warningMsg, 'minimum') || str_contains($warningMsg, 'Not in minimum');
                    @endphp
                    <h5 class="alert-heading mb-2">
                        @if($isMinimum)
                            ⚠️ Minimum Weight Required
                        @else
                            ⚠️ Maximum Weight Exceeded
                        @endif
                    </h5>
                    <p class="mb-2">{{ $warningMsg }}</p>
                    <hr class="my-2">
                    <p class="mb-0 small">
                        <strong>Solution:</strong>
                        @if($isMinimum)
                            Please add more laundry to meet the minimum weight requirement.
                        @else
                            Please adjust the weight to meet the service requirements or split the laundry into multiple loads.
                        @endif
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="flex-shrink: 0;"></button>
            </div>
        </div>
    @endif

    {{-- Error Alert --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-circle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    @if($error !== $errors->first('weight_warning'))
                        <li>{{ $error }}</li>
                    @endif
                @endforeach
            </ul>
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

    <form action="{{ route('admin.laundries.store') }}" method="POST" id="laundryForm" class="laundry-create-form">
        @csrf

        @if(isset($pickup))
            <input type="hidden" name="pickup_request_id" value="{{ $pickup->id }}">
        @endif

        <div class="row g-2">

            {{-- ── Left Column ── --}}
            <div class="col-lg-8">

                {{-- Customer Information Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-2">
                    <div class="card-header py-2 px-3">
                        <h6 class="mb-0"><i class="bi bi-person me-2"></i>Customer Information</h6>
                    </div>
                    <div class="card-body p-3">
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
                            <div class="mb-2">
                                <label class="form-label fw-semibold">Select Customer <span class="text-danger">*</span></label>
                                <select name="customer_id" class="form-select @error('customer_id') is-invalid @enderror" required id="customerSelect">
                                    <option value="">Choose customer...</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}"
                                            {{ old('customer_id') == $customer->id ? 'selected' : '' }}
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
                            <div class="accordion mt-3" id="newCustomerAccordion"></div>
                        @endif
                    </div>
                </div>

                {{-- Service Details Card --}}
                <div class="card border-0 shadow-sm rounded-3 mb-2">
                    <div class="card-header py-2 px-3">
                        <h6 class="mb-0"><i class="bi bi-droplet me-2"></i>Service Details</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row g-2">

                            {{-- Branch --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Branch <span class="text-danger">*</span></label>
                                @if(isset($pickup))
                                    <input type="hidden" name="branch_id" value="{{ $pickup->branch_id }}">
                                    <input type="text" class="form-control" value="{{ $pickup->branch->name }}" readonly>
                                @else
                                    <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror" required id="branchSelect">
                                        <option value="">Select branch...</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                @endif
                            </div>

                            {{-- Service --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                                @if(isset($pickup) && $pickup->service_id)
                                    <input type="hidden" name="service_id" value="{{ $pickup->service_id }}"
                                        data-pricing-type="{{ $pickup->service->pricing_type ?? 'per_load' }}"
                                        data-service-type="{{ $pickup->service->service_type ?? 'full_service' }}"
                                        data-price-per-load="{{ $pickup->service->price_per_load ?? 0 }}"
                                        data-price-per-piece="{{ $pickup->service->price_per_piece ?? 0 }}"
                                        data-max-weight="{{ $pickup->service->max_weight ?? 0 }}">
                                    <div class="alert alert-info border-info mb-0">
                                        <div class="d-flex align-items-start">
                                            <i class="bi bi-lock-fill me-2 mt-1"></i>
                                            <div>
                                                <strong>{{ $pickup->service->name }}</strong>
                                                @if($pickup->service->description)
                                                    <p class="mb-1 small text-muted">{{ $pickup->service->description }}</p>
                                                @endif
                                                <div class="mt-2">
                                                    @if($pickup->service->price_per_kilo)
                                                        <span class="badge bg-primary">₱{{ number_format($pickup->service->price_per_kilo, 2) }}/kg</span>
                                                    @endif
                                                    @if($pickup->service->price_per_load)
                                                        <span class="badge bg-success">₱{{ number_format($pickup->service->price_per_load, 2) }}/load</span>
                                                    @endif
                                                    @if($pickup->service->turnaround_time)
                                                        <span class="badge bg-secondary">{{ $pickup->service->turnaround_time }}</span>
                                                    @endif
                                                </div>
                                                <small class="text-muted d-block mt-2">
                                                    <i class="bi bi-info-circle"></i> Service is locked from pickup request
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <select name="service_id" class="form-select @error('service_id') is-invalid @enderror" id="serviceSelect">
                                        <option value="">Select service...</option>

                                        {{-- Drop Off --}}
                                        @php $dropOff = $services->whereIn('category', ['drop_off'])->whereNotIn('service_type', ['addon']); @endphp
                                        @if($dropOff->count())
                                            <optgroup label="DROP OFF SERVICES">
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
                                            <optgroup label="SELF SERVICE">
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
                                @endif
                            </div>

                            {{-- Weight Input --}}
                            <div class="col-md-6" id="weightContainer">
                                <label class="form-label fw-semibold">Weight (kg) <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="weight" step="0.1" min="0.1" required
                                        class="form-control @error('weight') is-invalid @enderror"
                                        value="{{ old('weight') }}"
                                        placeholder="e.g. 2.5"
                                        id="weightInput">
                                    <span class="input-group-text">kg</span>
                                </div>
                                @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted" id="weightHelp">Select a service to see weight requirements</small>
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

                            {{-- Promotion --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Apply Promotion (Optional)</label>
                                @if(isset($pickup) && $pickup->promotion)
                                    <div class="alert alert-info py-2 px-3 mb-2" style="border-radius:8px;font-size:0.85rem;">
                                        <i class="bi bi-tag-fill me-1"></i>
                                        <strong>Promo from pickup:</strong> {{ $pickup->promotion->name }}
                                        @if($pickup->promotion->display_price)
                                            &mdash; <span class="badge bg-primary">₱{{ $pickup->promotion->display_price }} {{ $pickup->promotion->price_unit ?? 'per load' }}</span>
                                        @endif
                                    </div>
                                @endif
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
                                            data-discount-value="{{ $promotion->discount_value }}"
                                            {{ (isset($pickup) && $pickup->promotion_id == $promotion->id) || old('promotion_id') == $promotion->id ? 'selected' : '' }}>
                                            {{ $promoText }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted" id="promotionDescription"></small>
                            </div>

                            {{-- Add-ons --}}
                            <div class="col-12 mt-3">
                                <label class="form-label fw-semibold">Add-ons (Optional)</label>
                                <p class="text-muted small mb-2">Select additional items (detergent, fabric conditioner, bleach, etc.)</p>
                                <div class="row g-2" id="addonsContainer">
                                    @foreach($addons as $addon)
                                        <div class="col-6 col-md-4 col-lg-3 d-flex">
                                            <div class="addon-item" data-addon-id="{{ $addon->id }}">
                                                <div class="addon-header">
                                                    <span class="addon-name">{{ $addon->name }}</span>
                                                    <span class="text-success fw-bold">₱{{ number_format($addon->unit_cost_price ?? 0, 2) }}</span>
                                                </div>
                                                <div class="addon-description">
                                                    @if($addon->brand)
                                                        <span class="badge bg-secondary">{{ $addon->brand }}</span>
                                                    @endif
                                                    <small class="text-muted">{{ $addon->category->name ?? '' }}</small>
                                                </div>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input addon-checkbox" type="checkbox"
                                                            name="addons[{{ $addon->id }}][id]" value="{{ $addon->id }}"
                                                            id="addon{{ $addon->id }}"
                                                            data-price="{{ $addon->unit_cost_price ?? 0 }}"
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
                                                            class="addon-quantity" value="1" min="0.01" step="0.01" max="999"
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

                        </div>{{-- end row g-2 inside service details --}}
                    </div>{{-- end card-body --}}
                </div>{{-- end service details card --}}

            </div>{{-- end col-lg-8 --}}

            {{-- ── Right Column ── --}}
            <div class="col-lg-4">
                <div class="row g-2">

                    {{-- Pickup & Delivery Fees --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4 @if(isset($pickup)) border-warning border-2 @endif">
                            <div class="card-header @if(isset($pickup)) bg-warning bg-opacity-10 @endif">
                                <h6 class="mb-0">
                                    <i class="bi bi-truck me-2"></i>Pickup &amp; Delivery Fees
                                    @if(isset($pickup))
                                        <span class="badge bg-warning text-dark ms-2">
                                            <i class="bi bi-exclamation-triangle"></i> ENTER FEES!
                                        </span>
                                    @endif
                                </h6>
                            </div>
                            <div class="card-body p-3">
                                @if(isset($pickup))
                                    <div class="btn-group-sm d-flex flex-wrap gap-2 mb-3">
                                        <button type="button" class="btn btn-sm btn-outline-primary"  onclick="window.laundryManager.setFees(50, 0)"><i class="bi bi-arrow-down-circle"></i> ₱50</button>
                                        <button type="button" class="btn btn-sm btn-outline-success"  onclick="window.laundryManager.setFees(0, 50)"><i class="bi bi-arrow-up-circle"></i> ₱50</button>
                                        <button type="button" class="btn btn-sm btn-outline-info"     onclick="window.laundryManager.setFees(50, 50)"><i class="bi bi-arrow-left-right"></i> Both</button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.laundryManager.setFees(0, 0)"><i class="bi bi-x-circle"></i> None</button>
                                    </div>
                                @endif
                                <div class="row g-2">
                                    <div class="col-12">
                                        <label class="form-label fw-semibold small">
                                            <i class="bi bi-arrow-down-circle text-primary"></i> Pickup Fee
                                            @if(isset($pickup))<span class="text-danger">*</span>@endif
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
                                            @if(isset($pickup))<span class="text-danger">*</span>@endif
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

                    {{-- Additional Notes + Summary --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-4">
                            <div class="card-header py-2 px-3">
                                <h6 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Additional Notes</h6>
                            </div>
                            <div class="card-body p-3">
                                <textarea name="notes" class="form-control form-control-sm @error('notes') is-invalid @enderror"
                                    rows="2" placeholder="Special instructions, stain notes, etc...">{{ isset($pickup) ? $pickup->notes : old('notes') }}</textarea>
                                @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror

                                {{-- Laundry Summary --}}
                                <div class="mt-3 p-2 rounded-3 border" style="background:rgba(61,59,107,0.05);">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-semibold">Service / Promo</small>
                                        <small id="summaryServiceDisplay">&#8369;0.00</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1" id="summaryAddonsRow" style="display:none!important;">
                                        <small class="text-muted fw-semibold">Add-ons</small>
                                        <small id="summaryAddonsDisplay">&#8369;0.00</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <small class="text-muted fw-semibold">Pickup + Delivery</small>
                                        <small id="summaryFeesDisplay">&#8369;0.00</small>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center border-top pt-1 mt-1">
                                        <span class="fw-bold small">Grand Total</span>
                                        <strong style="color:#3D3B6B;" id="summaryTotalDisplay">&#8369;0.00</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden legacy display spans (used by laundry-create.js) --}}
                    <div class="col-12" style="display:none;">
                        <div id="serviceBreakdown"><div id="serviceBaseInfo"></div><div id="loadsBreakdown"><div id="loadsBreakdownList"></div></div></div>
                        <span id="serviceChargesTitle"></span><span id="servicePriceDisplay"></span>
                        <span id="weightDisplay"></span><span id="quantityDisplay"></span>
                        <span id="serviceSubtotalDisplay"></span><span id="pickupFeeDisplay"></span>
                        <span id="deliveryFeeDisplay"></span><span id="totalFeesDisplay"></span>
                        <div id="extraLoadsSection"><span id="extraLoadsCount"></span><span id="extraLoadsCharge"></span></div>
                        <div id="addonsSection"><div id="addonsList"></div><span id="addonsTotalDisplay"></span></div>
                        <div id="promotionSection"><span id="promotionNameDisplay"></span><span id="promotionDiscountDisplay"></span></div>
                        <span id="gtServiceDisplay"></span>
                        <div id="gtAddonsRow"><span id="gtAddonsDisplay"></span></div>
                        <span id="gtPickupDisplay"></span><span id="gtDeliveryDisplay"></span>
                        <span id="summaryStatusBadge"></span>
                    </div>

                    {{-- ── Action Buttons ── --}}
                    <div class="col-12">
                        <div class="card border-0 shadow-sm rounded-3">
                            <div class="card-body p-2 d-flex flex-column gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-check-circle me-2"></i>Create Laundry
                                </button>
                                <a href="{{ isset($pickup) ? route('admin.pickups.show', $pickup) : route('admin.laundries.index') }}"
                                   class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-x-circle me-2"></i>Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                </div>{{-- end right inner row --}}
            </div>{{-- end col-lg-4 --}}

        </div>{{-- end outer row g-2 --}}

        <input type="hidden" name="_weight_fallback" value="0" id="weightFallback">
        <input type="hidden" name="extra_services" value="" id="extraServicesInput">
    </form>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/laundry-create.js') }}"></script>
@endpush