@extends('staff.layouts.staff')

@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-icon', 'bi-bell')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ============================================================
   NOTIFICATIONS — Refined Feed Aesthetic
   ============================================================ */

:root {
    --n-bg:           #f0f2f7;
    --n-surface:      #ffffff;
    --n-surface-2:    #f8f9fc;
    --n-border:       #e4e8f0;
    --n-border-2:     #d0d7e8;
    --n-text-1:       #0d1117;
    --n-text-2:       #4a5568;
    --n-text-3:       #8896ae;
    --n-accent:       #1a56db;
    --n-accent-soft:  rgba(26, 86, 219, 0.08);
    --n-accent-line:  rgba(26, 86, 219, 0.6);
    --n-success:      #0d9373;
    --n-danger:       #c81e1e;
    --n-danger-soft:  rgba(200, 30, 30, 0.08);
    --n-warning:      #c27803;
    --n-shadow-sm:    0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
    --n-shadow-md:    0 4px 16px rgba(0,0,0,0.08), 0 2px 6px rgba(0,0,0,0.04);
    --n-radius:       16px;
    --n-radius-sm:    10px;
    --n-font:         'Sora', sans-serif;
    --n-mono:         'JetBrains Mono', monospace;
    --n-unread-bg:    rgba(26, 86, 219, 0.03);
}

[data-theme="dark"] {
    --n-bg:           #080c14;
    --n-surface:      #0f1623;
    --n-surface-2:    #141d2e;
    --n-border:       #1e2d45;
    --n-border-2:     #243450;
    --n-text-1:       #e8edf5;
    --n-text-2:       #8896ae;
    --n-text-3:       #4a5a72;
    --n-accent:       #4d7cfe;
    --n-accent-soft:  rgba(77, 124, 254, 0.1);
    --n-accent-line:  rgba(77, 124, 254, 0.7);
    --n-success:      #10b981;
    --n-danger:       #f05252;
    --n-danger-soft:  rgba(240, 82, 82, 0.1);
    --n-warning:      #f59e0b;
    --n-shadow-sm:    0 1px 3px rgba(0,0,0,0.3);
    --n-shadow-md:    0 4px 16px rgba(0,0,0,0.4);
    --n-unread-bg:    rgba(77, 124, 254, 0.05);
}

/* ---- Base ---- */
.notif-page {
    font-family: var(--n-font);
    background: var(--n-bg);
    min-height: 100vh;
    padding: 2rem 1.5rem 3rem;
}

/* ---- Page Header ---- */
.notif-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1.75rem;
    flex-wrap: wrap;
}

.notif-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.7rem;
    font-weight: 600;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    color: var(--n-accent);
    background: var(--n-accent-soft);
    border: 1px solid rgba(26,86,219,0.15);
    padding: 0.28rem 0.7rem;
    border-radius: 999px;
    margin-bottom: 0.6rem;
}
[data-theme="dark"] .notif-eyebrow {
    border-color: rgba(77,124,254,0.2);
}

.notif-title {
    font-size: 1.75rem;
    font-weight: 700;
    color: var(--n-text-1);
    letter-spacing: -0.03em;
    line-height: 1.2;
    margin: 0 0 0.3rem;
}

.notif-subtitle {
    font-size: 0.85rem;
    color: var(--n-text-3);
    margin: 0;
    font-weight: 400;
}

/* ---- Header Actions ---- */
.notif-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    align-items: flex-start;
    padding-top: 0.25rem;
}

.notif-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.5rem 1rem;
    border-radius: var(--n-radius-sm);
    font-size: 0.8rem;
    font-weight: 600;
    font-family: var(--n-font);
    cursor: pointer;
    transition: all 0.18s ease;
    border: 1.5px solid transparent;
    text-decoration: none;
    line-height: 1;
}

.notif-btn-outline-primary {
    background: var(--n-accent-soft);
    border-color: rgba(26,86,219,0.2);
    color: var(--n-accent);
}
.notif-btn-outline-primary:hover {
    background: var(--n-accent);
    border-color: var(--n-accent);
    color: #fff;
    box-shadow: 0 4px 12px rgba(26,86,219,0.3);
    transform: translateY(-1px);
}

.notif-btn-outline-danger {
    background: var(--n-danger-soft);
    border-color: rgba(200,30,30,0.2);
    color: var(--n-danger);
}
.notif-btn-outline-danger:hover {
    background: var(--n-danger);
    border-color: var(--n-danger);
    color: #fff;
    box-shadow: 0 4px 12px rgba(200,30,30,0.3);
    transform: translateY(-1px);
}

/* ---- Alert ---- */
.notif-alert {
    display: flex;
    align-items: center;
    gap: 0.6rem;
    padding: 0.85rem 1.1rem;
    border-radius: var(--n-radius-sm);
    font-size: 0.85rem;
    font-weight: 500;
    background: rgba(13,147,115,0.08);
    border: 1px solid rgba(13,147,115,0.2);
    color: var(--n-success);
    margin-bottom: 1.25rem;
    position: relative;
}

.notif-alert .btn-close {
    position: absolute;
    right: 0.75rem;
    top: 50%;
    transform: translateY(-50%);
    opacity: 0.5;
    font-size: 0.7rem;
}

/* ---- Filter Bar ---- */
.notif-filter-card {
    background: var(--n-surface);
    border: 1px solid var(--n-border);
    border-radius: var(--n-radius);
    padding: 1rem 1.25rem;
    margin-bottom: 1.25rem;
    box-shadow: var(--n-shadow-sm);
    display: flex;
    align-items: center;
    gap: 1.25rem;
    flex-wrap: wrap;
}

.notif-filter-label {
    font-size: 0.68rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: var(--n-text-3);
    white-space: nowrap;
}

.notif-filter-pills {
    display: flex;
    gap: 0.4rem;
    flex-wrap: wrap;
    align-items: center;
}

.notif-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.38rem 0.85rem;
    border-radius: 999px;
    font-size: 0.775rem;
    font-weight: 500;
    font-family: var(--n-font);
    border: 1.5px solid var(--n-border);
    background: transparent;
    color: var(--n-text-2);
    cursor: pointer;
    text-decoration: none;
    transition: all 0.15s ease;
}
.notif-pill:hover {
    border-color: var(--n-accent);
    color: var(--n-accent);
    background: var(--n-accent-soft);
}
.notif-pill.active {
    background: var(--n-accent);
    border-color: var(--n-accent);
    color: #fff;
    box-shadow: 0 3px 10px rgba(26,86,219,0.25);
}

.notif-pill-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    border-radius: 999px;
    font-size: 0.65rem;
    font-weight: 700;
    font-family: var(--n-mono);
    padding: 0 0.3rem;
    background: rgba(255,255,255,0.25);
    color: inherit;
}
.notif-pill:not(.active) .notif-pill-count {
    background: var(--n-border);
    color: var(--n-text-3);
}
.notif-pill.active .notif-pill-count {
    background: rgba(255,255,255,0.25);
    color: #fff;
}
.notif-pill-count.danger-count {
    background: var(--n-danger);
    color: #fff;
}
.notif-pill.active .danger-count {
    background: rgba(255,255,255,0.3);
}

.notif-type-select {
    padding: 0.38rem 0.85rem;
    border-radius: 999px;
    font-size: 0.775rem;
    font-weight: 500;
    font-family: var(--n-font);
    border: 1.5px solid var(--n-border);
    background: var(--n-surface);
    color: var(--n-text-2);
    cursor: pointer;
    outline: none;
    transition: border-color 0.15s ease;
    min-width: 140px;
}
.notif-type-select:hover,
.notif-type-select:focus {
    border-color: var(--n-accent);
    color: var(--n-text-1);
}

/* ---- Feed Card ---- */
.notif-feed-card {
    background: var(--n-surface);
    border: 1px solid var(--n-border);
    border-radius: var(--n-radius);
    box-shadow: var(--n-shadow-md);
    overflow: hidden;
}

/* ---- Notification Item ---- */
.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.1rem 1.4rem;
    border-bottom: 1px solid var(--n-border);
    position: relative;
    transition: background 0.15s ease;
    cursor: default;
}

.notif-item:last-child {
    border-bottom: none;
}

.notif-item:hover {
    background: var(--n-surface-2);
}

.notif-item.unread {
    background: var(--n-unread-bg);
}
.notif-item.unread:hover {
    background: var(--n-accent-soft);
}

/* Unread left accent line */
.notif-item.unread::before {
    content: '';
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 3px;
    background: var(--n-accent-line);
    border-radius: 0 2px 2px 0;
}

/* ---- Icon ---- */
.notif-icon-wrap {
    width: 44px;
    height: 44px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 1.1rem;
    border: 1px solid transparent;
    transition: transform 0.2s ease;
}
.notif-item:hover .notif-icon-wrap {
    transform: scale(1.05);
}

/* Color variants for icon backgrounds */
.notif-icon-primary  { background: rgba(26,86,219,0.1);  border-color: rgba(26,86,219,0.15);  color: #1a56db; }
.notif-icon-success  { background: rgba(13,147,115,0.1); border-color: rgba(13,147,115,0.15); color: #0d9373; }
.notif-icon-warning  { background: rgba(194,120,3,0.1);  border-color: rgba(194,120,3,0.15);  color: #c27803; }
.notif-icon-danger   { background: rgba(200,30,30,0.1);  border-color: rgba(200,30,30,0.15);  color: #c81e1e; }
.notif-icon-info     { background: rgba(14,165,233,0.1); border-color: rgba(14,165,233,0.15); color: #0ea5e9; }
.notif-icon-secondary{ background: rgba(100,116,139,0.1);border-color: rgba(100,116,139,0.15);color: #64748b; }

[data-theme="dark"] .notif-icon-primary  { color: #60a5fa; }
[data-theme="dark"] .notif-icon-success  { color: #34d399; }
[data-theme="dark"] .notif-icon-warning  { color: #fbbf24; }
[data-theme="dark"] .notif-icon-danger   { color: #f87171; }
[data-theme="dark"] .notif-icon-info     { color: #38bdf8; }
[data-theme="dark"] .notif-icon-secondary{ color: #94a3b8; }

/* ---- Content ---- */
.notif-content {
    flex: 1;
    min-width: 0;
}

.notif-content-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 0.75rem;
}

.notif-item-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--n-text-1);
    line-height: 1.4;
    margin: 0 0 0.3rem;
}
.notif-item.unread .notif-item-title {
    color: var(--n-text-1);
}
.notif-item:not(.unread) .notif-item-title {
    color: var(--n-text-2);
}

.notif-title-link {
    text-decoration: none;
    color: inherit;
    transition: color 0.15s ease;
}
.notif-title-link:hover {
    color: var(--n-accent);
}

.notif-new-badge {
    display: inline-block;
    padding: 0.1rem 0.45rem;
    border-radius: 999px;
    font-size: 0.6rem;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    background: var(--n-accent);
    color: #fff;
    margin-left: 0.4rem;
    vertical-align: middle;
}

.notif-message {
    font-size: 0.815rem;
    color: var(--n-text-2);
    margin: 0 0 0.5rem;
    line-height: 1.5;
}

.notif-meta {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.notif-time {
    font-size: 0.72rem;
    color: var(--n-text-3);
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.notif-type-chip {
    display: inline-block;
    padding: 0.15rem 0.55rem;
    border-radius: 6px;
    font-size: 0.68rem;
    font-weight: 500;
    background: var(--n-surface-2);
    border: 1px solid var(--n-border);
    color: var(--n-text-3);
    letter-spacing: 0.02em;
}

/* ---- Kebab Menu ---- */
.notif-menu-btn {
    width: 30px;
    height: 30px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid var(--n-border);
    background: transparent;
    color: var(--n-text-3);
    cursor: pointer;
    transition: all 0.15s ease;
    font-size: 0.8rem;
    flex-shrink: 0;
    padding: 0;
    opacity: 0;
}
.notif-item:hover .notif-menu-btn {
    opacity: 1;
}
.notif-menu-btn:hover,
.notif-menu-btn.show {
    background: var(--n-surface-2);
    border-color: var(--n-border-2);
    color: var(--n-text-1);
    opacity: 1;
}

/* ---- Dropdown Menu ---- */
.notif-dropdown-menu {
    background: var(--n-surface);
    border: 1px solid var(--n-border);
    border-radius: var(--n-radius-sm);
    box-shadow: var(--n-shadow-md);
    padding: 0.35rem;
    min-width: 170px;
}

.notif-dropdown-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 7px;
    font-size: 0.8rem;
    font-weight: 500;
    color: var(--n-text-2);
    background: transparent;
    border: none;
    width: 100%;
    text-align: left;
    cursor: pointer;
    text-decoration: none;
    transition: background 0.12s ease, color 0.12s ease;
    font-family: var(--n-font);
}
.notif-dropdown-item:hover {
    background: var(--n-surface-2);
    color: var(--n-text-1);
}
.notif-dropdown-item.item-danger:hover {
    background: var(--n-danger-soft);
    color: var(--n-danger);
}

.notif-dropdown-divider {
    height: 1px;
    background: var(--n-border);
    margin: 0.3rem 0.25rem;
    border: none;
}

/* ---- Empty State ---- */
.notif-empty {
    padding: 4.5rem 2rem;
    text-align: center;
}

.notif-empty-icon {
    width: 72px; height: 72px;
    border-radius: 22px;
    background: var(--n-surface-2);
    border: 1px solid var(--n-border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--n-text-3);
    margin: 0 auto 1.25rem;
}

.notif-empty-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--n-text-2);
    margin-bottom: 0.35rem;
}

.notif-empty-sub {
    font-size: 0.825rem;
    color: var(--n-text-3);
}

/* ---- Pagination ---- */
.notif-pagination {
    padding: 1rem 1.4rem;
    border-top: 1px solid var(--n-border);
    background: var(--n-surface-2);
}

/* ---- Animations ---- */
@keyframes n-fade-up {
    from { opacity: 0; transform: translateY(14px); }
    to   { opacity: 1; transform: translateY(0); }
}

.notif-header      { animation: n-fade-up 0.38s ease both; }
.notif-filter-card { animation: n-fade-up 0.38s ease 0.07s both; }
.notif-feed-card   { animation: n-fade-up 0.38s ease 0.14s both; }

@media (max-width: 640px) {
    .notif-header { flex-direction: column; }
    .notif-title  { font-size: 1.4rem; }
    .notif-item   { padding: 1rem; }
    .notif-menu-btn { opacity: 1; }
}
</style>
@endpush

@section('content')
<div class="notif-page">

    {{-- Header --}}
    <div class="notif-header">
        <div>
            <div class="notif-eyebrow">
                <i class="bi bi-bell-fill"></i> Notifications
            </div>
            <h1 class="notif-title">All Notifications</h1>
            <p class="notif-subtitle">Stay updated with your latest activities</p>
        </div>

        <div class="notif-actions">
            @if($counts['unread'] > 0)
                <form action="{{ route('staff.notifications.mark-all-read') }}" method="POST">
                    @csrf
                    <button type="submit" class="notif-btn notif-btn-outline-primary">
                        <i class="bi bi-check-all"></i> Mark All Read
                    </button>
                </form>
            @endif
            @if($counts['read'] > 0)
                <form action="{{ route('staff.notifications.delete-read') }}" method="POST"
                      onsubmit="return confirm('Delete all read notifications?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="notif-btn notif-btn-outline-danger">
                        <i class="bi bi-trash"></i> Clear Read
                    </button>
                </form>
            @endif
        </div>
    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="notif-alert alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Filter Bar --}}
    <div class="notif-filter-card">
        <span class="notif-filter-label">Filter by</span>
        <div class="notif-filter-pills">
            <a href="{{ route('staff.notifications.index') }}"
               class="notif-pill {{ !request('status') ? 'active' : '' }}">
                All
                <span class="notif-pill-count">{{ $counts['all'] }}</span>
            </a>
            <a href="{{ route('staff.notifications.index', ['status' => 'unread']) }}"
               class="notif-pill {{ request('status') === 'unread' ? 'active' : '' }}">
                Unread
                <span class="notif-pill-count {{ !request('status') || request('status') !== 'unread' ? 'danger-count' : '' }}">{{ $counts['unread'] }}</span>
            </a>
            <a href="{{ route('staff.notifications.index', ['status' => 'read']) }}"
               class="notif-pill {{ request('status') === 'read' ? 'active' : '' }}">
                Read
                <span class="notif-pill-count">{{ $counts['read'] }}</span>
            </a>
        </div>

        @if($types->count() > 0)
            <select class="notif-type-select" onchange="window.location.href=this.value">
                <option value="{{ route('staff.notifications.index', request()->except('type')) }}">All Types</option>
                @foreach($types as $type)
                    <option value="{{ route('staff.notifications.index', array_merge(request()->all(), ['type' => $type])) }}"
                            {{ request('type') === $type ? 'selected' : '' }}>
                        {{ ucwords(str_replace('_', ' ', $type)) }}
                    </option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Notifications Feed --}}
    <div class="notif-feed-card">
        @forelse($notifications as $notification)
            @php
                $icon  = $notification->icon ? 'bi-' . $notification->icon : 'bi-bell';
                $color = $notification->color ?? 'secondary';
                $colorMap = [
                    'primary'   => 'notif-icon-primary',
                    'success'   => 'notif-icon-success',
                    'warning'   => 'notif-icon-warning',
                    'danger'    => 'notif-icon-danger',
                    'info'      => 'notif-icon-info',
                    'secondary' => 'notif-icon-secondary',
                ];
                $iconClass = $colorMap[$color] ?? 'notif-icon-secondary';
            @endphp

            <div class="notif-item {{ !$notification->is_read ? 'unread' : '' }}"
                 data-notification-id="{{ $notification->id }}">

                {{-- Icon --}}
                <div class="notif-icon-wrap {{ $iconClass }}">
                    <i class="bi {{ $icon }}"></i>
                </div>

                {{-- Content --}}
                <div class="notif-content">
                    <div class="notif-content-top">
                        <div style="flex:1; min-width:0;">
                            <p class="notif-item-title">
                                @if($notification->link)
                                    <a href="{{ $notification->link }}" class="notif-title-link">
                                        {{ $notification->title }}
                                    </a>
                                @else
                                    {{ $notification->title }}
                                @endif
                                @if(!$notification->is_read)
                                    <span class="notif-new-badge">New</span>
                                @endif
                            </p>
                            <p class="notif-message">{{ $notification->message }}</p>
                            <div class="notif-meta">
                                <span class="notif-time">
                                    <i class="bi bi-clock"></i>
                                    {{ $notification->created_at->diffForHumans() }}
                                </span>
                                <span class="notif-type-chip">
                                    {{ ucwords(str_replace('_', ' ', $notification->type)) }}
                                </span>
                            </div>
                        </div>

                        {{-- Kebab Menu --}}
                        <div class="dropdown">
                            <button class="notif-menu-btn" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu notif-dropdown-menu dropdown-menu-end">
                                @if(!$notification->is_read)
                                    <li>
                                        <form action="{{ route('staff.notifications.mark-read', $notification->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="notif-dropdown-item">
                                                <i class="bi bi-check2"></i> Mark as Read
                                            </button>
                                        </form>
                                    </li>
                                @endif
                                @if($notification->link)
                                    <li>
                                        <a class="notif-dropdown-item" href="{{ $notification->link }}">
                                            <i class="bi bi-arrow-up-right-square"></i> View Details
                                        </a>
                                    </li>
                                @endif
                                <li><div class="notif-dropdown-divider"></div></li>
                                <li>
                                    <form action="{{ route('staff.notifications.destroy', $notification->id) }}" method="POST"
                                          onsubmit="return confirm('Delete this notification?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="notif-dropdown-item item-danger">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="notif-empty">
                <div class="notif-empty-icon">
                    <i class="bi bi-bell-slash"></i>
                </div>
                <div class="notif-empty-title">No notifications</div>
                <div class="notif-empty-sub">You're all caught up! Check back later.</div>
            </div>
        @endforelse

        @if($notifications->hasPages())
            <div class="notif-pagination">
                {{ $notifications->withQueryString()->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
