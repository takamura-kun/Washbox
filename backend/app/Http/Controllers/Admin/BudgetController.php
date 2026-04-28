<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{Budget, Branch, ExpenseCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = Budget::with(['branch', 'category', 'creator'])
            ->orderBy('start_date', 'desc');

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'over_budget') {
                $query->whereRaw('spent_amount > allocated_amount');
            } elseif ($request->status === 'near_limit') {
                $query->whereRaw('(spent_amount / allocated_amount * 100) >= alert_threshold');
            }
        }

        $budgets = $query->paginate(20);

        // Summary
        $summary = [
            'total_allocated' => Budget::active()->sum('allocated_amount'),
            'total_spent' => Budget::active()->sum('spent_amount'),
            'total_remaining' => Budget::active()->sum('remaining_amount'),
            'over_budget_count' => Budget::active()->whereRaw('spent_amount > allocated_amount')->count(),
        ];

        $branches = Branch::active()->get();

        return view('admin.finance.budgets.index', compact('budgets', 'summary', 'branches'));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        $categories = ExpenseCategory::active()->get();

        return view('admin.finance.budgets.create', compact('branches', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'allocated_amount' => 'required|numeric|min:0',
            'alert_threshold' => 'required|integer|min:1|max:100',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = Auth::id();

        $budget = Budget::create($validated);

        return redirect()
            ->route('admin.finance.budgets.show', $budget)
            ->with('success', 'Budget created successfully');
    }

    public function show(Budget $budget)
    {
        $budget->load(['branch', 'category', 'creator']);
        
        // Get expenses for this budget
        $query = \App\Models\Expense::query();
        
        // Filter by branch if specified
        if ($budget->branch_id) {
            $query->where('branch_id', $budget->branch_id);
        }
        
        // Filter by category if specified
        if ($budget->expense_category_id) {
            $query->where('expense_category_id', $budget->expense_category_id);
        }
        
        $expenses = $query->whereBetween('expense_date', [$budget->start_date, $budget->end_date])
            ->with(['category', 'creator'])
            ->orderBy('expense_date', 'desc')
            ->paginate(20);

        return view('admin.finance.budgets.show', compact('budget', 'expenses'));
    }

    public function edit(Budget $budget)
    {
        $branches = Branch::active()->get();
        $categories = ExpenseCategory::active()->get();

        return view('admin.finance.budgets.edit', compact('budget', 'branches', 'categories'));
    }

    public function update(Request $request, Budget $budget)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'period_type' => 'required|in:monthly,quarterly,yearly',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'allocated_amount' => 'required|numeric|min:0',
            'alert_threshold' => 'required|integer|min:1|max:100',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        $budget->update($validated);
        $budget->updateSpentAmount();

        return redirect()
            ->route('admin.finance.budgets.show', $budget)
            ->with('success', 'Budget updated successfully');
    }

    public function destroy(Budget $budget)
    {
        $budget->delete();

        return redirect()
            ->route('admin.finance.budgets.index')
            ->with('success', 'Budget deleted successfully');
    }

    public function refresh(Budget $budget)
    {
        $budget->updateSpentAmount();

        return redirect()
            ->route('admin.finance.budgets.show', $budget)
            ->with('success', 'Budget refreshed successfully');
    }
}
