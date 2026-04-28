<div class="row g-2 mb-2">
    {{-- Revenue Card --}}
    {{-- Cash Flow Card --}}
    <div class="col-lg-3 col-md-6">

    </div>
</div>

<div class="row g-2 mb-2">
    {{-- Completion Rate --}}
    <div class="col-lg-3 col-md-6">
        <div class="modern-card">
            <div class="card-body-modern" style="padding:10px;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div style="width:28px;height:28px;border-radius:6px;background:rgba(16,185,129,0.1);
                                display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-check-circle-fill" style="font-size:0.9rem;color:#10b981;"></i>
                    </div>
                </div>
                <div style="font-size:0.6rem;color:#6b7280;margin-bottom:2px;font-weight:600;text-transform:uppercase;">
                    Completion Rate
                </div>
                <div style="font-size:1.4rem;font-weight:700;color:#10b981;margin-bottom:4px;">
                    {{ $service_quality['completion_rate'] }}%
                </div>
                <div style="font-size:0.6rem;color:#64748b;">
                    {{ $service_quality['completed_orders'] }} of {{ $service_quality['total_orders'] }} orders
                </div>
            </div>
        </div>
    </div>

    {{-- Service Accuracy --}}
    <div class="col-lg-3 col-md-6">
        <div class="modern-card">
            <div class="card-body-modern" style="padding:10px;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div style="width:28px;height:28px;border-radius:6px;background:rgba(59,130,246,0.1);
                                display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-bullseye" style="font-size:0.9rem;color:#3b82f6;"></i>
                    </div>
                </div>
                <div style="font-size:0.6rem;color:#6b7280;margin-bottom:2px;font-weight:600;text-transform:uppercase;">
                    Service Accuracy
                </div>
                <div style="font-size:1.4rem;font-weight:700;color:#3b82f6;margin-bottom:4px;">
                    {{ $service_quality['service_accuracy'] }}%
                </div>
                <div style="font-size:0.6rem;color:#64748b;">
                    {{ $service_quality['cancelled_orders'] }} cancelled/redo
                </div>
            </div>
        </div>
    </div>

    {{-- Avg Turnaround Time --}}
    <div class="col-lg-3 col-md-6">
        <div class="modern-card">
            <div class="card-body-modern" style="padding:10px;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div style="width:28px;height:28px;border-radius:6px;background:rgba(245,158,11,0.1);
                                display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-clock-history" style="font-size:0.9rem;color:#f59e0b;"></i>
                    </div>
                </div>
                <div style="font-size:0.6rem;color:#6b7280;margin-bottom:2px;font-weight:600;text-transform:uppercase;">
                    Avg Turnaround Time
                </div>
                <div style="font-size:1.4rem;font-weight:700;color:#f59e0b;margin-bottom:4px;">
                    {{ $service_quality['avg_turnaround_days'] }}d
                </div>
                <div style="font-size:0.6rem;color:#64748b;">
                    {{ $service_quality['avg_turnaround_hours'] }} hours avg
                </div>
            </div>
        </div>
    </div>

    {{-- Customer Retention --}}
    <div class="col-lg-3 col-md-6">
        <div class="modern-card">
            <div class="card-body-modern" style="padding:10px;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <div style="width:28px;height:28px;border-radius:6px;background:rgba(139,92,246,0.1);
                                display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-people-fill" style="font-size:0.9rem;color:#8b5cf6;"></i>
                    </div>
                </div>
                <div style="font-size:0.6rem;color:#6b7280;margin-bottom:2px;font-weight:600;text-transform:uppercase;">
                    Customer Retention
                </div>
                <div style="font-size:1.4rem;font-weight:700;color:#8b5cf6;margin-bottom:4px;">
                    {{ $service_quality['retention_rate'] }}%
                </div>
                <div style="font-size:0.6rem;color:#64748b;">
                    {{ $service_quality['repeat_customers'] }} repeat
                </div>
            </div>
        </div>
    </div>
</div>

