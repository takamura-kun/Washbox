<?php

namespace App\Http\Controllers\Branch;

use App\Models\User;
use App\Models\Branch;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    /**
     * Display customers from staff's branch only
     */
    public function index(Request $request)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.login')->with('error', 'Please login to access this page.');
        }

        $branchId = $branch->id;

        // Query only customers from staff's branch
        $query = Customer::query()
            ->with(['preferredBranch', 'laundries'])
            ->where('preferred_branch_id', $branchId);

        // Handle Filters
        if ($request->filled('registration_type')) {
            $query->where('registration_type', $request->registration_type);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Calculate stats for staff's branch only
        $stats = [
            'total'           => Customer::where('preferred_branch_id', $branchId)->count(),
            'walk_in'         => Customer::where('preferred_branch_id', $branchId)
                                         ->where('registration_type', 'walk_in')->count(),
            'self_registered' => Customer::where('preferred_branch_id', $branchId)
                                         ->where('registration_type', 'self_registered')->count(),
            'new_today'       => Customer::where('preferred_branch_id', $branchId)
                                         ->whereDate('created_at', today())->count(),
        ];

        // Get staff's branch info
        $currentBranch = $branch;

        $customers = $query->latest()->paginate(20)->withQueryString();

        return view('branch.customers.index', compact('customers', 'stats', 'currentBranch'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.login')->with('error', 'Please login to access this page.');
        }

        $branches = Branch::all();
        $currentBranch = $branch;

        return view('branch.customers.create', compact('branches', 'currentBranch'));
    }

    /**
     * Store new customer
     */
    public function store(Request $request)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return back()->with('error', 'Please login to access this feature.');
        }

        $branchId = $branch->id;

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:11|unique:customers,phone',
            'email' => 'nullable|email|unique:customers,email',
            'preferred_branch_id' => 'nullable|exists:branches,id',
            'address' => 'nullable|string',
        ]);

        // Auto-assign to staff's branch if not specified
        $finalBranchId = $request->filled('preferred_branch_id')
            ? $request->preferred_branch_id
            : $branchId;

        Customer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'preferred_branch_id' => $finalBranchId,
            'registration_type' => 'walk_in',
            'password' => Hash::make($request->phone),
            'registered_by' => $branch->id,
            'is_active' => true,
        ]);

        return redirect()->route('branch.customers.index')
                         ->with('success', 'Customer registered successfully!');
    }

    /**
     * Show customer details (branch-scoped)
     */
    public function show($id)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.login')->with('error', 'Please login to access this page.');
        }

        $branchId = $branch->id;

        $customer = Customer::with(['preferredBranch', 'registeredBy', 'laundries', 'pickupRequests', 'ratings'])
            ->where('preferred_branch_id', $branchId)
            ->findOrFail($id);

        return view('branch.customers.show', compact('customer'));
    }

    /**
     * Show edit form (branch-scoped)
     */
    public function edit($id)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return redirect()->route('branch.login')->with('error', 'Please login to access this page.');
        }

        $branchId = $branch->id;

        $customer = Customer::where('preferred_branch_id', $branchId)->findOrFail($id);
        $branches = Branch::all();
        $currentBranch = $branch;

        return view('branch.customers.edit', compact('customer', 'branches', 'currentBranch'));
    }

    /**
     * Update customer (branch-scoped)
     */
    public function update(Request $request, $id)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return back()->with('error', 'Please login to access this feature.');
        }

        $branchId = $branch->id;

        $customer = Customer::where('preferred_branch_id', $branchId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20|unique:customers,phone,' . $customer->id,
            'email' => 'nullable|email|unique:customers,email,' . $customer->id,
            'address' => 'nullable|string',
            'preferred_branch_id' => 'nullable|exists:branches,id',
            'is_active' => 'nullable|boolean',
        ]);

        // Prevent changing branch away from staff's branch
        if ($request->filled('preferred_branch_id') && $request->preferred_branch_id != $branchId) {
            return back()->with('error', 'You can only manage customers from your branch.');
        }

        // Handle Password only if provided
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $customer->update($validated);

        return redirect()->route('branch.customers.show', $customer->id)
                         ->with('success', 'Customer profile updated successfully.');
    }

    /**
     * Delete customer (branch-scoped)
     */
    public function destroy($id)
    {
        $branch = Auth::guard('branch')->user();
        if (!$branch) {
            return back()->with('error', 'Please login to access this feature.');
        }

        $branchId = $branch->id;

        $customer = Customer::where('preferred_branch_id', $branchId)->findOrFail($id);
        $customer->delete();

        return redirect()->route('branch.customers.index')
                         ->with('success', 'Customer deleted successfully.');
    }
}
