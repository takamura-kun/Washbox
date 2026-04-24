@props(['role' => 'staff'])

<aside class="sidebar shadow" id="sidebar">
    {{-- Desktop Toggle Button --}}
    <div class="sidebar-toggle-btn" id="desktopToggleBtn">
        <i class="bi bi-chevron-left"></i>
    </div>

    <div class="sidebar-brand">
        <div class="d-flex flex-column align-items-center text-center py-2 position-relative">
            {{-- Logo Container --}}
            <div class="logo-circle mb-2">
                <img src="{{ asset('images/logo.png') }}"
                     alt="WashBox Logo">
            </div>

            {{-- Brand Text --}}
            <div class="brand-text">
                <h3 class="text-white mb-0">WASHBOX</h3>
                @auth
                    @if(auth()->user()->branch)
                        <small class="text-white-50 d-block mt-1">
                            {{ auth()->user()->branch->name }} BRANCH
                        </small>
                    @endif
                @endauth
            </div>

            {{-- Mobile Close Button --}}
            <button class="btn btn-link text-white d-md-none p-0 position-absolute"
                    id="sidebarClose"
                    style="top: 10px; right: 15px;">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
    </div>

 
    {{-- Branch Analytics Widget --}}
    @auth
    @if(auth()->user()->branch)
    <div class="px-3 mb-3">
        <div class="card shadow-sm" style="background: white; border-radius: 8px; border: none;">
            <div class="card-body p-3">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold" style="font-size: 0.85rem; color: #333;">Analytics</h6>
                    <a href="{{ route('admin.branches.analytics', auth()->user()->branch_id) }}" class="text-decoration-none" style="font-size: 0.75rem; color: #ff5c35;">Manage →</a>
                </div>
                @php
                    try {
                        $branchId = auth()->user()->branch_id;
                        $todayLaundries = \App\Models\Laundry::where('branch_id', $branchId)->whereDate('created_at', today())->count();
                        $todayRevenue = \App\Models\Laundry::where('branch_id', $branchId)->whereDate('created_at', today())->sum('total_price');
                        $pendingPickups = \App\Models\Pickup::where('branch_id', $branchId)->where('status', 'pending')->count();
                    } catch (\Exception $e) {
                        $todayLaundries = 0;
                        $todayRevenue = 0;
                        $pendingPickups = 0;
                    }
                @endphp
                <div class="d-flex flex-column gap-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="flex: 1;">
                            <div style="height: 3px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                                <div style="height: 100%; width: {{ min(($todayLaundries / 30) * 100, 100) }}%; background: linear-gradient(90deg, #ff5c35, #ff8c42); border-radius: 2px;"></div>
                            </div>
                        </div>
                        <div class="ms-2 text-end">
                            <div class="fw-bold" style="font-size: 0.9rem; color: #333;">{{ $todayLaundries }}</div>
                            <div style="font-size: 0.65rem; color: #6c757d;">Orders</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="flex: 1;">
                            <div style="height: 3px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                                <div style="height: 100%; width: {{ min(($todayRevenue / 5000) * 100, 100) }}%; background: linear-gradient(90deg, #28a745, #5cb85c); border-radius: 2px;"></div>
                            </div>
                        </div>
                        <div class="ms-2 text-end">
                            <div class="fw-bold" style="font-size: 0.9rem; color: #333;">₱{{ number_format($todayRevenue, 2) }}</div>
                            <div style="font-size: 0.65rem; color: #6c757d;">Revenue</div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div style="flex: 1;">
                            <div style="height: 3px; background: #e9ecef; border-radius: 2px; overflow: hidden;">
                                <div style="height: 100%; width: {{ min(($pendingPickups / 10) * 100, 100) }}%; background: linear-gradient(90deg, #ffc107, #ffdb4d); border-radius: 2px;"></div>
                            </div>
                        </div>
                        <div class="ms-2 text-end">
                            <div class="fw-bold" style="font-size: 0.9rem; color: #333;">{{ $pendingPickups }}</div>
                            <div style="font-size: 0.65rem; color: #6c757d;">Pickups</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endauth

    <ul class="sidebar-menu">
        {{-- Dashboard --}}
        <li class="nav-item">
            <a href="{{ route('branch.dashboard') }}"
               class="nav-link {{ request()->routeIs('branch.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i>
                <span class="menu-text">Dashboard</span>
            </a>
        </li>

        {{-- Operations Section --}}
        <li class="nav-label">Operations</li>

        {{-- New Laundry --}}
        <li class="nav-item">
            <a href="{{ route('branch.laundries.create') }}"
               class="nav-link {{ request()->routeIs('branch.laundries.create') ? 'active' : '' }}">
                <i class="bi bi-plus-circle text-info"></i>
                <span class="menu-text">New Laundry</span>
            </a>
        </li>

        {{-- Laundries --}}
        <li class="nav-item">
            <a href="{{ route('branch.laundries.index') }}"
               class="nav-link {{ request()->routeIs('branch.laundries.*') && !request()->routeIs('branch.laundries.create') ? 'active' : '' }}">
                <i class="bi bi-basket"></i>
                <span class="menu-text">Laundries</span>
            </a>
        </li>

        {{-- Pickups --}}
        <li class="nav-item">
            <a href="{{ route('branch.pickups.index') }}"
               class="nav-link {{ request()->routeIs('branch.pickups.*') ? 'active' : '' }}">
                <i class="bi bi-truck"></i>
                <span class="menu-text">Pickups</span>
            </a>
        </li>

        {{-- Customers --}}
        <li class="nav-item">
            <a href="{{ route('branch.customers.index') }}"
               class="nav-link {{ request()->routeIs('branch.customers.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span class="menu-text">Customers</span>
            </a>
        </li>

        {{-- Ratings --}}
        <li class="nav-item">
            <a href="{{ route('branch.ratings.index') }}"
               class="nav-link {{ request()->routeIs('branch.ratings.*') ? 'active' : '' }}">
                <i class="bi bi-star text-warning"></i>
                <span class="menu-text">Ratings</span>
                @php
                    $branchRatings = \App\Models\CustomerRating::where('branch_id', auth()->guard('branch')->user()->id ?? 0)->count();
                @endphp
                @if($branchRatings > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $branchRatings }}</span>
                @endif
            </a>
        </li>

        {{-- Services & Add-ons Dropdown --}}
        @php
            $isServicesActive = request()->routeIs('branch.services.*');
            $isAddonsActive = request()->routeIs('branch.addons.*');
            $isServicesDropdownActive = $isServicesActive || $isAddonsActive;
        @endphp

        <li class="nav-item dropdown-container {{ $isServicesDropdownActive ? 'active' : '' }}">
            <a href="javascript:void(0);"
               class="nav-link dropdown-toggle {{ $isServicesDropdownActive ? 'active' : '' }}"
               onclick="toggleServicesDropdown(this)">
                <i class="bi bi-grid-3x3-gap-fill text-primary"></i>
                <span class="menu-text">Services & Add-ons</span>
                <span class="badge-container">
                    <i class="bi bi-chevron-down dropdown-arrow {{ $isServicesDropdownActive ? 'rotated' : '' }}"></i>
                </span>
            </a>
            <ul class="dropdown-menu-items {{ $isServicesDropdownActive ? 'show' : '' }}" id="servicesDropdown">
                <li class="nav-item">
                    <a href="{{ route('branch.services.index') }}"
                       class="nav-link {{ $isServicesActive ? 'active' : '' }}">
                        <i class="bi bi-droplet"></i>
                        <span class="menu-text">Services</span>
                        @php
                            $servicesCount = \App\Models\Service::count();
                        @endphp
                        @if($servicesCount > 0)
                            <span class="badge bg-primary rounded-pill ms-auto">{{ $servicesCount }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('branch.addons.index') }}"
                       class="nav-link {{ $isAddonsActive ? 'active' : '' }}">
                        <i class="bi bi-plus-circle"></i>
                        <span class="menu-text">Add-ons</span>
                        @php
                            $addonsCount = \App\Models\Service::where('category', 'addon')->count();
                        @endphp
                        @if($addonsCount > 0)
                            <span class="badge bg-success rounded-pill ms-auto">{{ $addonsCount }}</span>
                        @endif
                    </a>
                </li>
            </ul>
        </li>

        {{-- Promotions --}}
        <li class="nav-item">
            <a href="{{ route('branch.promotions.index') }}"
               class="nav-link {{ request()->routeIs('branch.promotions.*') ? 'active' : '' }}">
                <i class="bi bi-megaphone text-warning"></i>
                <span class="menu-text">Promotions</span>
                @php
                    $activePromotions = \App\Models\Promotion::where('is_active', true)->count();
                @endphp
                @if($activePromotions > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $activePromotions }}</span>
                @endif
            </a>
        </li>

        {{-- Unclaimed --}}
        <li class="nav-item">
            <a href="{{ route('branch.unclaimed.index') }}"
               class="nav-link {{ request()->routeIs('branch.unclaimed.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-octagon text-warning"></i>
                <span class="menu-text">Unclaimed</span>
                @php
                    $unclaimedCount = \App\Models\Laundry::where('status', 'ready_for_pickup')->count();
                @endphp
                @if($unclaimedCount > 0)
                    <span class="badge bg-danger rounded-pill ms-auto">{{ $unclaimedCount }}</span>
                @endif
            </a>
        </li>

        {{-- Payment Verification --}}
        <li class="nav-item">
            <a href="{{ route('branch.payments.verification.index') }}"
               class="nav-link {{ request()->routeIs('branch.payments.verification.*') ? 'active' : '' }}">
                <i class="bi bi-credit-card-2-front text-success"></i>
                <span class="menu-text">Payment Verification</span>
                @php
                    $pendingPayments = \App\Models\PaymentProof::where('status', 'pending')
                        ->whereHas('laundry', function($q) {
                            $q->where('branch_id', auth()->user()->branch_id ?? 0);
                        })->count();
                @endphp
                @if($pendingPayments > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $pendingPayments }}</span>
                @endif
            </a>
        </li>

        {{-- Branch Management Section --}}
        <li class="nav-divider"><hr class="border-white opacity-10 mx-3"></li>
        <li class="nav-label">Branch Management</li>

        {{-- Retail Sales --}}
        <li class="nav-item">
            <a href="{{ route('branch.retail.index') }}"
               class="nav-link {{ request()->routeIs('branch.retail.*') ? 'active' : '' }}">
                <i class="bi bi-shop text-info"></i>
                <span class="menu-text">Retail Sales</span>
            </a>
        </li>

        {{-- Staff & Payroll --}}
        <li class="nav-item">
            <a href="{{ route('branch.staff.index') }}"
               class="nav-link {{ request()->routeIs('branch.staff.*') ? 'active' : '' }}">
                <i class="bi bi-people-fill text-primary"></i>
                <span class="menu-text">Staff & Payroll</span>
                @php
                    $staffCount = \App\Models\User::where('role', 'staff')
                        ->where('branch_id', auth()->user()->branch_id ?? 0)
                        ->where('is_active', true)
                        ->count();
                @endphp
                @if($staffCount > 0)
                    <span class="badge bg-primary rounded-pill ms-auto">{{ $staffCount }}</span>
                @endif
            </a>
        </li>

        {{-- Inventory --}}
        <li class="nav-item">
            <a href="{{ route('branch.inventory.index') }}"
               class="nav-link {{ request()->routeIs('branch.inventory.*') ? 'active' : '' }}">
                <i class="bi bi-box-seam text-warning"></i>
                <span class="menu-text">Inventory</span>
                @php
                    $lowStock = \App\Models\BranchStock::where('branch_id', auth()->user()->branch_id ?? 0)
                        ->whereColumn('current_stock', '<=', 'reorder_point')
                        ->count();
                @endphp
                @if($lowStock > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $lowStock }}</span>
                @endif
            </a>
        </li>

        {{-- Finance --}}
        <li class="nav-item">
            <a href="{{ route('branch.finance.index') }}"
               class="nav-link {{ request()->routeIs('branch.finance.*') ? 'active' : '' }}">
                <i class="bi bi-cash-stack text-success"></i>
                <span class="menu-text">Finance</span>
            </a>
        </li>

        {{-- Attendance --}}
        <li class="nav-item">
            <a href="{{ route('branch.attendance.index') }}"
               class="nav-link {{ request()->routeIs('branch.attendance.*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check text-info"></i>
                <span class="menu-text">Attendance</span>
            </a>
        </li>

        {{-- Analytics --}}
        <li class="nav-item">
            <a href="{{ route('branch.analytics.index') }}"
               class="nav-link {{ request()->routeIs('branch.analytics.*') ? 'active' : '' }}">
                <i class="bi bi-graph-up text-primary"></i>
                <span class="menu-text">Analytics</span>
            </a>
        </li>

        {{-- Stock Adjustments --}}
        <li class="nav-item">
            <a href="{{ route('branch.adjustments.index') }}"
               class="nav-link {{ request()->routeIs('branch.adjustments.*') ? 'active' : '' }}">
                <i class="bi bi-exclamation-triangle text-danger"></i>
                <span class="menu-text">Stock Adjustments</span>
                @php
                    $pendingAdjustments = \App\Models\InventoryAdjustment::where('branch_id', auth()->user()->branch_id ?? 0)
                        ->where('status', 'pending')
                        ->count();
                @endphp
                @if($pendingAdjustments > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $pendingAdjustments }}</span>
                @endif
            </a>
        </li>

        <li class="nav-divider"><hr class="border-white opacity-10 mx-3"></li>

        {{-- Profile Section --}}
        <li class="nav-label">Account</li>

        {{-- My Payroll --}}
        <li class="nav-item">
            <a href="{{ route('branch.payroll.index') }}"
               class="nav-link {{ request()->routeIs('branch.payroll.*') ? 'active' : '' }}">
                <i class="bi bi-wallet2 text-success"></i>
                <span class="menu-text">Branch Payroll</span>
                @php
                    $pendingPayroll = \App\Models\PayrollItem::where('branch_id', auth()->user()->id)
                        ->where('status', 'approved')
                        ->count();
                @endphp
                @if($pendingPayroll > 0)
                    <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $pendingPayroll }}</span>
                @endif
            </a>
        </li>

        {{-- Profile --}}
        <li class="nav-item">
            <a href="{{ route('branch.profile') }}"
               class="nav-link {{ request()->routeIs('branch.profile') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span class="menu-text">Profile</span>
            </a>
        </li>

    </ul>
</aside>

