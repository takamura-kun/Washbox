<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentProof;
use App\Models\Laundry;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentVerificationController extends Controller
{
    public function index(Request $request)
    {
        $query = PaymentProof::with(['laundry.customer', 'laundry.branch', 'verifiedBy'])
            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->whereHas('laundry', function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            });
        }

        $paymentProofs = $query->paginate(20);

        return view('admin.payments.verification.index', compact('paymentProofs'));
    }

    public function show(PaymentProof $paymentProof)
    {
        $paymentProof->load(['laundry.customer', 'laundry.branch', 'verifiedBy']);
        
        return view('admin.payments.verification.show', compact('paymentProof'));
    }

    public function approve(Request $request, PaymentProof $paymentProof)
    {
        $request->validate([
            'admin_notes' => 'nullable|string|max:500'
        ]);

        $paymentProof->update([
            'status' => 'approved',
            'admin_notes' => $request->admin_notes,
            'verified_at' => now(),
            'verified_by' => auth()->id()
        ]);

        // Update laundry payment status
        $paymentProof->laundry->update([
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
            'paid_at' => now()
        ]);

        // Update laundry status if it's ready
        if ($paymentProof->laundry->status === 'ready') {
            $paymentProof->laundry->updateStatus('paid', auth()->user(), 'Payment verified via GCash');
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
        $request->validate([
            'admin_notes' => 'required|string|max:500'
        ]);

        $paymentProof->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'verified_at' => now(),
            'verified_by' => auth()->id()
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
        $request->validate([
            'payment_proof_ids' => 'required|array',
            'payment_proof_ids.*' => 'exists:payment_proofs,id'
        ]);

        $paymentProofs = PaymentProof::whereIn('id', $request->payment_proof_ids)
            ->where('status', 'pending')
            ->get();

        foreach ($paymentProofs as $paymentProof) {
            $paymentProof->update([
                'status' => 'approved',
                'verified_at' => now(),
                'verified_by' => auth()->id()
            ]);

            $paymentProof->laundry->update([
                'payment_status' => 'paid',
                'payment_method' => 'gcash',
                'paid_at' => now()
            ]);

            if ($paymentProof->laundry->status === 'ready') {
                $paymentProof->laundry->updateStatus('paid', auth()->user(), 'Payment verified via GCash (bulk approval)');
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