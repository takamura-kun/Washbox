// ─── Load Supplies for Service Edit ───────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const supplySelect = document.getElementById('supplySelect');
    const quantityInput = document.getElementById('quantityInput');
    const addSupplyBtn = document.getElementById('addSupplyBtn');
    const suppliesContainer = document.getElementById('suppliesContainer');
    const serviceForm = document.getElementById('serviceForm');
    const suppliesCount = document.getElementById('suppliesCount');

    let allSupplies = [];
    let currentSupplyIds = new Set();

    // Fetch supplies from server
    function loadSupplies() {
        fetch('/admin/inventory/supplies-api/active', {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.supplies) {
                allSupplies = data.supplies;
                populateSupplySelect();
            }
        })
        .catch(error => {
            console.error('Error loading supplies:', error);
            showNotification('Failed to load supplies', 'danger');
        });
    }

    // Populate supply dropdown
    function populateSupplySelect() {
        supplySelect.innerHTML = '<option value="">-- Select a supply --</option>';
        allSupplies.forEach(supply => {
            if (!currentSupplyIds.has(supply.id)) {
                const option = document.createElement('option');
                option.value = supply.id;
                option.textContent = `${supply.name}${supply.brand ? ' - ' + supply.brand : ''} (${supply.unit_label || 'units'})`;
                option.dataset.unit = supply.unit_label || 'units';
                option.dataset.name = supply.name;
                option.dataset.brand = supply.brand || '';
                supplySelect.appendChild(option);
            }
        });
    }

    // Initialize current supplies
    function initializeCurrentSupplies() {
        document.querySelectorAll('#suppliesContainer .supply-item').forEach(item => {
            currentSupplyIds.add(parseInt(item.dataset.supplyId));
        });
        updateSuppliesCount();
    }

    // Update supplies count badge
    function updateSuppliesCount() {
        const count = currentSupplyIds.size;
        if (suppliesCount) {
            suppliesCount.textContent = `${count} item${count !== 1 ? 's' : ''}`;
        }
        
        // Show/hide empty message
        const emptyMessage = document.getElementById('emptySuppliesMessage');
        if (emptyMessage) {
            emptyMessage.style.display = count > 0 ? 'none' : 'block';
        }
    }

    // Show notification
    function showNotification(message, type = 'success') {
        // Create toast notification
        const toast = document.createElement('div');
        toast.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        toast.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    // Add supply to service
    if (addSupplyBtn) {
        addSupplyBtn.addEventListener('click', function() {
            const supplyId = supplySelect.value;
            const quantity = parseFloat(quantityInput.value);

            if (!supplyId) {
                showNotification('Please select a supply', 'warning');
                return;
            }

            if (!quantity || quantity <= 0) {
                showNotification('Please enter a valid quantity', 'warning');
                return;
            }

            const supply = allSupplies.find(s => s.id == supplyId);
            if (!supply) return;

            // Check if already added
            if (currentSupplyIds.has(parseInt(supplyId))) {
                showNotification('This supply is already added', 'warning');
                return;
            }

            // Remove empty message if exists
            const emptyMessage = document.getElementById('emptySuppliesMessage');
            if (emptyMessage) {
                emptyMessage.remove();
            }

            // Create supply row
            const row = document.createElement('div');
            row.className = 'supply-item p-3 mb-2 border rounded d-flex justify-content-between align-items-center';
            row.style.background = 'var(--card-bg)';
            row.dataset.supplyId = supplyId;
            row.dataset.quantity = quantity;
            row.innerHTML = `
                <div class="flex-grow-1">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-box-seam text-primary"></i>
                        <div>
                            <strong>${supply.name}</strong>
                            ${supply.brand ? `<span class="badge bg-secondary ms-2" style="font-size: 0.7rem;">${supply.brand}</span>` : ''}
                            <small class="text-muted d-block">${quantity} ${supply.unit_label || 'units'} per service</small>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-outline-danger remove-supply-btn">
                    <i class="bi bi-trash"></i> Remove
                </button>
            `;

            suppliesContainer.appendChild(row);
            currentSupplyIds.add(parseInt(supplyId));
            updateSuppliesCount();

            // Remove option from select
            supplySelect.querySelector(`option[value="${supplyId}"]`).remove();
            supplySelect.value = '';
            quantityInput.value = '1';

            showNotification(`Added ${supply.name} to service supplies`, 'success');

            // Add remove listener
            row.querySelector('.remove-supply-btn').addEventListener('click', function() {
                const option = document.createElement('option');
                option.value = supply.id;
                option.textContent = `${supply.name}${supply.brand ? ' - ' + supply.brand : ''} (${supply.unit_label || 'units'})`;
                option.dataset.unit = supply.unit_label || 'units';
                option.dataset.name = supply.name;
                option.dataset.brand = supply.brand || '';
                supplySelect.appendChild(option);
                currentSupplyIds.delete(parseInt(supplyId));
                updateSuppliesCount();
                row.remove();
                showNotification(`Removed ${supply.name} from service supplies`, 'info');
            });
        });
    }

    // Add remove listeners to existing supplies
    document.querySelectorAll('.remove-supply-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('.supply-item');
            const supplyId = parseInt(row.dataset.supplyId);
            const supply = allSupplies.find(s => s.id === supplyId);
            
            if (supply) {
                const option = document.createElement('option');
                option.value = supply.id;
                option.textContent = `${supply.name}${supply.brand ? ' - ' + supply.brand : ''} (${supply.unit_label || 'units'})`;
                option.dataset.unit = supply.unit_label || 'units';
                option.dataset.name = supply.name;
                option.dataset.brand = supply.brand || '';
                supplySelect.appendChild(option);
            }
            
            currentSupplyIds.delete(supplyId);
            updateSuppliesCount();
            row.remove();
            
            if (supply) {
                showNotification(`Removed ${supply.name} from service supplies`, 'info');
            }
        });
    });

    // Handle form submission with supplies
    if (serviceForm) {
        serviceForm.addEventListener('submit', function(e) {
            // Collect supplies data
            const supplies = [];
            document.querySelectorAll('#suppliesContainer .supply-item').forEach((item) => {
                supplies.push({
                    supply_id: item.dataset.supplyId,
                    quantity_required: item.dataset.quantity
                });
            });

            // Add supplies as hidden input
            let suppliesInput = document.getElementById('suppliesInput');
            if (!suppliesInput) {
                suppliesInput = document.createElement('input');
                suppliesInput.type = 'hidden';
                suppliesInput.id = 'suppliesInput';
                suppliesInput.name = 'supplies';
                serviceForm.appendChild(suppliesInput);
            }
            if (supplies.length > 0) {
                suppliesInput.value = JSON.stringify(supplies);
            } else {
                suppliesInput.value = '[]';
            }
        });
    }

    // Load supplies on page load
    initializeCurrentSupplies();
    loadSupplies();
});
