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
        {{-- Dashboard - Always first --}}
        <x-admin.nav-item route="{{ $role }}.dashboard" icon="bi-speedometer2" label="Dashboard" />

        {{-- MOST USED - Daily Operations --}}
        <li><span class="nav-label">Daily Operations</span></li>
        @if($role === 'staff')
            <x-admin.nav-item route="staff.laundries.create" icon="bi-plus-circle text-info" label="New Laundry" />
        @endif
        <x-admin.nav-item route="{{ $role }}.laundries.index" icon="bi-basket" label="Laundries" />
        <x-admin.nav-item route="{{ $role }}.pickups.index" icon="bi-truck" label="Pickups" />
        <x-admin.nav-item route="{{ $role }}.customers.index" icon="bi-people" label="Customers" />

        @if($role === 'admin')
            {{-- Payment Verification - High Priority --}}
            @php
                try {
                    $pendingPayments = \App\Models\PaymentProof::where('status', 'pending')->count();
                } catch (\Exception $e) {
                    $pendingPayments = 0;
                }
            @endphp
            <x-admin.nav-item
                route="admin.payments.verification.index"
                icon="bi-credit-card-2-front text-success"
                label="Payment Verification"
                :badge="$pendingPayments"
                badgeClass="bg-warning text-dark rounded-pill" />
        @endif

        {{-- FREQUENTLY USED - Finance & Staff --}}
        @if($role === 'admin')
            <li><span class="nav-label">Finance & Staff</span></li>

            {{-- Finance Submenu (Collapsible) --}}
            <li class="nav-item">
                <a class="nav-link finance-toggle d-flex align-items-center justify-content-between"
                   href="#financeMenu"
                   role="button"
                   style="padding: 0.75rem 1rem; color: white; text-decoration: none; cursor: pointer;">
                    <span class="d-flex align-items-center">
                        <i class="bi bi-cash-coin me-2"></i>
                        <span>Finance</span>
                    </span>
                    <i class="bi bi-chevron-down ms-auto finance-chevron" style="font-size: 0.8rem; transition: transform 0.3s ease;"></i>
                </a>
                <div class="finance-menu" id="financeMenu" style="display: none;">
                    <ul class="nav flex-column ms-3 mt-2 mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.finance.dashboard') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-graph-up me-2"></i>Financial Overview

                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.finance.sales.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-cash-stack me-2"></i>Sales
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.finance.expenses.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-wallet2 me-2"></i>Expenses
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.finance.payroll.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-people-fill me-2"></i>Payroll
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.finance.reports.profit-loss') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-graph-up-arrow me-2"></i>Profit & Loss
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.finance.retail-sales.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-cart-plus me-2"></i>Retail Sales
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <x-admin.nav-item route="admin.staff.index" icon="bi-person-badge" label="Staff" />

            {{-- Attendance - Added for easy access --}}
            <x-admin.nav-item route="admin.attendance.index" icon="bi-calendar-check" label="Attendance" />
        @endif

        {{-- MODERATELY USED - Inventory & Analytics --}}
        @if($role === 'admin')
            <li><span class="nav-label">Inventory & Analytics</span></li>

            {{-- Inventory Submenu (Collapsible) --}}
            <li class="nav-item">
                <a class="nav-link inventory-toggle d-flex align-items-center justify-content-between"
                   href="#inventoryMenu"
                   role="button"
                   style="padding: 0.75rem 1rem; color: white; text-decoration: none; cursor: pointer;">
                    <span class="d-flex align-items-center">
                        <i class="bi bi-boxes me-2"></i>
                        <span>Inventory</span>
                    </span>
                    <i class="bi bi-chevron-down ms-auto inventory-chevron" style="font-size: 0.8rem; transition: transform 0.3s ease;"></i>
                </a>
                <div class="inventory-menu" id="inventoryMenu" style="display: none;">
                    <ul class="nav flex-column ms-3 mt-2 mb-2">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.dashboard') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-speedometer2 me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-box-seam me-2"></i>All Items
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.purchases.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-cart-check me-2"></i>Purchases
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.distribute.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-arrow-left-right me-2"></i>Distribute
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.dist-log') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-clock-history me-2"></i>Distribution Log
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.branch-stock') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-shop me-2"></i>Branch Stock
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.manage') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-gear me-2"></i>Manage
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.adjustments.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-exclamation-triangle me-2"></i>Adjustments
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.inventory.movements.index') }}" style="padding: 0.5rem 0.75rem; font-size: 0.9rem;">
                                <i class="bi bi-arrow-left-right me-2"></i>Movement Report
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <x-admin.nav-item route="admin.performance-report" icon="bi-graph-up-arrow" label="Performance Report" />

            @php
                try {
                    $newRatings = \App\Models\CustomerRating::whereNull('viewed_at')->count();
                } catch (\Exception $e) {
                    $newRatings = 0;
                }
            @endphp
            <x-admin.nav-item
                route="admin.reports.index"
                icon="bi-file-earmark-bar-graph"
                label="Customer Ratings"
                :badge="$newRatings"
                badgeClass="bg-danger rounded-pill" />
        @endif

        {{-- OCCASIONALLY USED - Alerts & Management --}}
        <li><span class="nav-label">Alerts & Management</span></li>
        <x-admin.nav-item route="{{ $role }}.unclaimed.index" icon="bi-exclamation-octagon text-warning" label="Unclaimed" />

        @if($role === 'admin')
            <x-admin.nav-item route="admin.promotions.index" icon="bi-megaphone" label="Promotions" />
        @endif

        {{-- LEAST USED - Configuration & Setup --}}
        @if($role === 'admin')
            <li><span class="nav-label">Configuration</span></li>
            <x-admin.nav-item route="admin.branches.index" icon="bi-shop" label="Branches" />
            <x-admin.nav-item route="admin.service-types.index" icon="bi-grid-3x3-gap" label="Service Types" />
            <x-admin.nav-item route="admin.services.index" icon="bi-droplet" label="Services" />
        @endif

        <li><hr class="border-white opacity-10 mx-3"></li>
        {{-- Settings - Always last --}}
        <x-admin.nav-item route="{{ $role }}.settings" icon="bi-gear" label="Settings" />
    </ul>
</aside>

<style>
#sidebarFilter::placeholder {
    color: rgba(255, 255, 255, 0.5);
}

#sidebarFilter:focus {
    background: rgba(255,255,255,0.15);
    border-color: rgba(255, 92, 53, 0.5);
    color: white;
    outline: none;
    box-shadow: 0 0 0 0.2rem rgba(255, 92, 53, 0.25);
}

.nav-link {
    color: rgba(255, 255, 255, 0.8) !important;
    transition: all 0.3s ease;
}

.nav-link:hover {
    color: white !important;
    background-color: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

.nav-link.active {
    color: white !important;
    background-color: rgba(255, 92, 53, 0.3);
    border-left: 3px solid #ff5c35;
    padding-left: calc(0.75rem - 3px) !important;
}

.inventory-menu {
    animation: slideDown 0.3s ease-out;
}

.finance-menu {
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.inventory-toggle,
.finance-toggle {
    cursor: pointer !important;
}

.nav-item.hidden {
    display: none !important;
}

.nav-label.hidden {
    display: none !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const inventoryToggle = document.querySelector('.inventory-toggle');
    const inventoryMenu = document.getElementById('inventoryMenu');
    const inventoryChevron = document.querySelector('.inventory-chevron');

    if (inventoryToggle && inventoryMenu) {
        inventoryToggle.addEventListener('click', function(e) {
            e.preventDefault();

            const isVisible = inventoryMenu.style.display !== 'none';
            inventoryMenu.style.display = isVisible ? 'none' : 'block';

            if (inventoryChevron) {
                inventoryChevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    }

    const financeToggle = document.querySelector('.finance-toggle');
    const financeMenu = document.getElementById('financeMenu');
    const financeChevron = document.querySelector('.finance-chevron');

    if (financeToggle && financeMenu) {
        financeToggle.addEventListener('click', function(e) {
            e.preventDefault();

            const isVisible = financeMenu.style.display !== 'none';
            financeMenu.style.display = isVisible ? 'none' : 'block';

            if (financeChevron) {
                financeChevron.style.transform = isVisible ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    }

    // Sidebar Filter Functionality
    const filterInput = document.getElementById('sidebarFilter');
    if (filterInput) {
        filterInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            const navItems = document.querySelectorAll('.sidebar-menu .nav-item');
            const navLabels = document.querySelectorAll('.sidebar-menu .nav-label');

            navItems.forEach(item => {
                const label = item.textContent.toLowerCase();
                const shouldShow = searchTerm === '' || label.includes(searchTerm);
                item.classList.toggle('hidden', !shouldShow);
            });

            navLabels.forEach(label => {
                if (searchTerm === '') {
                    label.classList.remove('hidden');
                } else {
                    label.classList.add('hidden');
                }
            });
        });
    }
});
</script>
