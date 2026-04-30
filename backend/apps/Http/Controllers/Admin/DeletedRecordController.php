<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DeletedRecord;
use App\Models\Branch;
use Illuminate\Http\Request;

class DeletedRecordController extends Controller
{
    public function index(Request $request)
    {
        $query = DeletedRecord::with('branch')->orderBy('deleted_at', 'desc');

        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('deleted_by')) {
            $query->where('deleted_by_name', 'like', '%' . $request->deleted_by . '%');
        }

        if ($request->filled('date_from')) {
            $query->whereDate('deleted_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('deleted_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('model_label', 'like', '%' . $request->search . '%');
        }

        $records = $query->paginate(50)->withQueryString();

        $branches = Branch::orderBy('name')->get();
        $modules  = DeletedRecord::distinct('module')->pluck('module')->sort()->values();

        $summary = [
            'total'   => DeletedRecord::count(),
            'today'   => DeletedRecord::whereDate('deleted_at', today())->count(),
            'week'    => DeletedRecord::whereBetween('deleted_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'by_module' => DeletedRecord::selectRaw('module, count(*) as total')
                ->groupBy('module')->pluck('total', 'module'),
        ];

        return view('admin.deleted-records.index', compact('records', 'branches', 'modules', 'summary'));
    }

    public function show(DeletedRecord $deletedRecord)
    {
        return view('admin.deleted-records.show', compact('deletedRecord'));
    }
}
