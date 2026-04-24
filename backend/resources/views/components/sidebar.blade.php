@props(['role' => 'admin'])

<aside class="sidebar shadow" id="sidebar">
    {{-- Desktop Toggle Button --}}
    <div class="sidebar-toggle-btn" id="desktopToggleBtn">
        <i class="bi bi-chevron-left"></i>
    </div>

    <div class="sidebar-brand">
        <div class="d-flex flex-column align-items-center text-center py-2 position-relative">
            {{-- Logo Container --}}
            <div class="logo-container mb-2 shadow-sm"
                 style="width: 85px; height: 85px; border-radius: 50%; overflow: hidden; border: 2px solid rgba(255,255,255,0.3); background: white;">
                <img src="{{ asset('images/logo.png') }}"
                     alt="WashBox Logo"
                     style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%; padding: 8px;">
                     {{-- ^^^ ADDED border-radius: 50% HERE ^^^ --}}
            </div>

            {{-- Brand Text --}}
            <div class="brand-text">
                <h5 class="mb-1 fw-bold" style="font-size: 1.1rem; color: white !important;">WASHBOX</h5>

                {{-- Branch Info --}}
                @if($role === 'staff' && auth()->user()->branch)
                    <div class="branch-info mt-1">
                        <small class="d-block fw-bold" style="font-size: 0.6rem; letter-spacing: 1px; color: rgba(255, 92, 53, 0.9) !important;">
                            {{ strtoupper(auth()->user()->branch->name) }} BRANCH
                        </small>
                    </div>
                @endif
            </div>

            {{-- Mobile Close Button --}}
            <button class="btn btn-link text-white d-md-none p-0 position-absolute"
                    id="sidebarClose"
                    style="top: 10px; right: 15px;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

    <ul class="sidebar-menu">
        <x-admin.nav-item route="{{ $role }}.dashboard" icon="bi-speedometer2" label="Dashboard" />

        {{-- Management Section --}}
        @if($role === 'admin')
            <li><span class="nav-label">Management</span></li>
            <x-admin.nav-item route="admin.analytics" icon="bi-graph-up" label="Analytics" />
            <x-admin.nav-item route="admin.branches.index" icon="bi-shop" label="Branches" />
            <x-admin.nav-item route="admin.service-types.index" icon="bi-grid-3x3-gap" label="Service Types" />
            <x-admin.nav-item route="admin.services.index" icon="bi-droplet" label="Services & Add-Ons" />
        @endif

        {{-- Operations Section --}}
        <li><span class="nav-label">Operations</span></li>
        @if($role === 'staff')
            <x-admin.nav-item route="staff.laundries.create" icon="bi-plus-circle text-info" label="New Laundry" />
        @endif
        <x-admin.nav-item route="{{ $role }}.laundries.index" icon="bi-basket" label="Laundries" />
        <x-admin.nav-item route="{{ $role }}.pickups.index" icon="bi-truck" label="Pickups" />
        <x-admin.nav-item route="{{ $role }}.customers.index" icon="bi-people" label="Customers" />
        <x-admin.nav-item route="{{ $role }}.unclaimed.index" icon="bi-exclamation-octagon text-warning" label="Unclaimed" />
        @if($role === 'staff')
            @php
                // Don't show badge if we're currently on the ratings page
                $newStaffRatings = request()->routeIs('staff.ratings.*') ? 0 : \App\Models\CustomerRating::where('branch_id', auth()->user()->branch_id ?? 0)
                    ->whereNull('viewed_at')
                    ->count();
            @endphp
            <x-admin.nav-item 
                route="staff.ratings.index" 
                icon="bi-star" 
                label="Ratings"
                :badge="$newStaffRatings"
                badgeClass="bg-danger rounded-pill" />
        @endif

        {{-- Admin Section --}}
        @if($role === 'admin')
            <li><span class="nav-label">Admin</span></li>
            <x-admin.nav-item route="admin.promotions.index" icon="bi-megaphone" label="Promotions" />
            @php
                $pendingPayments = \App\Models\PaymentProof::where('status', 'pending')->count();
            @endphp
            <x-admin.nav-item 
                route="admin.payments.verification.index" 
                icon="bi-credit-card-2-front text-success" 
                label="Payment Verification"
                :badge="$pendingPayments"
                badgeClass="bg-warning text-dark rounded-pill" />
            <x-admin.nav-item route="admin.staff.index" icon="bi-person-badge" label="Staff" />
            @php
                $newRatings = \App\Models\CustomerRating::whereNull('viewed_at')->count();
            @endphp
            <x-admin.nav-item 
                route="admin.reports.index" 
                icon="bi-file-earmark-bar-graph" 
                label="Reports"
                :badge="$newRatings"
                badgeClass="bg-danger rounded-pill" />
        @endif

        <li><hr class="border-white opacity-10 mx-3"></li>
        {{-- Use existing settings route --}}
        <x-admin.nav-item route="{{ $role }}.settings" icon="bi-gear" label="Settings" />
    </ul>
</aside>
