@extends('admin.layouts.app')

@section('title', 'Purchases - Inventory')
@section('page-title', 'Supply Purchases')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush
@section('content')
<div class="container-xl px-4 py-4 dashboard-modern-wrapper">
    {{-- Navigation Tabs --}}
    <div class="supply-tabs mb-4">
        <a href="{{ route('admin.inventory.dashboard') }}">Dashboard</a>
        <a href="{{ route('admin.inventory.index') }}">All Items</a>
        <a href="{{ route('admin.inventory.manage') }}">Manage</a>
        <a href="{{ route('admin.inventory.purchases.index') }}" class="active">Purchases</a>
        <a href="{{ route('admin.inventory.distribute.index') }}">Distribute</a>
        <a href="{{ route('admin.inventory.branch-stock') }}">Branches</a>
        <a href="{{ route('admin.inventory.dist-log') }}">Dist-log</a>
    </div>

    {{-- Main Card: Purchase History --}}
    <div class="inventory-card">
        <div class="inventory-card-body">
            <div class="d-flex justify-content-between align-items-center mb-4 flex-column flex-md-row gap-3">
                <div>
                    <h2 class="h5 mb-1 fw-semibold">Purchase history</h2>
                    <p class="text-muted mb-0">{{ $purchases->total() }} entries • ₱{{ number_format($purchases->sum('grand_total'), 2) }}</p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.inventory.purchases.create') }}" class="btn-inventory btn-inventory-primary">
                        <i class="bi bi-plus-circle me-2"></i>Record Purchase
                    </a>
                    <button type="button" class="btn-inventory btn-inventory-success" onclick="exportToCSV()">
                        <i class="bi bi-download"></i> Export CSV
                    </button>
                    <button type="button" class="btn-inventory btn-inventory-primary" onclick="exportToExcel()">
                        <i class="bi bi-file-earmark-excel"></i> Export Excel
                    </button>
                </div>
            </div>

            @forelse($purchases as $purchase)
                {{-- Header Row (only show once) --}}
                @if($loop->first)
                <div class="purchase-card mb-2" style="background: var(--bg-color);">
                    <div class="p-2">
                        <div class="row align-items-center g-3">
                            <div class="col-auto" style="min-width: 120px;">
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">Date</small>
                            </div>
                            <div class="col-auto" style="min-width: 140px;">
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">Purchase ID</small>
                            </div>
                            <div class="col-auto" style="min-width: 150px;">
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">Store / Vendor</small>
                            </div>
                            <div class="col" style="min-width: 200px;">
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">Product</small>
                            </div>
                            <div class="col-auto text-center" style="min-width: 100px;">
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">Quantity</small>
                            </div>
                            <div class="col-auto text-end" style="min-width: 120px;">
                                <small class="text-muted fw-semibold" style="font-size: 0.75rem; text-transform: uppercase;">Total Amount</small>
                            </div>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Data Row --}}
                <div class="purchase-card mb-2">
                    <div class="p-2">
                        <div class="row align-items-center g-3">
                            {{-- Date --}}
                            <div class="col-auto" style="min-width: 120px;">
                                <span class="fw-semibold">{{ $purchase->purchase_date->format('M d, Y') }}</span>
                            </div>

                            {{-- Purchase ID --}}
                            <div class="col-auto" style="min-width: 140px;">
                                <span>{{ $purchase->reference_no }}</span>
                            </div>

                            {{-- Store/Vendor --}}
                            <div class="col-auto" style="min-width: 150px;">
                                <span>{{ $purchase->supplier?->name ?? 'N/A' }}</span>
                            </div>

                            {{-- Product --}}
                            <div class="col" style="min-width: 200px;">
                                <div class="d-flex gap-1 flex-wrap">
                                    @foreach($purchase->items as $item)
                                        <span class="text-truncate" style="max-width: 200px;">{{ $item->item->name }}@if(!$loop->last),@endif</span>
                                    @endforeach
                                </div>
                            </div>

                            {{-- Quantity --}}
                            <div class="col-auto text-center" style="min-width: 100px;">
                                <span class="fw-semibold">{{ $purchase->items->sum('quantity') }}</span>
                            </div>

                            {{-- Total Amount --}}
                            <div class="col-auto text-end" style="min-width: 120px;">
                                <span class="fw-semibold">₱{{ number_format($purchase->grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="inventory-empty">
                    <div class="inventory-empty-icon">
                        <i class="bi bi-cart-check"></i>
                    </div>
                    <h3 class="inventory-empty-title">No purchases recorded yet</h3>
                    <p class="inventory-empty-message">Start recording your supply purchases to track inventory.</p>
                    <a href="{{ route('admin.inventory.purchases.create') }}" class="btn btn-success rounded-pill">
                        <i class="bi bi-plus-circle me-2"></i> Record First Purchase
                    </a>
                </div>
            @endforelse

            @if($purchases->hasPages())
                <div class="mt-4">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- SheetJS library for Excel export -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.1/package/dist/xlsx.full.min.js"></script>

<script>
// Export to CSV
function exportToCSV() {
    const purchases = @json($purchases->items());

    // CSV Headers
    let csv = 'Date,Purchase ID,Store/Vendor,Product,Quantity,Total Amount\n';

    // CSV Data
    purchases.forEach(purchase => {
        const date = new Date(purchase.purchase_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
        const purchaseId = purchase.reference_no;
        const vendor = purchase.supplier || 'N/A';
        const products = purchase.items.map(item => item.item.name).join(', ');
        const quantity = purchase.items.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0);
        const total = parseFloat(purchase.grand_total || 0).toFixed(2);

        csv += `"${date}","${purchaseId}","${vendor}","${products}",${quantity},₱${total}\n`;
    });

    // Download CSV
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', `purchase_history_${new Date().toISOString().split('T')[0]}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Export to Excel using SheetJS
function exportToExcel() {
    const purchases = @json($purchases->items());

    // Prepare data for Excel
    const data = [];

    // Add header row
    data.push(['Date', 'Purchase ID', 'Store / Vendor', 'Product', 'Quantity', 'Total Amount']);

    // Add data rows
    purchases.forEach(purchase => {
        const date = new Date(purchase.purchase_date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: '2-digit' });
        const purchaseId = purchase.reference_no;
        const vendor = purchase.supplier || 'N/A';
        const products = purchase.items.map(item => item.item.name).join(', ');
        const quantity = purchase.items.reduce((sum, item) => sum + parseInt(item.quantity || 0), 0);
        const total = '₱' + parseFloat(purchase.grand_total || 0).toFixed(2);

        data.push([date, purchaseId, vendor, products, quantity, total]);
    });

    // Create workbook and worksheet
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);

    // Set column widths
    ws['!cols'] = [
        { wch: 15 },  // Date
        { wch: 18 },  // Purchase ID
        { wch: 20 },  // Store/Vendor
        { wch: 30 },  // Product
        { wch: 12 },  // Quantity
        { wch: 15 }   // Total Amount
    ];

    // Style header row (bold)
    const range = XLSX.utils.decode_range(ws['!ref']);
    for (let C = range.s.c; C <= range.e.c; ++C) {
        const address = XLSX.utils.encode_col(C) + "1";
        if (!ws[address]) continue;
        ws[address].s = {
            font: { bold: true },
            fill: { fgColor: { rgb: "F3F4F6" } },
            alignment: { horizontal: "left", vertical: "center" }
        };
    }

    // Add worksheet to workbook
    XLSX.utils.book_append_sheet(wb, ws, 'Purchase History');

    // Generate filename with current date
    const filename = `purchase_history_${new Date().toISOString().split('T')[0]}.xlsx`;

    // Download Excel file
    XLSX.writeFile(wb, filename);
}
</script>
@endpush
