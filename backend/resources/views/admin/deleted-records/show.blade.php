@extends('admin.layouts.app')

@section('title', 'Deleted Record Detail — WashBox')
@section('page-title', 'Deleted Record Detail')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/inventory.css') }}">
@endpush

@section('content')
<div class="container-xl px-4 py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">{{ $deletedRecord->model_label }}</h2>
            <p class="text-muted mb-0">{{ class_basename($deletedRecord->model_type) }} — deleted {{ $deletedRecord->deleted_at->format('M d, Y h:i A') }}</p>
        </div>
        <a href="{{ route('admin.deleted-records.index') }}" class="btn-inventory btn-inventory-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="row g-4">
        {{-- Meta Info --}}
        <div class="col-md-4">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h6 class="fw-bold mb-3">Deletion Info</h6>
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width:40%">Module</td>
                            <td><span class="badge bg-secondary">{{ ucfirst($deletedRecord->module) }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Model</td>
                            <td>{{ class_basename($deletedRecord->model_type) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Original ID</td>
                            <td>#{{ $deletedRecord->original_id }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Deleted By</td>
                            <td>{{ $deletedRecord->deleted_by_name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Actor Type</td>
                            <td>{{ class_basename($deletedRecord->deleted_by_type ?? 'System') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Branch</td>
                            <td>{{ $deletedRecord->branch->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">IP Address</td>
                            <td>{{ $deletedRecord->ip_address ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Deleted At</td>
                            <td>{{ $deletedRecord->deleted_at->format('M d, Y h:i:s A') }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        {{-- Full Data Snapshot --}}
        <div class="col-md-8">
            <div class="inventory-card">
                <div class="inventory-card-body">
                    <h6 class="fw-bold mb-3">Full Data Snapshot</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th style="width:35%">Field</th>
                                    <th>Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($deletedRecord->data as $field => $value)
                                <tr>
                                    <td class="text-muted fw-semibold" style="font-size:0.75rem;">
                                        {{ str_replace('_', ' ', ucwords($field)) }}
                                    </td>
                                    <td style="font-size:0.8rem; word-break:break-word;">
                                        @if(is_null($value))
                                            <span class="text-muted fst-italic">null</span>
                                        @elseif(is_array($value))
                                            <code style="font-size:0.7rem;">{{ json_encode($value, JSON_PRETTY_PRINT) }}</code>
                                        @elseif(is_bool($value))
                                            <span class="badge bg-{{ $value ? 'success' : 'secondary' }}">{{ $value ? 'true' : 'false' }}</span>
                                        @else
                                            {{ $value }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
