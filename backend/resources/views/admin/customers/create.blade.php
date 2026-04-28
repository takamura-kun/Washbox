@extends('admin.layouts.app')
@section('title', 'Customers')
@section('page-title', 'Register New Customer')
@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/customers.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        /* Modern Professional Styling */
        .register-customer-page {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 2rem 0;
        }
        
        .page-header {
            margin-bottom: 2rem;
        }
        
        .back-button {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: transparent;
            border: 1.5px solid #e5e7eb;
            color: #6b7280;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        
        .back-button:hover {
            background: #f9fafb;
            border-color: #3D3B6B;
            color: #3D3B6B;
            transform: translateX(-2px);
        }
        
        [data-theme="dark"] .back-button {
            border-color: #374151;
            color: #9ca3af;
        }
        
        [data-theme="dark"] .back-button:hover {
            background: #1f2937;
            border-color: #6366f1;
            color: #818cf8;
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: #111827;
            letter-spacing: -0.025em;
            margin: 0;
        }
        
        [data-theme="dark"] .page-title {
            color: #f9fafb;
        }
        
        .page-subtitle {
            font-size: 0.875rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        [data-theme="dark"] .page-subtitle {
            color: #9ca3af;
        }
        
        /* Card Styling */
        .register-card {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            transition: box-shadow 0.2s ease;
        }
        
        .register-card:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        }
        
        [data-theme="dark"] .register-card {
            background: #1e293b;
            border-color: #334155;
        }
        
        .card-header-custom {
            background: linear-gradient(135deg, #3D3B6B 0%, #2d2b5f 100%);
            padding: 2rem;
            border-bottom: none;
        }
        
        .card-header-icon {
            width: 56px;
            height: 56px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .card-header-icon i {
            font-size: 1.75rem;
            color: #ffffff;
        }
        
        .card-header-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            letter-spacing: -0.02em;
        }
        
        .card-header-desc {
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0.5rem 0 0;
        }
        
        .card-body-custom {
            padding: 2rem;
        }
        
        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #f3f4f6;
        }
        
        [data-theme="dark"] .section-header {
            border-bottom-color: #374151;
        }
        
        .section-icon {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3D3B6B 0%, #5b59a8 100%);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .section-icon i {
            font-size: 1rem;
            color: #ffffff;
        }
        
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #111827;
            margin: 0;
            letter-spacing: -0.01em;
        }
        
        [data-theme="dark"] .section-title {
            color: #f9fafb;
        }
        
        /* Form Controls */
        .form-group-custom {
            margin-bottom: 1.5rem;
        }
        
        .form-label-custom {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            letter-spacing: -0.01em;
        }
        
        [data-theme="dark"] .form-label-custom {
            color: #e5e7eb;
        }
        
        .form-label-custom .required {
            color: #ef4444;
            margin-left: 2px;
        }
        
        .form-control-custom {
            width: 100%;
            padding: 0.75rem 1rem;
            font-size: 0.9375rem;
            font-weight: 500;
            color: #111827;
            background: #ffffff;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            transition: all 0.2s ease;
            outline: none;
        }
        
        .form-control-custom:focus {
            border-color: #3D3B6B;
            box-shadow: 0 0 0 4px rgba(61, 59, 107, 0.1);
        }
        
        .form-control-custom::placeholder {
            color: #9ca3af;
        }
        
        [data-theme="dark"] .form-control-custom {
            background: #334155;
            border-color: #475569;
            color: #f1f5f9;
        }
        
        [data-theme="dark"] .form-control-custom:focus {
            background: #334155;
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
        }
        
        .form-select-custom {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 1rem center;
            padding-right: 3rem;
        }
        
        .form-hint {
            font-size: 0.8125rem;
            color: #6b7280;
            margin-top: 0.375rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }
        
        [data-theme="dark"] .form-hint {
            color: #9ca3af;
        }
        
        .form-hint i {
            font-size: 0.75rem;
        }
        
        /* Action Buttons */
        .action-footer {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            flex-wrap: wrap;
        }
        
        [data-theme="dark"] .action-footer {
            border-top-color: #374151;
        }
        
        .btn-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            font-size: 0.9375rem;
            font-weight: 600;
            border-radius: 10px;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            letter-spacing: -0.01em;
        }
        
        .btn-cancel {
            background: #f3f4f6;
            color: #6b7280;
            border: 1.5px solid #e5e7eb;
        }
        
        .btn-cancel:hover {
            background: #e5e7eb;
            color: #374151;
            transform: translateY(-1px);
        }
        
        [data-theme="dark"] .btn-cancel {
            background: #374151;
            color: #d1d5db;
            border-color: #4b5563;
        }
        
        [data-theme="dark"] .btn-cancel:hover {
            background: #4b5563;
            color: #f3f4f6;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #3D3B6B 0%, #2d2b5f 100%);
            color: #ffffff;
            box-shadow: 0 4px 12px rgba(61, 59, 107, 0.3);
        }
        
        .btn-primary-custom:hover {
            background: linear-gradient(135deg, #2d2b5f 0%, #1d1b4f 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(61, 59, 107, 0.4);
            color: #ffffff;
        }
        
        .btn-primary-custom i {
            font-size: 1.125rem;
        }
        
        /* Info Box */
        .info-box {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-top: 1.5rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        
        [data-theme="dark"] .info-box {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.1) 0%, rgba(37, 99, 235, 0.1) 100%);
            border-color: rgba(59, 130, 246, 0.2);
        }
        
        .info-box-icon {
            width: 32px;
            height: 32px;
            background: #3b82f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        
        .info-box-icon i {
            font-size: 0.875rem;
            color: #ffffff;
        }
        
        .info-box-content {
            flex: 1;
        }
        
        .info-box-title {
            font-size: 0.875rem;
            font-weight: 600;
            color: #1e40af;
            margin: 0 0 0.25rem;
        }
        
        [data-theme="dark"] .info-box-title {
            color: #93c5fd;
        }
        
        .info-box-text {
            font-size: 0.8125rem;
            color: #1e40af;
            margin: 0;
            line-height: 1.5;
        }
        
        [data-theme="dark"] .info-box-text {
            color: #bfdbfe;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .register-customer-page {
                padding: 1rem 0;
            }
            
            .card-header-custom,
            .card-body-custom {
                padding: 1.5rem;
            }
            
            .action-footer {
                flex-direction: column;
            }
            
            .btn-custom {
                width: 100%;
            }
        }
    </style>
@endpush
@section('content')
    <div class="container-fluid px-4 register-customer-page">
        <div class="page-header">
            <div class="d-flex align-items-center gap-3 mb-3">
                <a href="{{ route('admin.customers.index') }}" class="back-button">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="page-title">Register New Customer</h1>
                    <p class="page-subtitle">Add a new customer to your laundry management system</p>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9 col-xl-8">
                <div class="register-card">
                    <div class="card-header-custom">
                        <div class="card-header-icon">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <h2 class="card-header-title">Customer Information</h2>
                        <p class="card-header-desc">Please fill in the customer details below to create a new account</p>
                    </div>
                    
                    <div class="card-body-custom">
                        <form action="{{ route('admin.customers.store') }}" method="POST">
                            @csrf
                            
                            {{-- Basic Information Section --}}
                            <div class="section-header">
                                <div class="section-icon">
                                    <i class="bi bi-person-badge"></i>
                                </div>
                                <h3 class="section-title">Basic Information</h3>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            Full Name <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               name="name" 
                                               class="form-control-custom" 
                                               placeholder="Juan Dela Cruz"
                                               required>
                                        <div class="form-hint">
                                            <i class="bi bi-info-circle"></i>
                                            <span>Enter customer's complete name</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            Phone Number <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               name="phone" 
                                               class="form-control-custom" 
                                               placeholder="09171234567"
                                               maxlength="11" 
                                               required>
                                        <div class="form-hint">
                                            <i class="bi bi-telephone"></i>
                                            <span>11-digit mobile number</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            Email Address <span style="color: #9ca3af; font-weight: 400;">(Optional)</span>
                                        </label>
                                        <input type="email" 
                                               name="email" 
                                               class="form-control-custom"
                                               placeholder="customer@example.com">
                                        <div class="form-hint">
                                            <i class="bi bi-envelope"></i>
                                            <span>For sending receipts and notifications</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Service Details Section --}}
                            <div class="section-header" style="margin-top: 2rem;">
                                <div class="section-icon">
                                    <i class="bi bi-geo-alt"></i>
                                </div>
                                <h3 class="section-title">Service Details</h3>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            Preferred Branch
                                        </label>
                                        <select name="preferred_branch_id" class="form-control-custom form-select-custom">
                                            <option value="">Select a branch</option>
                                            @foreach($branches as $branch)
                                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="form-hint">
                                            <i class="bi bi-building"></i>
                                            <span>Customer's preferred service location</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <div class="form-group-custom">
                                        <label class="form-label-custom">
                                            Home Address
                                        </label>
                                        <textarea name="address" 
                                                  class="form-control-custom" 
                                                  rows="3"
                                                  placeholder="House No., Street, Barangay, City, Province"
                                                  style="resize: vertical; min-height: 90px;"></textarea>
                                        <div class="form-hint">
                                            <i class="bi bi-house"></i>
                                            <span>For pickup and delivery services</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Info Box --}}
                            <div class="info-box">
                                <div class="info-box-icon">
                                    <i class="bi bi-lightbulb-fill"></i>
                                </div>
                                <div class="info-box-content">
                                    <h4 class="info-box-title">Quick Tip</h4>
                                    <p class="info-box-text">
                                        Make sure the phone number is correct as it will be used for SMS notifications and customer verification.
                                    </p>
                                </div>
                            </div>

                            {{-- Action Footer --}}
                            <div class="action-footer">
                                <a href="{{ route('admin.customers.index') }}" class="btn-custom btn-cancel">
                                    <i class="bi bi-x-circle"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn-custom btn-primary-custom">
                                    <i class="bi bi-check-circle-fill"></i>
                                    Register Customer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
