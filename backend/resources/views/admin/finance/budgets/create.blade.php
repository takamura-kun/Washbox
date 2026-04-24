@extends('admin.layouts.app')

@section('title', 'Create Budget — WashBox')
@section('page-title', 'Create Budget')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Create New Budget</h2>
        <a href="{{ route('admin.finance.budgets.index') }}" class="btn-inventory btn-inventory-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Budgets
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <form method="POST" action="{{ route('admin.finance.budgets.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Budget Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" 
                                   value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Branch</label>
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
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Period <span class="text-danger">*</span></label>
                                <select name="period_type" class="form-select @error('period_type') is-invalid @enderror" required>
                                    <option value="monthly" {{ old('period_type') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    <option value="quarterly" {{ old('period_type') == 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                                    <option value="yearly" {{ old('period_type') == 'yearly' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @error('period_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Allocated Amount <span class="text-danger">*</span></label>
                                <input type="number" name="allocated_amount" step="0.01" 
                                       class="form-control @error('allocated_amount') is-invalid @enderror" 
                                       value="{{ old('allocated_amount') }}" required>
                                @error('allocated_amount')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Start Date <span class="text-danger">*</span></label>
                                <input type="date" name="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date') }}" required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">End Date <span class="text-danger">*</span></label>
                                <input type="date" name="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date') }}" required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Alert Threshold (%)</label>
                                <input type="number" name="alert_threshold" step="0.01" 
                                       class="form-control @error('alert_threshold') is-invalid @enderror" 
                                       value="{{ old('alert_threshold', 80) }}">
                                <small class="text-muted">Alert when utilization reaches this percentage</small>
                                @error('alert_threshold')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" rows="3" 
                                      class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn-inventory btn-inventory-primary">
                                <i class="bi bi-check-circle me-2"></i>Create Budget
                            </button>
                            <a href="{{ route('admin.finance.budgets.index') }}" class="btn-inventory btn-inventory-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="inventory-card">
                <div class="inventory-card-header">
                    <h5 class="mb-0">Budget Guidelines</h5>
                </div>
                <div class="inventory-card-body">
                    <ul class="small mb-0">
                        <li class="mb-2">Choose a descriptive name for easy identification</li>
                        <li class="mb-2">Select a branch or leave blank for company-wide budget</li>
                        <li class="mb-2">Set realistic allocated amounts based on historical data</li>
                        <li class="mb-2">Alert threshold triggers notifications when reached</li>
                        <li>Budget utilization updates automatically with expenses</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
