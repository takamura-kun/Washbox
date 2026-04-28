<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FinancialAuditLog;
use Illuminate\Http\Request;

class FinancialAuditController extends Controller
{
    public function index(Request $request)
    {
        $query = FinancialAuditLog::with(['user', 'auditable'])
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('auditable_type')) {
            $query->where('auditable_type', $request->auditable_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $logs = $query->paginate(50);

        $users = \App\Models\User::orderBy('name')->get();

        $summary = [
            'total_logs' => FinancialAuditLog::count(),
            'today_logs' => FinancialAuditLog::whereDate('created_at', today())->count(),
            'unique_users' => FinancialAuditLog::distinct('user_id')->count('user_id'),
            'critical_actions' => FinancialAuditLog::whereIn('action', ['deleted', 'reversed'])->count(),
        ];

        return view('admin.finance.audit-logs.index', compact('logs', 'users', 'summary'));
    }

    public function show(FinancialAuditLog $log)
    {
        $log->load(['user', 'auditable']);

        return view('admin.finance.audit-logs.show', compact('log'));
    }
}
