<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="WashBox Admin Dashboard">
    <title>@yield('title', 'Dashboard') - WashBox Admin</title>

    <!-- Bootstrap CSS -->
    <link href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('assets/bootstrap-icons/bootstrap-icons.css') }}">
    <!-- Unified Layout CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/layout.css') }}">
    <!-- Admin Dashboard CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/admin.css') }}">
    <!-- Compact Dashboard CSS -->

    <!-- Responsive Design CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/responsive.css') }}">
    <!-- Skip Links Fix -->
    <style>
        /* Global Table Fixes */
        .table thead th {
            background-color: #f8f9fa !important;
            color: #212529 !important;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6 !important;
            padding: 12px !important;
            vertical-align: middle;
        }
        
        .table tbody td {
            background-color: #ffffff !important;
            color: #212529 !important;
            border-color: #dee2e6 !important;
            padding: 12px !important;
            vertical-align: middle;
        }
        
        .table tbody tr:hover td {
            background-color: #f8f9fa !important;
        }
        
        /* Dark Mode Table Styles */
        [data-theme="dark"] .table thead th {
            background-color: #2d3748 !important;
            color: #e2e8f0 !important;
            border-bottom-color: #4a5568 !important;
        }
        
        [data-theme="dark"] .table tbody td {
            background-color: #1a202c !important;
            color: #e2e8f0 !important;
            border-color: #4a5568 !important;
        }
        
        [data-theme="dark"] .table tbody tr:hover td {
            background-color: #2d3748 !important;
        }
        
        /* Card Styles */
        .card {
            background-color: #ffffff !important;
            border: 1px solid #dee2e6 !important;
        }
        
        .card-body {
            background-color: #ffffff !important;
        }
        
        [data-theme="dark"] .card {
            background-color: #1a202c !important;
            border-color: #4a5568 !important;
        }
        
        [data-theme="dark"] .card-body {
            background-color: #1a202c !important;
        }
        
        /* Dashboard Controls Styles */
        .dashboard-controls {
            border-right: 1px solid rgba(0,0,0,0.1);
            padding-right: 1rem;
            margin-right: 0.5rem;
        }
        .live-sync-indicator .pulse-dot {
            display: inline-block;
            width: 6px;
            height: 6px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
        .bg-success-soft {
            background-color: rgba(16, 185, 129, 0.1) !important;
        }
        @media (max-width: 991px) {
            .dashboard-controls {
                display: none !important;
            }
        }
    </style>

    @stack('styles')
</head>

<body data-theme="light" data-role="admin" class="light-mode">
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay" aria-hidden="true"></div>

    <!-- Sidebar -->
    <x-sidebar role="{{ auth()->user()->role ?? 'admin' }}" />

    <!-- Main Content -->
    <div class="main-content" id="mainContent">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <!-- Mobile Menu Toggle -->
                <button class="mobile-menu-btn" id="mobileMenuToggle" aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </button>

                <!-- Page Title with Icon -->
                <h1 class="topbar-title">
                    <i class="bi @yield('page-icon', 'bi-speedometer2')" <a href="{{ route('admin.dashboard') }}" ></i>

                    @yield('page-title', 'Dashboard')
                </h1>

                <!-- Breadcrumb Navigation -->
                <nav class="breadcrumb-nav" aria-label="breadcrumb">
                    <a href="{{ route('admin.dashboard') }}" class="breadcrumb-item"></a>
                    @hasSection('breadcrumbs')
                        <span class="breadcrumb-divider">/</span>
                        @yield('breadcrumbs')
                    @endif
                </nav>
            </div>

            <div class="topbar-right">
                <!-- Dashboard Controls (only show on dashboard page) -->
                @if(request()->routeIs('admin.dashboard'))
                    <div class="dashboard-controls d-flex gap-2 align-items-center me-3">
                        <!-- Live Sync Indicator -->

                        <!-- Date Range Filter -->
                        <form method="GET" action="{{ route('admin.dashboard') }}" class="d-flex gap-2 m-0 align-items-center" id="dashboardFilterForm">
                            @php
                                $activeRange  = request('date_range', 'today');
                                $rangeOptions = [
                                    'today'        => 'Today',
                                    'yesterday'    => 'Yesterday',
                                    'last_7_days'  => 'Last 7 Days',
                                    'this_week'    => 'This Week',
                                    'this_month'   => 'This Month',
                                ];
                            @endphp
                            <select name="date_range"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()"
                                    style="width: auto; min-width: 120px; font-size: 0.75rem;"
                                    aria-label="Date range">
                                @foreach($rangeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ $activeRange === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Branch Filter -->
                            <select name="branch_id"
                                    class="form-select form-select-sm"
                                    onchange="this.form.submit()"
                                    style="width: auto; min-width: 130px; font-size: 0.75rem;"
                                    aria-label="Branch">
                                <option value="">All Branches</option>
                                @foreach(\App\Models\Branch::orderBy('name')->get() as $branch)
                                    <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Export Dropdown -->
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-danger" type="button" data-bs-toggle="dropdown" title="Export" style="font-size: 0.75rem;">
                                    <i class="bi bi-download"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                                    <li><a class="dropdown-item py-2" href="{{ route('admin.reports.index') }}"><i class="bi bi-file-pdf me-2 text-danger"></i>Reports</a></li>
                                    <li><a class="dropdown-item py-2" href="#" onclick="exportData('excel')"><i class="bi bi-file-excel me-2 text-success"></i>Excel</a></li>
                                    <li><a class="dropdown-item py-2" href="#" onclick="exportData('csv')"><i class="bi bi-file-text me-2 text-info"></i>CSV</a></li>
                                </ul>
                            </div>

                            <!-- Refresh Button -->
                            <button onclick="refreshDashboard()" class="btn btn-sm btn-outline-secondary" id="refresh-btn" title="Refresh" type="button" style="font-size: 0.75rem;">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </form>
                    </div>
                @endif

                <!-- Theme Toggle -->
                <button class="theme-toggle" id="themeToggle" aria-label="Toggle theme">
                    <i class="bi bi-moon"></i>
                    <i class="bi bi-sun"></i>
                </button>

                <!-- Notification Bell -->
                <div class="notification-wrapper">
                    <button class="notification-bell" type="button" id="notificationDropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <span class="badge rounded-pill bg-danger notification-badge" id="notificationBadge" style="display: none;">
                            0
                        </span>
                    </button>

                    <div class="dropdown-menu dropdown-menu-end notification-dropdown rounded-3 p-0" aria-labelledby="notificationDropdown">
                        <!-- Header -->
                        <div class="notification-header">
                            <h6 class="mb-0">
                                <i class="bi bi-bell me-2 text-primary"></i>Notifications
                            </h6>
                            <div class="notification-actions">
                                <button class="btn btn-sm btn-outline-secondary" onclick="markAllNotificationsRead()">
                                    <i class="bi bi-check-all me-1"></i>Mark all read
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="clearAllNotifications()">
                                    <i class="bi bi-trash me-1"></i>Clear all
                                </button>
                            </div>
                        </div>

                        <!-- Notifications List -->
                        <div class="notification-dropdown-list" id="notificationDropdownList">
                            <!-- Loading skeleton will be inserted here -->
                        </div>

                        <!-- Footer -->
                        <div class="border-top px-3 py-2 text-center">
                            <a href="{{ route('admin.notifications.index') }}" class="text-decoration-none small fw-semibold text-primary">
                                View All Notifications <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Menu -->
                <div class="user-menu-wrapper">
                    <div class="user-menu dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar" id="userAvatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="user-info">
                            <span class="user-name">{{ auth()->user()->name ?? 'Admin' }}</span>
                            <span class="user-role">{{ ucfirst(auth()->user()->role ?? 'Administrator') }}</span>
                        </div>
                        <i class="bi bi-chevron-down"></i>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3">
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('admin.profile') }}">
                                <i class="bi bi-person"></i>Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2" href="{{ route('admin.settings') }}">
                                <i class="bi bi-gear"></i>Settings
                            </a>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form id="logoutForm" action="{{ route('admin.logout') }}" method="POST" class="mb-0">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 text-danger">
                                    <i class="bi bi-box-arrow-right"></i>Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Content Area -->
        <div class="content-area">
            <x-alert />
            @yield('content')
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- jQuery -->
    <script src="{{ asset('assets/jquery/jquery-3.7.0.min.js') }}"></script>
    <!-- Unified Layout JS -->
    <script src="{{ asset('assets/js/layout.js') }}"></script>

    <!-- Dashboard Functions -->
    <script>
        function refreshDashboard() {
            const btn = document.getElementById('refresh-btn');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i>';
            }
            window.location.reload();
        }

        function exportData(format) {
            const dateRange = document.querySelector('select[name="date_range"]')?.value || 'today';
            const branchId = document.querySelector('select[name="branch_id"]')?.value || '';

            console.log(`Exporting ${format} - Date: ${dateRange}, Branch: ${branchId}`);
            alert(`Export to ${format.toUpperCase()} - Feature coming soon!`);
        }
    </script>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        .spin {
            animation: spin 1s linear infinite;
        }
    </style>

    @vite(['resources/js/app.js'])
    @stack('scripts')

    <script>
    // ── Admin Notification Polling + Sound ──────────────────────────────────
    (function () {
        let lastCount = 0;
        const badge  = document.getElementById('notificationBadge');
        const csrf   = document.querySelector('meta[name="csrf-token"]')?.content;

        function playBeep() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain); gain.connect(ctx.destination);
                osc.type = 'sine'; osc.frequency.value = 880;
                gain.gain.setValueAtTime(0.3, ctx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.4);
                osc.start(); osc.stop(ctx.currentTime + 0.4);
            } catch(e) {}
        }

        async function pollCount() {
            try {
                const res  = await fetch('{{ route("admin.notifications.unread-count") }}', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const count = data.count ?? 0;
                if (badge) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.style.display = count > 0 ? 'flex' : 'none';
                }
                if (count > lastCount) playBeep();
                lastCount = count;
            } catch(e) {}
        }

        async function loadDropdown() {
            try {
                const res  = await fetch('{{ route("admin.notifications.recent") }}?limit=10', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (!res.ok) return;
                const data = await res.json();
                const list = document.getElementById('notificationDropdownList');
                if (!list) return;
                const items = data.notifications ?? data.data ?? [];
                if (!items.length) {
                    list.innerHTML = '<div class="text-center py-4 text-muted"><i class="bi bi-bell-slash"></i><p class="small mt-2">No notifications</p></div>';
                    return;
                }
                list.innerHTML = items.map(n => `
                    <a href="${n.link||'#'}" class="d-flex gap-3 px-3 py-2 border-bottom text-decoration-none text-dark ${!n.is_read?'bg-primary bg-opacity-10':''}"
                       onclick="fetch('{{ url('admin/notifications') }}/${n.id}/mark-read',{method:'POST',headers:{'X-CSRF-TOKEN':'${csrf}','Accept':'application/json'}})">
                        <i class="bi ${n.icon||'bi-bell'} text-${n.color||'primary'} mt-1"></i>
                        <div>
                            <div class="small fw-semibold">${n.title} ${!n.is_read?'<span class="badge bg-primary" style="font-size:.6rem">NEW</span>':''}</div>
                            <div class="small text-muted">${n.message}</div>
                            <div class="text-muted" style="font-size:.7rem">${n.created_at}</div>
                        </div>
                    </a>`).join('');
            } catch(e) {}
        }

        window.markAllNotificationsRead = async function() {
            await fetch('{{ route("admin.notifications.mark-all-read") }}', {
                method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            });
            lastCount = 0;
            if (badge) badge.style.display = 'none';
            loadDropdown();
        };

        window.clearAllNotifications = async function() {
            await fetch('{{ route("admin.notifications.delete-all-read") }}', {
                method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            });
            loadDropdown();
        };

        document.addEventListener('DOMContentLoaded', () => {
            pollCount();
            setInterval(pollCount, 30000);
            const bell = document.getElementById('notificationDropdown');
            if (bell) bell.addEventListener('show.bs.dropdown', loadDropdown);
        });
    })();
    </script>
</body>
</html>
