<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use App\Models\Laundry;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PaymentVerificationController extends Controller
{
    /**
     * Verify that the payment proof belongs to the staff's branch
     */
    private function verifyBranchAccess(PaymentProof $paymentProof)
    {
        // Get authenticated user (could be branch or regular user)
        $user = Auth::guard('branch')->user() ?? Auth::user();

        // Load laundry if not already loaded
        if (!$paymentProof->relationLoaded('laundry')) {
            $paymentProof->load('laundry');
        }

        // Check if laundry exists
        if (!$paymentProof->laundry) {
            \Log::error('Payment proof has no associated laundry', [
                'payment_proof_id' => $paymentProof->id,
                'laundry_id' => $paymentProof->laundry_id,
            ]);
            abort(403, 'Payment proof has no associated laundry order.');
        }

        // Get branch_id based on user type
        $branchId = null;
        if ($user instanceof \App\Models\Branch) {
            // Branch login
            $branchId = $user->id;
        } elseif ($user instanceof \App\Models\User) {
            // Regular user (admin/staff)
            if ($user->role === 'admin') {
                // Admin can access all branches
                return;
            }
            $branchId = $user->branch_id;
        }

        // Check if user has branch access
        if (!$branchId) {
            \Log::error('User has no branch access', [
                'user_id' => $user->id,
                'user_type' => get_class($user),
            ]);
            abort(403, 'Your account is not assigned to any branch. Please contact administrator.');
        }

        // Check if laundry belongs to user's branch
        if ($paymentProof->laundry->branch_id !== $branchId) {
            \Log::warning('Branch access denied for payment proof', [
                'payment_proof_id' => $paymentProof->id,
                'laundry_branch_id' => $paymentProof->laundry->branch_id,
                'user_branch_id' => $branchId,
                'user_id' => $user->id,
            ]);
            abort(403, 'This payment proof belongs to a different branch.');
        }
    }
    public function index(Request $request)
    {
        // Get authenticated user (could be branch or regular user)
        $user = Auth::guard('branch')->user() ?? Auth::user();

        // Get branch_id based on user type
        $branchId = null;
        if ($user instanceof \App\Models\Branch) {
            $branchId = $user->id;
        } elseif ($user instanceof \App\Models\User && $user->role === 'admin') {
            // Admin can see all payment proofs
            $branchId = null;
        } elseif ($user instanceof \App\Models\User) {
            $branchId = $user->branch_id;
        }

        $staff = $user;

        // Base query for the staff's branch only
        $query = PaymentProof::with(['laundry.customer', 'laundry.branch', 'verifiedBy']);

        if ($branchId) {
            $query->whereHas('laundry', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $query->orderBy('created_at', 'desc');

        // Get statistics for the staff's branch
        $statsQuery = PaymentProof::query();
        if ($branchId) {
            $statsQuery->whereHas('laundry', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $stats = [
            'pending' => (clone $statsQuery)->where('status', 'pending')->count(),
            'approved' => (clone $statsQuery)->where('status', 'approved')->count(),
            'rejected' => (clone $statsQuery)->where('status', 'rejected')->count(),
            'total' => (clone $statsQuery)->count(),
        ];

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $paymentProofs = $query->paginate(20);

        return view('branch.payments.verification.index', compact('paymentProofs', 'stats'));
    }

    public function show(PaymentProof $paymentProof)
    {
        // Verify branch access
        $this->verifyBranchAccess($paymentProof);

        // Load additional relationships
        $paymentProof->load(['laundry.customer', 'laundry.branch', 'verifiedBy']);

        return view('branch.payments.verification.show', compact('paymentProof'));
    }

    public function approve(Request $request, PaymentProof $paymentProof)
    {
        // Verify branch access
        $this->verifyBranchAccess($paymentProof);

        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        // Get the authenticated user (for verified_by)
        $user = Auth::guard('branch')->user() ?? Auth::user();
        $verifiedById = null;

        if ($user instanceof \App\Models\User) {
            $verifiedById = $user->id;
        }

        $paymentProof->update([
            'status' => 'approved',
            'admin_notes' => $request->admin_notes,
            'verified_at' => now(),
            'verified_by' => $verifiedById
        ]);

        // Update laundry payment status
        $paymentProof->laundry->update([
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'paid_at' => now()
        ]);

        // Update laundry status if it's ready
        if ($paymentProof->laundry->status === 'ready') {
            // Only pass User model to updateStatus, not Branch
            $changedBy = ($user instanceof \App\Models\User) ? $user : null;
            $paymentProof->laundry->updateStatus('paid', $changedBy, 'Payment verified via GCash');
        }

        // Send notification to customer
        NotificationService::notifyPaymentApproved(
            $paymentProof->laundry,
            $request->admin_notes
        );

        return redirect()->back()->with('success', 'Payment approved successfully');
    }

    public function reject(Request $request, PaymentProof $paymentProof)
    {
        // Verify branch access
        $this->verifyBranchAccess($paymentProof);

        $request->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        // Get the authenticated user (for verified_by)
        $user = Auth::guard('branch')->user() ?? Auth::user();
        $verifiedById = null;

        if ($user instanceof \App\Models\User) {
            $verifiedById = $user->id;
        }

        $paymentProof->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'verified_at' => now(),
            'verified_by' => $verifiedById
        ]);

        // Update laundry payment status back to unpaid
        $paymentProof->laundry->update([
            'payment_status' => 'unpaid'
        ]);

        // Send rejection notification to customer
        NotificationService::notifyPaymentRejected(
            $paymentProof->laundry,
            $request->admin_notes
        );

        return redirect()->back()->with('success', 'Payment rejected and customer has been notified');
    }

    public function bulkApprove(Request $request)
    {
        // Get authenticated user (could be branch or regular user)
        $user = Auth::guard('branch')->user() ?? Auth::user();

        // Get branch_id based on user type
        $branchId = null;
        if ($user instanceof \App\Models\Branch) {
            $branchId = $user->id;
        } elseif ($user instanceof \App\Models\User && $user->role === 'admin') {
            $branchId = null;
        } elseif ($user instanceof \App\Models\User) {
            $branchId = $user->branch_id;
        }

        $staff = $user;

        $request->validate([
            'payment_proof_ids' => 'required|array',
            'payment_proof_ids.*' => 'exists:payment_proofs,id'
        ]);

        $query = PaymentProof::whereIn('id', $request->payment_proof_ids)
            ->where('status', 'pending');

        if ($branchId) {
            $query->whereHas('laundry', function($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            });
        }

        $paymentProofs = $query->get();

        // Get the authenticated user
        $user = Auth::guard('branch')->user() ?? Auth::user();
        $verifiedById = ($user instanceof \App\Models\User) ? $user->id : null;
        $changedBy = ($user instanceof \App\Models\User) ? $user : null;

        foreach ($paymentProofs as $paymentProof) {
            $paymentProof->update([
                'status' => 'approved',
                'verified_at' => now(),
                'verified_by' => $verifiedById
            ]);

            $paymentProof->laundry->update([
                'payment_status' => 'paid',
                'payment_method' => 'gcash',
                'paid_at' => now()
            ]);

            if ($paymentProof->laundry->status === 'ready') {
                $paymentProof->laundry->updateStatus('paid', $changedBy, 'Payment verified via GCash (bulk approval)');
            }

            // Send notification to customer
            NotificationService::notifyPaymentApproved(
                $paymentProof->laundry,
                'Payment approved via bulk verification'
            );
        }

        return redirect()->back()->with('success', count($paymentProofs) . ' payments approved successfully');
    }
}
