@extends('staff.layouts.staff')

@section('title', 'My Profile')

@section('content')
<div class="container-fluid px-4 py-4 profile-page">

    <!-- Profile Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="profile-header-card card border-0">
                <div class="card-body text-white p-5 position-relative">
                    <div class="deco-circle deco-circle-1"></div>
                    <div class="deco-circle deco-circle-2"></div>

                    <div class="row align-items-center position-relative">
                        <div class="col-auto">
                            <div class="avatar-circle d-flex align-items-center justify-content-center">
                                <i class="bi bi-person-fill text-white" style="font-size: 3rem;"></i>
                            </div>
                        </div>
                        <div class="col">
                            <h2 class="mb-2 fw-bold header-name">{{ $user->name ?? 'Unknown User' }}</h2>
                            <p class="mb-2 opacity-90" style="font-size: 1.1rem;">
                                <i class="bi bi-briefcase-fill me-2"></i>{{ ucfirst($user->role ?? 'staff') }} Member
                            </p>
                            <div class="d-flex align-items-center" style="font-size: 1rem;">
                                <i class="bi bi-geo-alt-fill me-2"></i>
                                <span>{{ $user->branch ? $user->branch->name . ' Branch' : 'No Branch Assigned' }}</span>
                            </div>
                        </div>
                        <div class="col-auto text-end">
                            <div class="active-badge">
                                <i class="bi bi-check-circle-fill me-2"></i>Active
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">

        <!-- Profile Information -->
        <div class="col-lg-4 mb-4">
            <div class="card profile-card h-100">
                <div class="card-header profile-card-header header-blue">
                    <h5 class="fw-bold mb-0 d-flex align-items-center card-title-text title-blue">
                        <div class="icon-wrapper icon-blue me-3">
                            <i class="bi bi-info-circle"></i>
                        </div>
                        Profile Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="info-item mb-4 pb-3">
                        <label class="info-label">Full Name</label>
                        <p class="info-value mb-0">{{ $user->name }}</p>
                    </div>

                    <div class="info-item mb-4 pb-3">
                        <label class="info-label">Email Address</label>
                        <p class="info-value mb-0">{{ $user->email }}</p>
                    </div>

                    <div class="info-item mb-4 pb-3">
                        <label class="info-label">Phone Number</label>
                        <p class="info-value mb-0">{{ $user->phone ?: 'Not provided' }}</p>
                    </div>

                    <div class="info-item mb-4 pb-3">
                        <label class="info-label">Role</label>
                        <span class="badge bg-primary rounded-pill px-3 py-2" style="font-size: 0.85rem;">{{ ucfirst($user->role) }}</span>
                    </div>

                    @if($user->branch)
                    <div class="info-item mb-4 pb-3">
                        <label class="info-label">Branch</label>
                        <p class="info-value mb-1">{{ $user->branch->name }}</p>
                        <small class="info-small">
                            <i class="bi bi-geo-alt me-1"></i>{{ $user->branch->address }}
                        </small>
                    </div>

                    <div class="info-item mb-4 pb-3">
                        <label class="info-label">Branch Contact</label>
                        <p class="info-value mb-0">{{ $user->branch->phone ?: 'Not available' }}</p>
                    </div>

                    <div class="info-item mb-4 pb-3">
                        <label class="info-label">Operating Hours</label>
                        <p class="mb-1 fw-semibold">
                            <span class="{{ $user->branch && $user->branch->isOpen() ? 'text-success' : 'text-danger' }}">
                                @if($user->branch && $user->branch->isOpen())
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Open Now
                                @else
                                    <i class="bi bi-circle-fill me-1" style="font-size: 0.5rem;"></i>Closed
                                @endif
                            </span>
                        </p>
                        <small class="info-small">{{ $user->branch ? $user->branch->getTodayHoursFormatted() : 'N/A' }}</small>
                    </div>
                    @endif

                    <div class="info-item">
                        <label class="info-label">Member Since</label>
                        <p class="info-value mb-0">{{ $user->created_at->format('F j, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Update Personal Info -->
        <div class="col-lg-4 mb-4">
            <div class="card profile-card h-100">
                <div class="card-header profile-card-header header-green">
                    <h5 class="fw-bold mb-0 d-flex align-items-center card-title-text title-green">
                        <div class="icon-wrapper icon-green me-3">
                            <i class="bi bi-pencil-square"></i>
                        </div>
                        Update Information
                    </h5>
                </div>
                <div class="card-body p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show rounded-3" role="alert">
                            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('staff.profile.update') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label class="form-label field-label">Full Name</label>
                            <input type="text" class="form-control profile-input profile-input-readonly" value="{{ $user->name }}" readonly>
                            <small class="field-hint"><i class="bi bi-info-circle me-1"></i>Contact admin to change your name</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label field-label">Email Address</label>
                            <input type="email" class="form-control profile-input profile-input-readonly" value="{{ $user->email }}" readonly>
                            <small class="field-hint"><i class="bi bi-info-circle me-1"></i>Contact admin to change your email</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label field-label">Phone Number</label>
                            <div class="input-group profile-input-group">
                                <span class="input-group-text profile-input-addon"><i class="bi bi-telephone"></i></span>
                                <input type="text" name="phone" class="form-control profile-input"
                                       value="{{ $user->phone }}"
                                       placeholder="Enter your phone number">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 profile-btn">
                            <i class="bi bi-check-lg me-2"></i>Update Phone Number
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Change Password -->
        <div class="col-lg-4 mb-4">
            <div class="card profile-card h-100">
                <div class="card-header profile-card-header header-amber">
                    <h5 class="fw-bold mb-0 d-flex align-items-center card-title-text title-amber">
                        <div class="icon-wrapper icon-amber me-3">
                            <i class="bi bi-shield-lock"></i>
                        </div>
                        Security Settings
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('staff.profile.password') }}" method="POST">
                        @csrf
                        {{-- Hidden username field for password manager accessibility --}}
                        <input type="text" name="username" value="{{ $user->email }}" autocomplete="username" style="display:none;" aria-hidden="true">
                        <div class="mb-4">
                            <label class="form-label field-label">Current Password</label>
                            <div class="input-group profile-input-group">
                                <span class="input-group-text profile-input-addon"><i class="bi bi-lock"></i></span>
                                <input type="password" name="current_password" class="form-control profile-input" autocomplete="current-password" required>
                            </div>
                        </div>

                        <hr class="profile-divider my-4">

                        <div class="mb-4">
                            <label class="form-label field-label">New Password</label>
                            <div class="input-group profile-input-group">
                                <span class="input-group-text profile-input-addon"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" class="form-control profile-input" autocomplete="new-password" required>
                            </div>
                            <small class="field-hint"><i class="bi bi-info-circle me-1"></i>Minimum 8 characters</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label field-label">Confirm New Password</label>
                            <div class="input-group profile-input-group">
                                <span class="input-group-text profile-input-addon"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="password_confirmation" class="form-control profile-input" autocomplete="new-password" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-warning w-100 profile-btn text-white">
                            <i class="bi bi-shield-check me-2"></i>Update Password
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>

<style>
/* =========================================
   PROFILE PAGE — CLEAN LIGHT/DARK MODE FIX
   ========================================= */

/* ---------- Header Card ---------- */
.profile-header-card {
    background: linear-gradient(135deg, #2D2B5F 0%, #667eea 100%);
    border-radius: 24px;
    overflow: hidden;
}

.deco-circle {
    position: absolute;
    border-radius: 50%;
    filter: blur(40px);
    pointer-events: none;
}
.deco-circle-1 {
    top: -50px; right: -50px;
    width: 200px; height: 200px;
    background: rgba(255,255,255,0.1);
}
.deco-circle-2 {
    bottom: -30px; left: -30px;
    width: 150px; height: 150px;
    background: rgba(255,255,255,0.08);
    filter: blur(30px);
}

.header-name {
    font-size: 2rem;
    letter-spacing: -0.5px;
    color: #ffffff !important;
}

.avatar-circle {
    width: 100px;
    height: 100px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(10px);
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: 0 8px 32px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}
.avatar-circle:hover { transform: scale(1.05); }

.active-badge {
    display: inline-block;
    padding: 0.6rem 1.2rem;
    border-radius: 999px;
    background: rgba(16, 185, 129, 0.2);
    border: 2px solid rgba(16, 185, 129, 0.4);
    backdrop-filter: blur(10px);
    font-size: 1rem;
    color: #ffffff;
    font-weight: 600;
}

/* ---------- Cards ---------- */
.profile-card {
    border-radius: 20px !important;
    border: 1px solid #e5e7eb !important;
    background: #ffffff !important;
    box-shadow: 0 4px 12px rgba(0,0,0,0.07) !important;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: fadeInUp 0.5s ease-out both;
}
.profile-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.12) !important;
}
.col-lg-4:nth-child(1) .profile-card { animation-delay: 0.1s; }
.col-lg-4:nth-child(2) .profile-card { animation-delay: 0.2s; }
.col-lg-4:nth-child(3) .profile-card { animation-delay: 0.3s; }

/* Card headers */
.profile-card-header {
    border-radius: 20px 20px 0 0 !important;
    border-bottom: 1px solid #e5e7eb !important;
    padding: 1.25rem 1.5rem !important;
}
.header-blue  { background: rgba(59,  130, 246, 0.08) !important; }
.header-green { background: rgba(16,  185, 129, 0.08) !important; }
.header-amber { background: rgba(245, 158,  11, 0.08) !important; }

.card-title-text { font-size: 1rem; }
.title-blue  { color: #2563EB !important; }
.title-green { color: #059669 !important; }
.title-amber { color: #D97706 !important; }

.icon-wrapper {
    width: 40px; height: 40px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
}
.icon-blue  { background: rgba(59,  130, 246, 0.15); color: #2563EB; }
.icon-green { background: rgba(16,  185, 129, 0.15); color: #059669; }
.icon-amber { background: rgba(245, 158,  11, 0.15); color: #D97706; }

/* ---------- Info Items ---------- */
.info-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #6b7280;          /* visible grey in light mode */
    margin-bottom: 0.3rem;
}
.info-value {
    font-size: 1.0rem;
    font-weight: 600;
    color: #111827;          /* near-black in light mode */
}
.info-small {
    font-size: 0.82rem;
    color: #6b7280;
}

.info-item {
    border-bottom: 1px solid #e5e7eb;
    transition: padding-left 0.2s ease, background 0.2s ease;
    border-radius: 0 0 0 0;
    padding-bottom: 0.75rem;
}
.info-item:hover {
    padding-left: 8px;
    background: rgba(59,130,246,0.03);
    border-radius: 8px;
}
.info-item:last-child {
    border-bottom: none;
    padding-bottom: 0;
}

/* ---------- Form Controls ---------- */
.field-label {
    font-size: 0.95rem;
    font-weight: 600;
    color: #111827;          /* always visible in light mode */
    margin-bottom: 0.5rem;
}
.field-hint {
    display: block;
    margin-top: 0.4rem;
    font-size: 0.8rem;
    color: #6b7280;
}

.profile-input {
    border: 2px solid #d1d5db;
    border-radius: 12px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    background: #ffffff;
    color: #111827;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.profile-input:focus {
    border-color: #2D2B5F !important;
    box-shadow: 0 0 0 4px rgba(45,43,95,0.1);
    background: #ffffff;
    color: #111827;
    outline: none;
}
.profile-input-readonly {
    background: #f3f4f6 !important;
    color: #6b7280 !important;
    cursor: not-allowed;
    border-color: #e5e7eb !important;
}

.profile-input-group {
    border-radius: 12px;
    overflow: hidden;
}
.profile-input-group .profile-input {
    border-radius: 0 12px 12px 0;
    border-left: none;
}
.profile-input-addon {
    background: #f3f4f6;
    border: 2px solid #d1d5db;
    border-right: none;
    border-radius: 12px 0 0 12px;
    color: #374151;
    padding: 0 1rem;
}

.profile-btn {
    border-radius: 12px;
    padding: 0.875rem;
    font-weight: 600;
    font-size: 1rem;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.profile-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.15);
}

.profile-divider {
    border-color: #e5e7eb;
    opacity: 0.6;
}

/* ---------- Animations ---------- */
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* =========================================
   DARK MODE OVERRIDES
   ========================================= */
[data-theme="dark"] .profile-card {
    background: #1e293b !important;
    border-color: #334155 !important;
    box-shadow: 0 4px 20px rgba(0,0,0,0.4) !important;
}
[data-theme="dark"] .profile-card:hover {
    box-shadow: 0 20px 40px rgba(0,0,0,0.6) !important;
}

[data-theme="dark"] .profile-card-header {
    border-bottom-color: #334155 !important;
}
[data-theme="dark"] .header-blue  { background: rgba(59,  130, 246, 0.12) !important; }
[data-theme="dark"] .header-green { background: rgba(16,  185, 129, 0.12) !important; }
[data-theme="dark"] .header-amber { background: rgba(245, 158,  11, 0.12) !important; }

[data-theme="dark"] .title-blue  { color: #60a5fa !important; }
[data-theme="dark"] .title-green { color: #34d399 !important; }
[data-theme="dark"] .title-amber { color: #fbbf24 !important; }

[data-theme="dark"] .icon-blue  { background: rgba(96, 165, 250, 0.15); color: #60a5fa; }
[data-theme="dark"] .icon-green { background: rgba(52, 211, 153, 0.15); color: #34d399; }
[data-theme="dark"] .icon-amber { background: rgba(251,191,  36, 0.15); color: #fbbf24; }

[data-theme="dark"] .info-label   { color: #94a3b8; }
[data-theme="dark"] .info-value   { color: #f1f5f9; }
[data-theme="dark"] .info-small   { color: #94a3b8; }

[data-theme="dark"] .info-item    { border-bottom-color: #334155; }
[data-theme="dark"] .info-item:hover { background: rgba(255,255,255,0.04); }

[data-theme="dark"] .field-label  { color: #f1f5f9; }
[data-theme="dark"] .field-hint   { color: #94a3b8; }

[data-theme="dark"] .profile-input {
    background: #0f172a;
    border-color: #334155;
    color: #f1f5f9;
}
[data-theme="dark"] .profile-input:focus {
    border-color: #6366f1 !important;
    box-shadow: 0 0 0 4px rgba(99,102,241,0.2);
    background: #0f172a;
    color: #f1f5f9;
}
[data-theme="dark"] .profile-input-readonly {
    background: #1e293b !important;
    color: #64748b !important;
    border-color: #1e293b !important;
}
[data-theme="dark"] .profile-input-addon {
    background: #334155;
    border-color: #334155;
    color: #cbd5e1;
}
[data-theme="dark"] .profile-divider { border-color: #334155; }
</style>
@endsection
