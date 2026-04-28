<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Branch;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('branch')
            ->orderBy('created_at', 'desc');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('causer_name')) {
            $query->where('causer_name', 'like', '%' . $request->causer_name . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(50)->withQueryString();

        $branches = Branch::orderBy('name')->get();

        $modules = ActivityLog::distinct('module')->pluck('module')->sort()->values();
        $events  = ActivityLog::distinct('event')->pluck('event')->sort()->values();

        $summary = [
            'total'   => ActivityLog::count(),
            'today'   => ActivityLog::whereDate('created_at', today())->count(),
            'logins'  => ActivityLog::where('event', 'login')->whereDate('created_at', today())->count(),
            'errors'  => ActivityLog::whereIn('event', ['deleted', 'deactivated'])->whereDate('created_at', today())->count(),
        ];

        return view('admin.activity-logs.index', compact(
            'logs', 'branches', 'modules', 'events', 'summary'
        ));
    }
}
