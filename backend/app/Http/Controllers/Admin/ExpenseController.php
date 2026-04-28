<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $query = Expense::with(['branch', 'category', 'creator']);

        // Get salary category ID
        $salaryCategory = ExpenseCategory::where('slug', 'salaries')->first();
        $salaryCategoryId = $salaryCategory ? $salaryCategory->id : null;

        // Filter by expense type (tab)
        $type = $request->get('type', 'all');
        
        if ($type === 'salary') {
            if ($salaryCategoryId) {
                $query->where('expense_category_id', $salaryCategoryId)
                      ->whereNull('inventory_purchase_id');
            } else {
                // If no salary category exists, show no results
                $query->whereRaw('1 = 0');
            }
            
            // Filter by period if specified
            if ($request->filled('period')) {
                $period = $request->get('period');
                $now = now();
                
                if ($period === 'weekly') {
                    $query->whereBetween('expense_date', [
                        $now->copy()->startOfWeek(),
                        $now->copy()->endOfWeek()
                    ]);
                }
            }
        } elseif ($type === 'inventory') {
            $query->whereNotNull('inventory_purchase_id');
        } elseif ($type === 'other') {
            $query->where(function($q) use ($salaryCategoryId) {
                $q->whereNull('inventory_purchase_id');
                if ($salaryCategoryId) {
                    $q->where('expense_category_id', '!=', $salaryCategoryId);
                }
            });
        }
        // 'all' shows everything, no additional filter needed

        if ($request->filled('branch_id')) {
            $query->byBranch($request->branch_id);
        }

        if ($request->filled('category_id')) {
            $query->byCategory($request->category_id);
        }

        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        if ($request->filled('date_from') && $request->filled('date_to')) {
            $query->dateRange($request->date_from, $request->date_to);
        }

        $expenses = $query->orderBy('expense_date', 'desc')->paginate(20)->appends($request->except('page'));
        $branches = Branch::active()->get();
        $categories = ExpenseCategory::active()->get();

        $totalExpenses = $query->sum('amount');

        // Calculate stats for tabs
        $stats = [
            'total' => Expense::count(),
            'salary' => $salaryCategoryId ? Expense::where('expense_category_id', $salaryCategoryId)->whereNull('inventory_purchase_id')->count() : 0,
            'inventory' => Expense::whereNotNull('inventory_purchase_id')->count(),
            'other' => Expense::whereNull('inventory_purchase_id')
                ->where(function($q) use ($salaryCategoryId) {
                    if ($salaryCategoryId) {
                        $q->where('expense_category_id', '!=', $salaryCategoryId);
                    }
                })->count(),
        ];

        // Calculate inventory totals (only if on inventory tab)
        $inventoryTotals = [];
        if ($type === 'inventory') {
            $baseQuery = Expense::whereNotNull('inventory_purchase_id');
            
            // Apply same filters as main query
            if ($request->filled('branch_id')) {
                $baseQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $baseQuery->whereBetween('expense_date', [$request->date_from, $request->date_to]);
            }
            
            $totalInventory = (clone $baseQuery)->sum('amount');
            
            // Calculate weekly inventory from current week
            $now = now();
            $weeklyInventory = (clone $baseQuery)
                ->whereBetween('expense_date', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek()
                ])
                ->sum('amount');
            
            // Calculate monthly inventory from current month
            $monthlyInventory = (clone $baseQuery)
                ->whereBetween('expense_date', [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth()
                ])
                ->sum('amount');
            
            // Calculate yearly inventory from current year
            $yearlyInventory = (clone $baseQuery)
                ->whereBetween('expense_date', [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear()
                ])
                ->sum('amount');
            
            $inventoryTotals = [
                'total' => $totalInventory,
                'weekly' => $weeklyInventory,
                'monthly' => $monthlyInventory,
                'yearly' => $yearlyInventory,
            ];
        }

        // Calculate other expenses totals (only if on other tab)
        $otherTotals = [];
        if ($type === 'other') {
            $baseQuery = Expense::whereNull('inventory_purchase_id')
                ->where(function($q) use ($salaryCategoryId) {
                    if ($salaryCategoryId) {
                        $q->where('expense_category_id', '!=', $salaryCategoryId);
                    }
                });
            
            // Apply same filters as main query
            if ($request->filled('branch_id')) {
                $baseQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $baseQuery->whereBetween('expense_date', [$request->date_from, $request->date_to]);
            }
            
            $totalOther = (clone $baseQuery)->sum('amount');
            
            // Calculate weekly other from current week
            $now = now();
            $weeklyOther = (clone $baseQuery)
                ->whereBetween('expense_date', [
                    $now->copy()->startOfWeek(),
                    $now->copy()->endOfWeek()
                ])
                ->sum('amount');
            
            // Calculate monthly other from current month
            $monthlyOther = (clone $baseQuery)
                ->whereBetween('expense_date', [
                    $now->copy()->startOfMonth(),
                    $now->copy()->endOfMonth()
                ])
                ->sum('amount');
            
            // Calculate yearly other from current year
            $yearlyOther = (clone $baseQuery)
                ->whereBetween('expense_date', [
                    $now->copy()->startOfYear(),
                    $now->copy()->endOfYear()
                ])
                ->sum('amount');
            
            $otherTotals = [
                'total' => $totalOther,
                'weekly' => $weeklyOther,
                'monthly' => $monthlyOther,
                'yearly' => $yearlyOther,
            ];
        }

        // Calculate salary totals (only if on salary tab)
        $salaryTotals = [];
        if ($type === 'salary' && $salaryCategoryId) {
            $baseQuery = Expense::where('expense_category_id', $salaryCategoryId)
                ->whereNull('inventory_purchase_id');
            
            // Apply same filters as main query
            if ($request->filled('branch_id')) {
                $baseQuery->where('branch_id', $request->branch_id);
            }
            if ($request->filled('date_from') && $request->filled('date_to')) {
                $baseQuery->whereBetween('expense_date', [$request->date_from, $request->date_to]);
            }
            
            $totalSalary = (clone $baseQuery)->sum('amount');
            
            // Calculate by salary type
            $weeklySalary = (clone $baseQuery)->where('salary_type', 'weekly')->sum('amount');
            $monthlySalary = (clone $baseQuery)->where('salary_type', 'monthly')->sum('amount');
            $yearlySalary = (clone $baseQuery)->where('salary_type', 'yearly')->sum('amount');
            
            $salaryTotals = [
                'total' => $totalSalary,
                'weekly' => $weeklySalary,
                'monthly' => $monthlySalary,
                'yearly' => $yearlySalary,
            ];
        }

        return view('admin.finance.expenses.index', compact(
            'expenses', 
            'branches', 
            'categories', 
            'totalExpenses', 
            'stats', 
            'salaryTotals',
            'inventoryTotals',
            'otherTotals',
            'salaryCategory'
        ));
    }

    public function create()
    {
        $branches = Branch::active()->get();
        $categories = ExpenseCategory::active()->get();

        return view('admin.finance.expenses.create', compact('branches', 'categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'expense_category_id' => 'nullable|exists:expense_categories,id',
            'new_category' => 'nullable|string|max:255',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'reference_no' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'notes' => 'nullable|string',
            'is_recurring' => 'boolean',
        ]);

        // Handle new category creation
        if ($request->filled('new_category')) {
            $category = ExpenseCategory::firstOrCreate(
                ['name' => $request->new_category],
                [
                    'slug' => Str::slug($request->new_category),
                    'is_system' => false,
                    'is_active' => true,
                ]
            );
            $validated['expense_category_id'] = $category->id;
        }

        // Ensure we have a category
        if (empty($validated['expense_category_id'])) {
            return back()->withErrors(['expense_category_id' => 'Please select or enter a category'])->withInput();
        }

        if ($request->hasFile('attachment')) {
            $validated['attachment'] = $request->file('attachment')->store('expenses', 'public');
        }

        $validated['created_by'] = auth()->id();
        $validated['source'] = 'manual';

        $expense = Expense::create($validated);

        // Record financial transaction
        $financialService = app(\App\Services\FinancialTransactionService::class);
        $financialService->recordExpense($expense);

        return redirect()->route('admin.finance.expenses.index')
            ->with('success', 'Expense added successfully');
    }

    public function storeCategory(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:expense_categories,name',
            'description' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['name']);
        $validated['is_system'] = false;

        $category = ExpenseCategory::create($validated);

        return response()->json(['success' => true, 'category' => $category]);
    }
}
