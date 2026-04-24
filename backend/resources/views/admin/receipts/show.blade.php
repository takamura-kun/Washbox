@extends('admin.layouts.app')

@section('title', 'Receipt for Laundry #' . $laundry->tracking_number)

@push('styles')
<style>
  /* ══════════════════════════════════════════
     RECEIPT THEME TOKENS
  ══════════════════════════════════════════ */
  :root {
    /* Light mode */
    --r-bg:           #f0f4f8;
    --r-card:         #ffffff;
    --r-card-header:  #ffffff;
    --r-card-footer:  #f8fafc;
    --r-border:       #e2e8f0;
    --r-border-soft:  #edf2f7;
    --r-text:         #1a202c;
    --r-text-muted:   #718096;
    --r-text-subtle:  #a0aec0;
    --r-label:        #4a5568;
    --r-accent:       #0077b6;
    --r-accent-light: #e6f3fb;
    --r-success:      #276749;
    --r-success-bg:   #c6f6d5;
    --r-warning:      #744210;
    --r-warning-bg:   #fefcbf;
    --r-danger:       #742a2a;
    --r-danger-bg:    #fed7d7;
    --r-table-head:   #f7fafc;
    --r-table-row:    #ffffff;
    --r-table-alt:    #f7fafc;
    --r-shadow:       0 4px 6px -1px rgba(0,0,0,.07), 0 2px 4px -1px rgba(0,0,0,.04);
    --r-shadow-lg:    0 20px 50px -12px rgba(0,0,0,.12);
    --r-total-bg:     #0077b6;
    --r-total-text:   #ffffff;
    --r-toggle-bg:    #e2e8f0;
    --r-toggle-knob:  #ffffff;
    --r-badge-text:   #ffffff;
    --r-note-bg:      #f7fafc;
    --r-note-border:  #e2e8f0;
    --r-divider:      rgba(0,0,0,.07);
    --ease:           cubic-bezier(.16,1,.3,1);
  }

  /* Dark mode */
  [data-theme="dark"] {
    --r-bg:           #0d1117;
    --r-card:         #161b22;
    --r-card-header:  #1c2333;
    --r-card-footer:  #1c2333;
    --r-border:       #30363d;
    --r-border-soft:  #21262d;
    --r-text:         #e6edf3;
    --r-text-muted:   #8b949e;
    --r-text-subtle:  #6e7681;
    --r-label:        #8b949e;
    --r-accent:       #58b4e0;
    --r-accent-light: rgba(88,180,224,.12);
    --r-success:      #56d364;
    --r-success-bg:   rgba(86,211,100,.12);
    --r-warning:      #f0b429;
    --r-warning-bg:   rgba(240,180,41,.12);
    --r-danger:       #f87171;
    --r-danger-bg:    rgba(248,113,113,.12);
    --r-table-head:   #1c2333;
    --r-table-row:    #161b22;
    --r-table-alt:    #1c2333;
    --r-shadow:       0 4px 6px -1px rgba(0,0,0,.3), 0 2px 4px -1px rgba(0,0,0,.2);
    --r-shadow-lg:    0 20px 50px -12px rgba(0,0,0,.6);
    --r-total-bg:     #1d4e70;
    --r-total-text:   #e6edf3;
    --r-toggle-bg:    #30363d;
    --r-toggle-knob:  #58b4e0;
    --r-badge-text:   #ffffff;
    --r-note-bg:      #1c2333;
    --r-note-border:  #30363d;
    --r-divider:      rgba(255,255,255,.06);
  }

  /* ══════════════════════════════════════════
     BASE
  ══════════════════════════════════════════ */
  body {
    background: var(--r-bg) !important;
    color: var(--r-text) !important;
    transition: background .3s var(--ease), color .3s var(--ease);
  }

  /* ══════════════════════════════════════════
     THEME TOGGLE
  ══════════════════════════════════════════ */
  .theme-toggle-wrap {
    display: flex; justify-content: flex-end;
    margin-bottom: 1rem;
  }
  .theme-toggle {
    display: flex; align-items: center; gap: .6rem;
    background: var(--r-card); border: 1px solid var(--r-border);
    border-radius: 40px; padding: .4rem .9rem;
    cursor: pointer; user-select: none;
    box-shadow: var(--r-shadow);
    transition: all .25s var(--ease);
    color: var(--r-text-muted); font-size: .82rem; font-weight: 500;
  }
  .theme-toggle:hover {
    border-color: var(--r-accent);
    box-shadow: 0 0 0 3px var(--r-accent-light);
  }
  .toggle-track {
    width: 36px; height: 20px; border-radius: 40px;
    background: var(--r-toggle-bg);
    position: relative; transition: background .3s;
    flex-shrink: 0;
  }
  .toggle-knob {
    position: absolute; top: 2px; left: 2px;
    width: 16px; height: 16px; border-radius: 50%;
    background: var(--r-toggle-knob);
    transition: transform .3s var(--ease), background .3s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
  }
  [data-theme="dark"] .toggle-knob { transform: translateX(16px); }
  [data-theme="dark"] .toggle-track { background: var(--r-accent); }
  .toggle-icon-sun, .toggle-icon-moon { font-size: .9rem; }
  [data-theme="dark"] .toggle-icon-sun  { display: none; }
  [data-theme="light"] .toggle-icon-moon { display: none; }

  /* ══════════════════════════════════════════
     OPTIMIZED LAYOUT IMPROVEMENTS
  ══════════════════════════════════════════ */
  
  /* Enhanced container and spacing */
  .container-fluid {
    max-width: 1200px;
    margin: 0 auto;
  }
  
  /* Improved receipt card layout */
  .receipt-card {
    background: var(--r-card);
    border: 1px solid var(--r-border) !important;
    border-radius: 16px !important;
    box-shadow: var(--r-shadow-lg) !important;
    overflow: hidden;
    transition: all .3s var(--ease);
    margin-bottom: 2rem;
  }
  
  /* Enhanced table responsiveness */
  .receipt-table {
    width: 100%; 
    border-collapse: collapse;
    border-radius: 12px; 
    overflow: hidden;
    border: 1px solid var(--r-border);
    font-variant-numeric: tabular-nums; /* Better number alignment */
  }
  
  /* Improved button interactions */
  .r-btn {
    display: inline-flex; 
    align-items: center; 
    gap: .45rem;
    padding: .6rem 1.3rem; 
    border-radius: 10px;
    font-size: .85rem; 
    font-weight: 600;
    text-decoration: none; 
    cursor: pointer; 
    border: none;
    transition: all .22s var(--ease);
    white-space: nowrap;
    user-select: none;
  }
  
  /* Enhanced theme toggle */
  .theme-toggle {
    display: flex; 
    align-items: center; 
    gap: .6rem;
    background: var(--r-card); 
    border: 1px solid var(--r-border);
    border-radius: 40px; 
    padding: .4rem .9rem;
    cursor: pointer; 
    user-select: none;
    box-shadow: var(--r-shadow);
    transition: all .25s var(--ease);
    color: var(--r-text-muted); 
    font-size: .82rem; 
    font-weight: 500;
    backdrop-filter: blur(10px);
  }
  
  /* Better payment status styling */
  .payment-status-section {
    margin-top: 1.5rem;
    padding: 1.2rem;
    background: var(--r-table-head);
    border: 1px solid var(--r-border);
    border-radius: 12px;
    backdrop-filter: blur(5px);
  }
  
  /* Enhanced info panels */
  .info-panel {
    background: var(--r-table-head);
    border: 1px solid var(--r-border);
    border-radius: 12px; 
    padding: 1.2rem 1.4rem;
    transition: all .3s var(--ease);
    backdrop-filter: blur(5px);
  }
  
  .info-panel:hover {
    transform: translateY(-2px);
    box-shadow: var(--r-shadow-lg);
  }

  /* Header */
  .receipt-header {
    background: var(--r-card-header);
    border-bottom: 1px solid var(--r-border) !important;
    padding: 1.8rem 2rem;
    position: relative; overflow: hidden;
    transition: background .3s var(--ease);
  }
  .receipt-header::before {
    content: '';
    position: absolute; top: 0; left: 0; right: 0; height: 3px;
    background: linear-gradient(90deg, var(--r-accent), #3b82f6, #8b5cf6);
  }
  .receipt-header-brand {
    font-size: 1.5rem; font-weight: 800; letter-spacing: -.5px;
    color: var(--r-text);
  }
  .receipt-header-brand span { color: var(--r-accent); }
  .receipt-header-sub {
    color: var(--r-text-muted); font-size: .85rem; margin-top: .1rem;
  }
  .receipt-number {
    font-size: 1.2rem; font-weight: 700;
    color: var(--r-accent); letter-spacing: -.3px;
    font-family: 'DM Mono', 'Courier New', monospace;
  }
  .receipt-date {
    color: var(--r-text-muted); font-size: .82rem; margin-top: .25rem;
  }
  .receipt-tag {
    display: inline-block;
    background: var(--r-accent-light);
    color: var(--r-accent);
    font-size: .65rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    padding: .2rem .7rem; border-radius: 40px;
    border: 1px solid var(--r-accent);
    margin-bottom: .5rem;
  }

  /* Body */
  .receipt-body {
    padding: 2rem;
    background: var(--r-card);
    transition: background .3s var(--ease);
  }

  /* Section labels */
  .section-label {
    font-size: .65rem; font-weight: 700;
    letter-spacing: 2px; text-transform: uppercase;
    color: var(--r-text-subtle); margin-bottom: .7rem;
    display: flex; align-items: center; gap: .5rem;
  }
  .section-label::after {
    content: ''; flex: 1; height: 1px;
    background: var(--r-divider);
  }

  /* Info blocks */
  .info-block p {
    color: var(--r-text); margin-bottom: .3rem; font-size: .9rem;
  }
  .info-block p strong { color: var(--r-text); }
  .info-block p i { color: var(--r-accent); }
  .info-name {
    font-size: 1.05rem; font-weight: 700; color: var(--r-text);
    margin-bottom: .4rem;
  }

  /* Badges */
  .r-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    padding: .25rem .7rem; border-radius: 40px;
    font-size: .72rem; font-weight: 700; letter-spacing: .3px;
  }
  .r-badge-success { background: var(--r-success-bg); color: var(--r-success); }
  .r-badge-warning { background: var(--r-warning-bg); color: var(--r-warning); }
  .r-badge-danger  { background: var(--r-danger-bg);  color: var(--r-danger);  }

  /* Divider */
  .r-divider {
    height: 1px; background: var(--r-divider);
    margin: 1.5rem 0; border: none;
  }

  /* ══════════════════════════════════════════
     TABLE
  ══════════════════════════════════════════ */
  .receipt-table {
    width: 100%; border-collapse: collapse;
    border-radius: 12px; overflow: hidden;
    border: 1px solid var(--r-border);
  }
  .receipt-table thead tr {
    background: var(--r-table-head);
  }
  .receipt-table thead th {
    padding: .85rem 1rem; font-size: .72rem;
    font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase;
    color: var(--r-text-muted); border-bottom: 1px solid var(--r-border);
    white-space: nowrap;
  }
  .receipt-table tbody tr {
    background: var(--r-table-row);
    border-bottom: 1px solid var(--r-border-soft);
    transition: background .15s;
  }
  .receipt-table tbody tr:nth-child(even) { background: var(--r-table-alt); }
  .receipt-table tbody tr:hover { background: var(--r-accent-light); }
  .receipt-table tbody td {
    padding: .9rem 1rem; font-size: .9rem; color: var(--r-text);
    vertical-align: middle;
  }
  .receipt-table tbody td strong { color: var(--r-text); }
  .receipt-table .item-sub { font-size: .75rem; color: var(--r-text-muted); margin-top: .2rem; }
  .receipt-table .discount-row { background: var(--r-success-bg) !important; }
  .receipt-table .discount-row td { color: var(--r-success) !important; }

  /* Footer rows */
  .receipt-table tfoot tr {
    border-top: 1px solid var(--r-border);
    background: var(--r-table-head);
  }
  .receipt-table tfoot td,
  .receipt-table tfoot th {
    padding: .65rem 1rem; font-size: .88rem;
    color: var(--r-text-muted);
  }
  .receipt-table tfoot .total-row {
    background: var(--r-total-bg);
  }
  .receipt-table tfoot .total-row td,
  .receipt-table tfoot .total-row th {
    color: var(--r-total-text) !important;
    font-size: 1rem; font-weight: 800;
    padding: 1rem;
  }

  /* ══════════════════════════════════════════
     PAYMENT & TIMELINE BLOCKS
  ══════════════════════════════════════════ */
  .info-grid {
    display: grid; grid-template-columns: 1fr 1fr;
    gap: 1.5rem; margin-top: 1.5rem;
  }
  .info-panel {
    background: var(--r-table-head);
    border: 1px solid var(--r-border);
    border-radius: 12px; padding: 1.2rem 1.4rem;
    transition: background .3s var(--ease);
  }

  /* Timeline */
  .timeline { display: flex; flex-direction: column; gap: .5rem; margin-top: .5rem; }
  .timeline-item {
    display: flex; align-items: center; gap: .7rem;
    font-size: .82rem; color: var(--r-text-muted);
  }
  .timeline-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--r-accent); flex-shrink: 0;
  }
  .timeline-item strong { color: var(--r-text); margin-right: .3rem; }

  /* Notes */
  .receipt-notes {
    background: var(--r-note-bg);
    border: 1px solid var(--r-note-border);
    border-left: 3px solid var(--r-accent);
    border-radius: 10px; padding: 1rem 1.2rem;
    margin-top: 1.5rem;
    transition: background .3s var(--ease);
  }
  .receipt-notes p { color: var(--r-text); font-size: .88rem; margin: 0; }

  /* Footer */
  .receipt-footer-band {
    background: var(--r-card-footer);
    border-top: 1px solid var(--r-border);
    padding: 1.5rem 2rem; text-align: center;
    transition: background .3s var(--ease);
  }
  .receipt-footer-brand {
    font-size: 1.1rem; font-weight: 800; color: var(--r-text);
  }
  .receipt-footer-brand span { color: var(--r-accent); }
  .receipt-footer-band p {
    color: var(--r-text-muted); font-size: .8rem; margin: .2rem 0 0;
  }
  .receipt-footer-band .tagline {
    font-style: italic; color: var(--r-text-subtle); font-size: .78rem;
  }

  /* Actions */
  .receipt-actions {
    background: var(--r-card-footer);
    border-top: 1px solid var(--r-border);
    padding: 1rem 2rem;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: .75rem;
    transition: background .3s var(--ease);
  }
  .r-btn {
    display: inline-flex; align-items: center; gap: .45rem;
    padding: .6rem 1.3rem; border-radius: 10px;
    font-size: .85rem; font-weight: 600;
    text-decoration: none; cursor: pointer; border: none;
    transition: all .22s var(--ease);
  }
  .r-btn-ghost {
    background: transparent;
    border: 1px solid var(--r-border);
    color: var(--r-text-muted);
  }
  .r-btn-ghost:hover {
    border-color: var(--r-accent); color: var(--r-accent);
    background: var(--r-accent-light);
  }
  .r-btn-primary {
    background: var(--r-accent); color: #fff;
    box-shadow: 0 4px 12px -3px rgba(0,119,182,.4);
  }
  .r-btn-primary:hover {
    filter: brightness(1.1); transform: translateY(-1px);
    box-shadow: 0 8px 20px -5px rgba(0,119,182,.45);
    color: #fff;
  }
  .r-btn-success {
    background: #276749; color: #fff;
    box-shadow: 0 4px 12px -3px rgba(39,103,73,.35);
  }
  [data-theme="dark"] .r-btn-success { background: #1f5c3e; }
  .r-btn-success:hover {
    filter: brightness(1.1); transform: translateY(-1px); color: #fff;
  }

  /* ══════════════════════════════════════════
     PRINT
  ══════════════════════════════════════════ */
  @media print {
    body { background: #fff !important; color: #000 !important; }
    .theme-toggle-wrap, .receipt-actions, .btn { display: none !important; }
    .receipt-card { border: none !important; box-shadow: none !important; border-radius: 0 !important; }
    .receipt-header::before { display: none; }

    /* Force light palette when printing */
    * {
      --r-bg: #ffffff;
      --r-card: #ffffff;
      --r-card-header: #ffffff;
      --r-card-footer: #f8fafc;
      --r-border: #dee2e6;
      --r-text: #000000;
      --r-text-muted: #555555;
      --r-table-head: #f8f9fa;
      --r-table-row: #ffffff;
      --r-table-alt: #f8f9fa;
      --r-total-bg: #0077b6;
      --r-total-text: #ffffff;
    }

    .receipt-table { page-break-inside: avoid; }
  }

  /* ══════════════════════════════════════════
     RESPONSIVE
  ══════════════════════════════════════════ */
  @media (max-width: 640px) {
    .receipt-header, .receipt-body, .receipt-actions { padding: 1.2rem; }
    .info-grid { grid-template-columns: 1fr; }
    .receipt-header .row > div + div { margin-top: 1rem; text-align: left !important; }
  }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" id="receiptPage">
  <div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">

      {{-- Theme Toggle --}}
      <div class="theme-toggle-wrap">
        <button class="theme-toggle" id="themeToggle" aria-label="Toggle dark/light mode">
          <i class="bi bi-sun-fill toggle-icon-sun"></i>
          <i class="bi bi-moon-fill toggle-icon-moon" style="display:none"></i>
          <div class="toggle-track">
            <div class="toggle-knob"></div>
          </div>
          <span id="themeLabel">Light Mode</span>
        </button>
      </div>

      {{-- Receipt Card --}}
      <div class="receipt-card" id="receiptCard">

        {{-- ── Header ── --}}
        <div class="receipt-header">
          <div class="row align-items-center">
            <div class="col-7">
              <div class="receipt-tag">Official Receipt</div>
              <div class="receipt-header-brand">Wash<span>Box</span></div>
              <div class="receipt-header-sub">Laundry Service — {{ $laundry->branch->name ?? 'Main Branch' }}</div>
            </div>
            <div class="col-5 text-end">
              <div style="font-size:.65rem;text-transform:uppercase;letter-spacing:2px;color:var(--r-text-subtle);margin-bottom:.3rem;">Receipt No.</div>
              <div class="receipt-number">#{{ $laundry->tracking_number }}</div>
              <div class="receipt-date">{{ $laundry->created_at->format('M d, Y · h:i A') }}</div>
            </div>
          </div>
        </div>

        {{-- ── Body ── --}}
        <div class="receipt-body">

          {{-- Customer + Laundry Info --}}
          <div class="row mb-0">
            <div class="col-md-6 mb-3 mb-md-0">
              <div class="section-label">Billed To</div>
              <div class="info-block">
                <div class="info-name">{{ $laundry->customer->name }}</div>
                @if($laundry->customer->phone)
                  <p><i class="bi bi-telephone-fill"></i> {{ $laundry->customer->phone }}</p>
                @endif
                @if($laundry->customer->address)
                  <p><i class="bi bi-geo-alt-fill"></i> {{ $laundry->customer->address }}</p>
                @endif
              </div>
            </div>
            <div class="col-md-6">
              <div class="section-label">Laundry Info</div>
              <div class="info-block">
                <p>
                  <strong>Status: </strong>
                  @if($laundry->status == 'completed')
                    <span class="r-badge r-badge-success"><i class="bi bi-check-circle-fill"></i> Completed</span>
                  @elseif($laundry->status == 'cancelled')
                    <span class="r-badge r-badge-danger"><i class="bi bi-x-circle-fill"></i> Cancelled</span>
                  @else
                    <span class="r-badge r-badge-warning"><i class="bi bi-clock-fill"></i> {{ ucfirst($laundry->status) }}</span>
                  @endif
                </p>
                <p><strong>Branch:</strong> {{ $laundry->branch->name }}</p>
                @if($laundry->staff)
                  <p><strong>Staff:</strong> {{ $laundry->staff->name }}</p>
                @endif
              </div>
            </div>
          </div>

          <hr class="r-divider">

          {{-- Items Table --}}
          <div class="section-label">Bill Details</div>
          <div style="overflow-x:auto; border-radius:12px; border:1px solid var(--r-border);">
            <table class="receipt-table" style="border:none;">
              <thead>
                <tr>
                  <th style="width:45%">Description</th>
                  <th class="text-center">Qty / Weight</th>
                  <th class="text-end">Unit Price</th>
                  <th class="text-end">Amount</th>
                </tr>
              </thead>
              <tbody>
                {{-- Service --}}
                @if($laundry->service)
                <tr>
                  <td>
                    <strong>{{ $laundry->service->name }}</strong>
                    <div class="item-sub">
                      @if($laundry->service->pricing_type == 'per_load')
                        {{ $laundry->service->service_type == 'special_item' ? 'Per piece' : 'Per load' }}
                      @else
                        Per kg
                      @endif
                    </div>
                  </td>
                  <td class="text-center">
                    @if($laundry->service->pricing_type == 'per_load')
                      {{ $laundry->number_of_loads ?? 1 }}
                      {{ $laundry->service->service_type == 'special_item' ? 'pcs' : 'loads' }}
                    @else
                      {{ number_format($laundry->weight, 2) }} kg
                    @endif
                  </td>
                  <td class="text-end">
                    @if($laundry->service->pricing_type == 'per_load')
                      ₱{{ number_format($laundry->service->price_per_load, 2) }}
                    @else
                      ₱{{ number_format($laundry->service->price_per_piece, 2) }}
                    @endif
                  </td>
                  <td class="text-end"><strong>₱{{ number_format($laundry->subtotal, 2) }}</strong></td>
                </tr>
                @endif

                {{-- Add-ons --}}
                @foreach($laundry->addons as $addon)
                <tr>
                  <td>
                    <strong>{{ $addon->name }}</strong>
                    <div class="item-sub">Add-on service</div>
                  </td>
                  <td class="text-center">{{ $addon->pivot->quantity }}</td>
                  <td class="text-end">₱{{ number_format($addon->pivot->price_at_purchase, 2) }}</td>
                  <td class="text-end">₱{{ number_format($addon->pivot->price_at_purchase * $addon->pivot->quantity, 2) }}</td>
                </tr>
                @endforeach

                {{-- Pickup Fee --}}
                @if($laundry->pickup_fee > 0)
                <tr>
                  <td colspan="3"><strong><i class="bi bi-arrow-down-circle" style="color:var(--r-accent)"></i> Pickup Fee</strong></td>
                  <td class="text-end">₱{{ number_format($laundry->pickup_fee, 2) }}</td>
                </tr>
                @endif

                {{-- Delivery Fee --}}
                @if($laundry->delivery_fee > 0)
                <tr>
                  <td colspan="3"><strong><i class="bi bi-arrow-up-circle" style="color:var(--r-success)"></i> Delivery Fee</strong></td>
                  <td class="text-end">₱{{ number_format($laundry->delivery_fee, 2) }}</td>
                </tr>
                @endif

                {{-- Discount --}}
                @if($laundry->discount_amount > 0)
                <tr class="discount-row">
                  <td colspan="3">
                    <strong><i class="bi bi-tag-fill"></i> Discount</strong>
                    @if($laundry->promotion)
                      <div class="item-sub">{{ $laundry->promotion->name }}</div>
                    @endif
                  </td>
                  <td class="text-end"><strong>-₱{{ number_format($laundry->discount_amount, 2) }}</strong></td>
                </tr>
                @endif
              </tbody>
              <tfoot>
                <tr>
                  <td colspan="3" class="text-end" style="color:var(--r-text-muted);font-weight:600;">Subtotal</td>
                  <td class="text-end" style="color:var(--r-text);font-weight:700;">₱{{ number_format($laundry->subtotal, 2) }}</td>
                </tr>
                @if($laundry->addons_total > 0)
                <tr>
                  <td colspan="3" class="text-end" style="color:var(--r-text-muted);font-weight:600;">Add-ons</td>
                  <td class="text-end" style="color:var(--r-text);font-weight:700;">₱{{ number_format($laundry->addons_total, 2) }}</td>
                </tr>
                @endif
                @if($laundry->pickup_fee + $laundry->delivery_fee > 0)
                <tr>
                  <td colspan="3" class="text-end" style="color:var(--r-text-muted);font-weight:600;">Service Fees</td>
                  <td class="text-end" style="color:var(--r-text);font-weight:700;">₱{{ number_format($laundry->pickup_fee + $laundry->delivery_fee, 2) }}</td>
                </tr>
                @endif
                @if($laundry->discount_amount > 0)
                <tr>
                  <td colspan="3" class="text-end" style="color:var(--r-success);font-weight:600;">Discount</td>
                  <td class="text-end" style="color:var(--r-success);font-weight:700;">-₱{{ number_format($laundry->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr class="total-row">
                  <td colspan="3" class="text-end" style="font-weight:800;font-size:1rem;letter-spacing:.5px;text-transform:uppercase;">Total Amount to Pay</td>
                  <td class="text-end" style="font-size:1.3rem;font-weight:900;letter-spacing:-.5px;">₱{{ number_format($laundry->total_amount, 2) }}</td>
                </tr>
              </tfoot>
            </table>
          </div>

          {{-- Payment Status Section --}}
          <div class="payment-status-section">
            <div class="section-label" style="margin-bottom:.8rem;">Payment Status</div>
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
              <span style="font-size:.88rem;color:var(--r-text-muted);font-weight:600;">Status:</span>
              @if($laundry->payment_status == 'paid')
                <span class="r-badge r-badge-success" style="font-size:.8rem;padding:.3rem .8rem;"><i class="bi bi-check-circle-fill"></i> PAID</span>
              @else
                <span class="r-badge r-badge-warning" style="font-size:.8rem;padding:.3rem .8rem;"><i class="bi bi-clock-fill"></i> UNPAID</span>
              @endif
            </div>
            @if($laundry->payment_method)
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:.5rem;">
              <span style="font-size:.88rem;color:var(--r-text-muted);font-weight:600;">Method:</span>
              <span style="font-size:.88rem;color:var(--r-text);font-weight:700;text-transform:uppercase;">{{ $laundry->payment_method }}</span>
            </div>
            @endif
            @if($laundry->paid_at)
            <div style="display:flex;align-items:center;justify-content:space-between;">
              <span style="font-size:.88rem;color:var(--r-text-muted);font-weight:600;">Paid At:</span>
              <span style="font-size:.88rem;color:var(--r-text);font-weight:600;">{{ $laundry->paid_at->format('M d, Y · h:i A') }}</span>
            </div>
            @endif
          </div>

          {{-- Payment + Timeline --}}
          <div class="info-grid">
            <div class="info-panel">
              <div class="section-label" style="margin-bottom:.8rem;">Payment Details</div>
              @if($laundry->payment_method)
                <p style="font-size:.88rem;color:var(--r-text);margin-bottom:.4rem;">
                  <strong style="color:var(--r-text-muted);font-size:.75rem;display:block;margin-bottom:.1rem;">METHOD</strong>
                  {{ ucfirst($laundry->payment_method) }}
                </p>
              @endif
              @if($laundry->payment_status)
                <p style="font-size:.88rem;color:var(--r-text);margin-bottom:.4rem;">
                  <strong style="color:var(--r-text-muted);font-size:.75rem;display:block;margin-bottom:.1rem;">STATUS</strong>
                  @if($laundry->payment_status == 'paid')
                    <span class="r-badge r-badge-success"><i class="bi bi-check-circle-fill"></i> Paid</span>
                  @else
                    <span class="r-badge r-badge-warning"><i class="bi bi-clock-fill"></i> {{ ucfirst($laundry->payment_status) }}</span>
                  @endif
                </p>
              @endif
              @if($laundry->paid_at)
                <p style="font-size:.82rem;color:var(--r-text-muted);margin:0;">
                  <strong>Paid:</strong> {{ $laundry->paid_at->format('M d, Y · h:i A') }}
                </p>
              @endif
            </div>

            <div class="info-panel">
              <div class="section-label" style="margin-bottom:.8rem;">Timeline</div>
              <div class="timeline">
                @if($laundry->received_at)
                <div class="timeline-item">
                  <div class="timeline-dot" style="background:#8b5cf6;"></div>
                  <span><strong>Received</strong> {{ $laundry->received_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if($laundry->ready_at)
                <div class="timeline-item">
                  <div class="timeline-dot" style="background:var(--r-accent);"></div>
                  <span><strong>Ready</strong> {{ $laundry->ready_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if($laundry->completed_at)
                <div class="timeline-item">
                  <div class="timeline-dot" style="background:var(--r-success);"></div>
                  <span><strong>Completed</strong> {{ $laundry->completed_at->format('M d, Y') }}</span>
                </div>
                @endif
                @if(!$laundry->received_at && !$laundry->ready_at && !$laundry->completed_at)
                  <p style="font-size:.82rem;color:var(--r-text-subtle);margin:0;">No timeline data yet.</p>
                @endif
              </div>
            </div>
          </div>

          {{-- Notes --}}
          @if($laundry->notes)
          <div class="receipt-notes">
            <div class="section-label" style="margin-bottom:.5rem;">Notes</div>
            <p>{{ $laundry->notes }}</p>
          </div>
          @endif

        </div>

        {{-- ── Footer Band ── --}}
        <div class="receipt-footer-band">
          <div class="receipt-footer-brand">Wash<span>Box</span> Laundry Service</div>
          <p>{{ $laundry->branch->address ?? 'Main Branch' }} &nbsp;·&nbsp; {{ $laundry->branch->phone ?? 'N/A' }}</p>
          <p class="tagline">Thank you for trusting us with your laundry! 🫧</p>
        </div>

        {{-- ── Actions ── --}}
        <div class="receipt-actions">
          <a href="{{ route('admin.laundries.show', $laundry) }}" class="r-btn r-btn-ghost">
            <i class="bi bi-arrow-left"></i> Back to Laundry
          </a>
          <div style="display:flex;gap:.6rem;flex-wrap:wrap;">
            <button onclick="window.print()" class="r-btn r-btn-primary">
              <i class="bi bi-printer-fill"></i> Print Receipt
            </button>
            <a href="{{ route('admin.laundries.show', $laundry) }}?download=1" class="r-btn r-btn-success">
              <i class="bi bi-download"></i> Download PDF
            </a>
          </div>
        </div>

      </div>{{-- /receipt-card --}}
    </div>
  </div>
</div>

<script>
(function() {
  const STORAGE_KEY = 'wb_receipt_theme';
  const html        = document.documentElement;
  const btn         = document.getElementById('themeToggle');
  const label       = document.getElementById('themeLabel');
  const iconSun     = btn.querySelector('.toggle-icon-sun');
  const iconMoon    = btn.querySelector('.toggle-icon-moon');

  // Detect initial preference: saved → OS → light
  function getPreferred() {
    const saved = localStorage.getItem(STORAGE_KEY);
    if (saved) return saved;
    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
  }

  function applyTheme(theme) {
    html.setAttribute('data-theme', theme);
    label.textContent = theme === 'dark' ? 'Dark Mode' : 'Light Mode';
    iconSun.style.display  = theme === 'dark'  ? 'none'         : 'inline';
    iconMoon.style.display = theme === 'dark'  ? 'inline'       : 'none';
    localStorage.setItem(STORAGE_KEY, theme);
  }

  // Init
  applyTheme(getPreferred());

  // Toggle
  btn.addEventListener('click', () => {
    const current = html.getAttribute('data-theme') || 'light';
    applyTheme(current === 'dark' ? 'light' : 'dark');
  });

  // Respond to OS theme changes
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
    if (!localStorage.getItem(STORAGE_KEY)) applyTheme(e.matches ? 'dark' : 'light');
  });
})();
</script>
@endsection
