@extends('admin.layouts.app')

@section('title', 'New Payroll Period')

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">New Payroll Period</h1>
        <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card shadow-sm" style="background: var(--card-bg); border-color: var(--border-color);">
        <div class="card-body">
            <form action="{{ route('admin.finance.payroll.store') }}" method="POST">
                @csrf

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label" style="color: var(--text-primary);">Period Label <span class="text-danger">*</span></label>
                        <input type="text" name="period_label" class="form-control @error('period_label') is-invalid @enderror" 
                               value="{{ old('period_label') }}" placeholder="e.g., January 1-15, 2026" required>
                        @error('period_label')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" style="color: var(--text-primary);">Branch</label>
                        <select name="branch_id" class="form-select @error('branch_id') is-invalid @enderror">
                            <option value="">All Branches</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                    {{ $branch->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small style="color: var(--text-secondary);">Leave empty to include all branches</small>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="color: var(--text-primary);">Date From <span class="text-danger">*</span></label>
                        <input type="date" name="date_from" class="form-control @error('date_from') is-invalid @enderror" 
                               value="{{ old('date_from') }}" required>
                        @error('date_from')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="color: var(--text-primary);">Date To <span class="text-danger">*</span></label>
                        <input type="date" name="date_to" class="form-control @error('date_to') is-invalid @enderror" 
                               value="{{ old('date_to') }}" required>
                        @error('date_to')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label" style="color: var(--text-primary);">Pay Date <span class="text-danger">*</span></label>
                        <input type="date" name="pay_date" class="form-control @error('pay_date') is-invalid @enderror" 
                               value="{{ old('pay_date') }}" required>
                        @error('pay_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="bi bi-info-circle me-2"></i>
                    <strong>Note:</strong> Payroll items will be automatically generated for all active staff members based on their salary configuration.
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save me-2"></i>Create Payroll Period
                    </button>
                    <a href="{{ route('admin.finance.payroll.index') }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
