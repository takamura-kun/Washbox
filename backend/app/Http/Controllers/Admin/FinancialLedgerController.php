<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{FinancialTransaction, Branch, FinancialAuditLog};
use App\Services\FinancialTransactionService;
use Illuminate\Http\Request;

class FinancialLedgerController extends Controller
{
    protected $financialService;

    public function __construct(FinancialTransactionService $financialService)
    {
        $this->financialService = $financialService;
    }

    public function index(Request $request)
    {
        $query = FinancialTransaction::with(['branch', 'creator', 'approver'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_number', 'like', "%{$request->search}%")
                  ->orWhere('description', 'like', "%{$request->search}%");
            });
        }

        $transactions = $query->paginate(50);

        // Summary stats
        $summary = [
            'total_income' => FinancialTransaction::income()->completed()->sum('amount'),
            'total_expenses' => FinancialTransaction::expense()->completed()->sum('amount'),
            // 'pending_count' => FinancialTransaction::where('status', 'pending')->count(), // Hidden for now
        ];

        $branches = Branch::active()->get();

        return view('admin.finance.ledger.index', compact('transactions', 'summary', 'branches'));
    }

    public function show(FinancialTransaction $transaction)
    {
        $transaction->load(['branch', 'creator', 'approver', 'reference']);
        
        return view('admin.finance.ledger.show', compact('transaction'));
    }

    public function reverse(Request $request, FinancialTransaction $transaction)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $reversal = $this->financialService->reverseTransaction($transaction, $request->reason);

        return redirect()
            ->route('admin.finance.ledger.show', $reversal)
            ->with('success', 'Transaction reversed successfully');
    }

    // PENDING TRANSACTIONS FEATURE - DISABLED FOR NOW
    // Uncomment these methods and routes to enable pending transactions approval workflow
    
    /*
    public function pending(Request $request)
    {
        $query = FinancialTransaction::with(['branch', 'creator'])
            ->where('status', 'pending')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc');

        // Filters
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        $transactions = $query->paginate(50);
        $branches = Branch::active()->get();

        return view('admin.finance.ledger.pending', compact('transactions', 'branches'));
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense,transfer',
            'category' => 'required|in:laundry_sale,retail_sale,pickup_fee,delivery_fee,expense,payroll,inventory_purchase,refund,adjustment,other',
            'amount' => 'required|numeric|min:0',
            'branch_id' => 'required|exists:branches,id',
            'transaction_date' => 'required|date',
            'description' => 'required|string|max:1000',
            'payment_method' => 'nullable|string|max:50',
            'reference_number' => 'nullable|string|max:100',
        ]);

        $transaction = FinancialTransaction::create([
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'branch_id' => $validated['branch_id'],
            'transaction_date' => $validated['transaction_date'],
            'description' => $validated['description'],
            'metadata' => [
                'payment_method' => $validated['payment_method'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
            ],
            'status' => 'pending',
            'created_by' => auth()->id(),
        ]);

        FinancialAuditLog::logAudit(
            'created',
            $transaction,
            null,
            $transaction->toArray(),
            'Pending transaction created manually'
        );

        return redirect()
            ->route('admin.finance.ledger.pending')
            ->with('success', "Transaction {$transaction->transaction_number} created successfully and is pending approval.");
    }

    public function approve(FinancialTransaction $transaction)
    {
        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Only pending transactions can be approved');
        }

        $transaction->update([
            'status' => 'completed',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        FinancialAuditLog::logAudit(
            'approved',
            $transaction,
            ['status' => 'pending'],
            ['status' => 'completed', 'approved_by' => auth()->id()],
            'Transaction approved by ' . auth()->user()->name
        );

        return back()->with('success', 'Transaction approved successfully');
    }

    public function reject(Request $request, FinancialTransaction $transaction)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Only pending transactions can be rejected');
        }

        $transaction->update([
            'status' => 'cancelled',
            'metadata' => array_merge($transaction->metadata ?? [], [
                'rejection_reason' => $request->reason,
                'rejected_by' => auth()->id(),
                'rejected_at' => now()->toDateTimeString(),
            ]),
        ]);

        FinancialAuditLog::logAudit(
            'rejected',
            $transaction,
            ['status' => 'pending'],
            ['status' => 'cancelled'],
            "Transaction rejected: {$request->reason}"
        );

        return back()->with('success', 'Transaction rejected successfully');
    }
    */

    // END OF PENDING TRANSACTIONS FEATURE

    public function export(Request $request)
    {
        $query = FinancialTransaction::with(['branch', 'creator']);

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_number', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $filename = 'financial_ledger_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Transaction #', 'Date', 'Type', 'Category', 'Description', 'Branch', 'Amount', 'Status', 'Created By']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_number,
                    $transaction->transaction_date->format('Y-m-d H:i:s'),
                    ucfirst($transaction->type),
                    str_replace('_', ' ', ucwords($transaction->category)),
                    $transaction->description,
                    $transaction->branch->name ?? 'N/A',
                    $transaction->amount,
                    ucfirst($transaction->status),
                    $transaction->creator->name ?? 'System',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
