// ─── Load Supplies for Service Creation ───────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Service Supplies Script Loaded ===');
    
    const supplySelect = document.getElementById('supplySelect');
    const quantityInput = document.getElementById('quantityInput');
    const addSupplyBtn = document.getElementById('addSupplyBtn');
    const suppliesContainer = document.getElementById('suppliesContainer');
    const createServiceModal = document.getElementById('createServiceModal');

    console.log('Elements found:', {
        supplySelect: !!supplySelect,
        quantityInput: !!quantityInput,
        addSupplyBtn: !!addSupplyBtn,
        suppliesContainer: !!suppliesContainer,
        createServiceModal: !!createServiceModal
    });

    let supplyIndex = 0;
    let allSupplies = [];

    // Fetch supplies from server
    function loadSupplies() {
        console.log('Fetching supplies from API...');
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        fetch('/admin/inventory/supplies-api/active', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            },
            credentials: 'same-origin'
        })
        .then(response => {
            console.log('API Response Status:', response.status);
            console.log('API Response Headers:', response.headers);
            if (!response.ok) {
                return response.text().then(text => {
                    console.error('API Error Response:', text);
                    throw new Error(`HTTP error! status: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            console.log('API Response Data:', data);
            if (data.success && data.supplies) {
                allSupplies = data.supplies;
                console.log('Supplies received:', allSupplies.length, 'items');
                populateSupplySelect();
            } else if (Array.isArray(data)) {
                // Handle if API returns array directly
                allSupplies = data;
                console.log('Supplies received (array):', allSupplies.length, 'items');
                populateSupplySelect();
            } else {
                console.error('Invalid response format:', data);
                console.log('Attempting to populate with empty supplies');
                populateSupplySelect();
            }
        })
        .catch(error => {
            console.error('Error loading supplies:', error);
            console.log('Continuing with empty supplies list');
            populateSupplySelect();
        });
    }

    // Populate supply dropdown
    function populateSupplySelect() {
        if (!supplySelect) {
            console.error('supplySelect element not found');
            return;
        }
        
        console.log('Populating dropdown with', allSupplies.length, 'supplies');
        supplySelect.innerHTML = '<option value="">-- Select a supply --</option>';
        
        if (allSupplies.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = '-- No supplies available --';
            option.disabled = true;
            supplySelect.appendChild(option);
            console.log('No supplies to display');
            return;
        }
        
        allSupplies.forEach(supply => {
            const option = document.createElement('option');
            option.value = supply.id;
            option.textContent = `${supply.name} (${supply.unit})`;
            option.dataset.unit = supply.unit;
            supplySelect.appendChild(option);
        });
        
        console.log('Dropdown populated successfully');
    }

    // Add supply to service
    if (addSupplyBtn) {
        addSupplyBtn.addEventListener('click', function() {
            const supplyId = supplySelect.value;
            const quantity = quantityInput.value;

            console.log('Add supply clicked:', { supplyId, quantity });

            if (!supplyId) {
                alert('Please select a supply');
                return;
            }

            if (!quantity || quantity < 1) {
                alert('Please enter a valid quantity');
                return;
            }

            const supply = allSupplies.find(s => s.id == supplyId);
            if (!supply) {
                console.error('Supply not found:', supplyId);
                return;
            }

            // Check if already added
            if (suppliesContainer.querySelector(`[data-supply-id="${supplyId}"]`)) {
                alert('This supply is already added');
                return;
            }

            // Create supply row
            const row = document.createElement('div');
            row.className = 'supply-item p-2 mb-2 border rounded d-flex justify-content-between align-items-center';
            row.dataset.supplyId = supplyId;
            row.dataset.quantity = quantity;
            row.innerHTML = `
                <div class="flex-grow-1">
                    <strong>${supply.name}</strong>
                    <small class="text-muted d-block">${quantity} ${supply.unit}</small>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-supply-btn">
                    <i class="bi bi-trash"></i>
                </button>
            `;

            suppliesContainer.appendChild(row);
            console.log('Supply added:', supply.name);

            // Remove option from select
            const option = supplySelect.querySelector(`option[value="${supplyId}"]`);
            if (option) option.remove();
            supplySelect.value = '';
            quantityInput.value = '1';

            // Add remove listener
            row.querySelector('.remove-supply-btn').addEventListener('click', function() {
                const newOption = document.createElement('option');
                newOption.value = supply.id;
                newOption.textContent = `${supply.name} (${supply.unit})`;
                newOption.dataset.unit = supply.unit;
                supplySelect.appendChild(newOption);
                row.remove();
                console.log('Supply removed:', supply.name);
            });
        });
    } else {
        console.error('addSupplyBtn element not found');
    }

    // Reset modal on open
    if (createServiceModal) {
        createServiceModal.addEventListener('show.bs.modal', function() {
            console.log('Modal opened, loading supplies...');
            loadSupplies();
            if (suppliesContainer) {
                suppliesContainer.innerHTML = '';
            }
            supplyIndex = 0;
        });
    } else {
        console.error('createServiceModal element not found');
    }

    // Load supplies on page load
    console.log('Initial load of supplies');
    loadSupplies();
});
