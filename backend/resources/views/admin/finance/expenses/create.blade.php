@extends('admin.layouts.app')

@section('title', 'Add Expense — WashBox')
@section('page-title', 'Add Expense')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')

<div class="container-xl px-4 py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h5 class="mb-4">Record New Expense</h5>

                    @if($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.finance.expenses.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="inventory-form-label">Branch <span class="text-danger">*</span></label>
                                <select name="branch_id" class="inventory-form-control" required>
                                    <option value="">Select a branch...</option>
                                    @foreach($branches as $branch)
                                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                            {{ $branch->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="inventory-form-hint">Which branch is this expense for?</small>
                            </div>

                            <div class="col-md-6">
                                <label class="inventory-form-label">Category <span class="text-danger">*</span></label>
                                <div class="d-flex gap-2 align-items-start">
                                    <div class="flex-grow-1">
                                        <select name="expense_category_id" id="categorySelect" class="inventory-form-control" {{ old('new_category') ? '' : 'required' }}>
                                            <option value="">Select a category...</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('expense_category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="text" name="new_category" id="newCategoryInput" class="inventory-form-control mt-2" placeholder="Enter new category name" value="{{ old('new_category') }}" style="display: none;">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="toggleCategoryBtn" onclick="toggleCategoryInput()" style="margin-top: 0; white-space: nowrap;">
                                        <i class="bi bi-plus-circle"></i> New
                                    </button>
                                </div>
                                <small class="inventory-form-hint">Select existing or create new category</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="inventory-form-label">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="inventory-form-control" placeholder="e.g., Monthly Utilities" value="{{ old('title') }}" required>
                            <small class="inventory-form-hint">Brief description of the expense</small>
                        </div>

                        <div class="mb-3">
                            <label class="inventory-form-label">Description</label>
                            <textarea name="description" class="inventory-form-control" rows="3" placeholder="Additional details...">{{ old('description') }}</textarea>
                            <small class="inventory-form-hint">Optional detailed description</small>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="inventory-form-label">Amount <span class="text-danger">*</span></label>
                                <input type="number" name="amount" class="inventory-form-control" placeholder="0.00" step="0.01" min="0" value="{{ old('amount') }}" required>
                                <small class="inventory-form-hint">Expense amount in PHP</small>
                            </div>

                            <div class="col-md-6">
                                <label class="inventory-form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" name="expense_date" class="inventory-form-control" value="{{ old('expense_date', now()->format('Y-m-d')) }}" required>
                                <small class="inventory-form-hint">When was this expense incurred?</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="inventory-form-label">Reference Number</label>
                            <input type="text" name="reference_no" class="inventory-form-control" placeholder="e.g., INV-2024-001" value="{{ old('reference_no') }}">
                            <small class="inventory-form-hint">Optional reference or invoice number</small>
                        </div>

                        <div class="mb-3">
                            <label class="inventory-form-label">Attachment</label>
                            <input type="file" name="attachment" class="inventory-form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <small class="inventory-form-hint">Receipt or invoice (JPG, PNG, PDF - Max 2MB)</small>
                        </div>

                        <div class="mb-3">
                            <label class="inventory-form-label">Notes</label>
                            <textarea name="notes" class="inventory-form-control" rows="2" placeholder="Additional notes...">{{ old('notes') }}</textarea>
                            <small class="inventory-form-hint">Internal notes about this expense</small>
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" name="is_recurring" class="form-check-input" id="isRecurring" {{ old('is_recurring') ? 'checked' : '' }}>
                            <label class="form-check-label" for="isRecurring">
                                This is a recurring expense
                            </label>
                        </div>

                        <div class="d-flex gap-2 justify-content-between">
                            <a href="{{ route('admin.finance.expenses.index') }}" class="btn-inventory btn-inventory-secondary">
                                Cancel
                            </a>
                            <button type="submit" class="btn-inventory btn-inventory-primary">
                                <i class="bi bi-check-lg me-2"></i>Record Expense
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function toggleCategoryInput() {
    const selectElement = document.getElementById('categorySelect');
    const inputElement = document.getElementById('newCategoryInput');
    const toggleBtn = document.getElementById('toggleCategoryBtn');
    
    if (inputElement.style.display === 'none') {
        // Show input, hide select
        selectElement.style.display = 'none';
        selectElement.removeAttribute('required');
        selectElement.value = '';
        inputElement.style.display = 'block';
        inputElement.setAttribute('required', 'required');
        inputElement.focus();
        toggleBtn.innerHTML = '<i class="bi bi-list"></i> Select';
        toggleBtn.classList.remove('btn-outline-primary');
        toggleBtn.classList.add('btn-outline-secondary');
    } else {
        // Show select, hide input
        inputElement.style.display = 'none';
        inputElement.removeAttribute('required');
        inputElement.value = '';
        selectElement.style.display = 'block';
        selectElement.setAttribute('required', 'required');
        toggleBtn.innerHTML = '<i class="bi bi-plus-circle"></i> New';
        toggleBtn.classList.remove('btn-outline-secondary');
        toggleBtn.classList.add('btn-outline-primary');
    }
}

// Initialize on page load if old input exists
document.addEventListener('DOMContentLoaded', function() {
    const newCategoryValue = '{{ old('new_category') }}';
    if (newCategoryValue) {
        toggleCategoryInput();
    }
});
</script>
@endpush

@endsection
