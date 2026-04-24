@extends('admin.layouts.app')

@section('title', 'Manage Service Supplies - ' . $service->name)

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 mb-0">
                <a href="{{ route('admin.services.show', $service) }}" class="text-decoration-none">
                    {{ $service->name }}
                </a>
                <small class="text-muted">/</small>
                <span class="text-muted">Manage Supplies</span>
            </h1>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.services.show', $service) }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-light">
            <h5 class="mb-0">Service Supplies Configuration</h5>
            <small class="text-muted">Define which supplies are consumed when this service is used</small>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.services.supplies.update', $service) }}" method="POST" id="suppliesForm">
                @csrf
                
                <div id="suppliesContainer">
                    @forelse($service->supplies as $supply)
                        <div class="supply-row mb-3 p-3 border rounded" data-supply-id="{{ $supply->id }}">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">Supply</label>
                                    <input type="hidden" name="supplies[{{ $loop->index }}][supply_id]" value="{{ $supply->id }}">
                                    <div class="form-control-plaintext fw-500">
                                        {{ $supply->name }}
                                        <small class="text-muted d-block">{{ $supply->unit }}</small>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Quantity Required</label>
                                    <input type="number" 
                                           name="supplies[{{ $loop->index }}][quantity_required]" 
                                           class="form-control" 
                                           value="{{ $supply->pivot->quantity_required }}"
                                           min="1"
                                           required>
                                </div>
                                <div class="col-md-2">
                                    <button type="button" class="btn btn-sm btn-danger remove-supply" data-supply-id="{{ $supply->id }}">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> No supplies assigned yet. Add supplies below.
                        </div>
                    @endforelse
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label class="form-label">Add Supply</label>
                    <div class="row">
                        <div class="col-md-6">
                            <select id="supplySelect" class="form-select">
                                <option value="">-- Select a supply --</option>
                                @foreach($availableSupplies as $supply)
                                    @if(!$service->supplies->contains($supply->id))
                                        <option value="{{ $supply->id }}" data-unit="{{ $supply->unit }}">
                                            {{ $supply->name }} ({{ $supply->unit }})
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <input type="number" id="quantityInput" class="form-control" placeholder="Quantity" min="1" value="1">
                        </div>
                        <div class="col-md-2">
                            <button type="button" class="btn btn-primary w-100" id="addSupplyBtn">
                                <i class="bi bi-plus"></i> Add
                            </button>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.services.show', $service) }}" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let supplyIndex = {{ $service->supplies->count() }};

    document.getElementById('addSupplyBtn').addEventListener('click', function() {
        const select = document.getElementById('supplySelect');
        const quantity = document.getElementById('quantityInput');
        const supplyId = select.value;
        const supplyName = select.options[select.selectedIndex].text;

        if (!supplyId) {
            alert('Please select a supply');
            return;
        }

        if (!quantity.value || quantity.value < 1) {
            alert('Please enter a valid quantity');
            return;
        }

        const container = document.getElementById('suppliesContainer');
        const row = document.createElement('div');
        row.className = 'supply-row mb-3 p-3 border rounded';
        row.dataset.supplyId = supplyId;
        row.innerHTML = `
            <div class="row align-items-end">
                <div class="col-md-6">
                    <label class="form-label">Supply</label>
                    <input type="hidden" name="supplies[${supplyIndex}][supply_id]" value="${supplyId}">
                    <div class="form-control-plaintext fw-500">
                        ${supplyName}
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantity Required</label>
                    <input type="number" 
                           name="supplies[${supplyIndex}][quantity_required]" 
                           class="form-control" 
                           value="${quantity.value}"
                           min="1"
                           required>
                </div>
                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-danger remove-supply" data-supply-id="${supplyId}">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </div>
            </div>
        `;

        container.appendChild(row);
        supplyIndex++;

        // Remove empty state alert if exists
        const emptyAlert = container.querySelector('.alert-info');
        if (emptyAlert) {
            emptyAlert.remove();
        }

        // Remove option from select
        select.querySelector(`option[value="${supplyId}"]`).remove();
        select.value = '';
        quantity.value = '1';
    });

    // Remove supply
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-supply')) {
            const btn = e.target.closest('.remove-supply');
            const supplyId = btn.dataset.supplyId;
            const row = btn.closest('.supply-row');
            const supplyName = row.querySelector('.form-control-plaintext').textContent.trim();

            // Add back to select
            const select = document.getElementById('supplySelect');
            const supply = @json($availableSupplies);
            const supplyData = supply.find(s => s.id == supplyId);
            
            if (supplyData) {
                const option = document.createElement('option');
                option.value = supplyData.id;
                option.textContent = `${supplyData.name} (${supplyData.unit})`;
                select.appendChild(option);
            }

            row.remove();

            // Show empty state if no supplies
            const container = document.getElementById('suppliesContainer');
            if (container.querySelectorAll('.supply-row').length === 0) {
                container.innerHTML = '<div class="alert alert-info"><i class="bi bi-info-circle"></i> No supplies assigned yet. Add supplies below.</div>';
            }
        }
    });
});
</script>
@endsection
