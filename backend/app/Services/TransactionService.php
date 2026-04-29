<?php

namespace App\Services;

use App\Models\Laundry;
use App\Models\PaymentProof;
use App\Models\PaymentEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class TransactionService
{
    /**
     * Process payment proof submission with atomic transaction
     * Ensures payment status updates are consistent and not duplicated
     */
    public function processPaymentProof(Laundry $laundry, array $data): array
    {
        $maxRetries = 3;
        $attempt = 0;

        while ($attempt < $maxRetries) {
            try {
                return DB::transaction(function () use ($laundry, $data) {
                    // Lock the laundry row to prevent concurrent modifications
                    $lockedLaundry = Laundry::lockForUpdate()->findOrFail($laundry->id);

                    // Check if payment already exists (idempotency)
                    if ($lockedLaundry->payment_status === 'approved' && PaymentProof::where('laundry_id', $laundry->id)->exists()) {
                        Log::warning("Payment already processed for laundry {$laundry->id}. Returning existing record.");
                        return [
                            'success' => true,
                            'message' => 'Payment already processed',
                            'payment_proof_id' => PaymentProof::where('laundry_id', $laundry->id)->first()->id,
                            'idempotent' => true,
                        ];
                    }

                    // Create payment proof record
                    $paymentProof = PaymentProof::create([
                        'laundry_id' => $laundry->id,
                        'customer_id' => $laundry->customer_id,
                        'payment_method' => $data['payment_method'] ?? 'gcash',
                        'transaction_id' => $data['transaction_id'] ?? null,
                        'amount' => $laundry->total_amount,
                        'reference_number' => $data['reference_number'] ?? null,
                        'screenshot_path' => $data['screenshot_path'] ?? null,
                        'notes' => $data['notes'] ?? null,
                        'submitted_at' => now(),
                    ]);

                    // Create payment event for auditing
                    PaymentEvent::create([
                        'laundry_id' => $laundry->id,
                        'customer_id' => $laundry->customer_id,
                        'event_type' => 'proof_submitted',
                        'amount' => $laundry->total_amount,
                        'status' => 'pending',
                        'data' => [
                            'payment_method' => $data['payment_method'] ?? 'gcash',
                            'transaction_id' => $data['transaction_id'] ?? null,
                        ],
                    ]);

                    // Update laundry payment status to pending verification
                    $lockedLaundry->update([
                        'payment_status' => 'pending',
                        'payment_method' => $data['payment_method'] ?? 'gcash',
                        'payment_proof_id' => $paymentProof->id,
                    ]);

                    Log::info("Payment proof submitted successfully", [
                        'laundry_id' => $laundry->id,
                        'payment_proof_id' => $paymentProof->id,
                        'amount' => $laundry->total_amount,
                    ]);

                    return [
                        'success' => true,
                        'message' => 'Payment proof submitted successfully. Waiting for verification.',
                        'payment_proof_id' => $paymentProof->id,
                        'idempotent' => false,
                    ];
                }, 3); // Retry 3 times on deadlock
            } catch (\Illuminate\Database\QueryException $e) {
                if ($e->getCode() == 40001 || strpos($e->getMessage(), 'deadlock') !== false) {
                    // Deadlock detected, retry
                    $attempt++;
                    if ($attempt < $maxRetries) {
                        usleep(random_int(100000, 500000)); // Random backoff
                        Log::warning("Deadlock detected, retrying (attempt {$attempt}/{$maxRetries})");
                        continue;
                    }
                }
                throw $e;
            }
        }

        throw new Exception("Failed to process payment after {$maxRetries} attempts");
    }

    /**
     * Approve payment atomically
     */
    public function approvePayment(Laundry $laundry): array
    {
        return DB::transaction(function () use ($laundry) {
            $lockedLaundry = Laundry::lockForUpdate()->findOrFail($laundry->id);

            // Verify payment proof exists
            $paymentProof = PaymentProof::where('laundry_id', $laundry->id)
                ->where('status', '!=', 'approved')
                ->first();

            if (!$paymentProof) {
                throw new Exception("No pending payment proof found for laundry {$laundry->id}");
            }

            // Update payment proof status
            $paymentProof->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id() ?? null,
            ]);

            // Update laundry payment status
            $lockedLaundry->update([
                'payment_status' => 'approved',
                'paid_at' => now(),
            ]);

            // Create payment event
            PaymentEvent::create([
                'laundry_id' => $laundry->id,
                'customer_id' => $laundry->customer_id,
                'event_type' => 'proof_approved',
                'amount' => $laundry->total_amount,
                'status' => 'approved',
                'data' => [
                    'approved_by' => auth()->id() ?? null,
                    'approved_at' => now()->toIso8601String(),
                ],
            ]);

            Log::info("Payment approved", [
                'laundry_id' => $laundry->id,
                'amount' => $laundry->total_amount,
            ]);

            return [
                'success' => true,
                'message' => 'Payment approved successfully',
            ];
        });
    }

    /**
     * Reject payment atomically
     */
    public function rejectPayment(Laundry $laundry, string $reason = null): array
    {
        return DB::transaction(function () use ($laundry, $reason) {
            $lockedLaundry = Laundry::lockForUpdate()->findOrFail($laundry->id);

            // Verify payment proof exists
            $paymentProof = PaymentProof::where('laundry_id', $laundry->id)
                ->where('status', '!=', 'rejected')
                ->first();

            if (!$paymentProof) {
                throw new Exception("No pending payment proof found for laundry {$laundry->id}");
            }

            // Update payment proof status
            $paymentProof->update([
                'status' => 'rejected',
                'rejected_at' => now(),
                'rejected_by' => auth()->id() ?? null,
                'rejection_reason' => $reason,
            ]);

            // Update laundry payment status back to unpaid
            $lockedLaundry->update([
                'payment_status' => 'unpaid',
            ]);

            // Create payment event
            PaymentEvent::create([
                'laundry_id' => $laundry->id,
                'customer_id' => $laundry->customer_id,
                'event_type' => 'proof_rejected',
                'amount' => $laundry->total_amount,
                'status' => 'rejected',
                'data' => [
                    'rejected_by' => auth()->id() ?? null,
                    'rejected_at' => now()->toIso8601String(),
                    'reason' => $reason,
                ],
            ]);

            Log::info("Payment rejected", [
                'laundry_id' => $laundry->id,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => 'Payment rejected. Customer can resubmit payment proof.',
            ];
        });
    }

    /**
     * Get payment audit trail
     */
    public function getPaymentAuditTrail(Laundry $laundry)
    {
        return PaymentEvent::where('laundry_id', $laundry->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($event) => [
                'event_type' => $event->event_type,
                'status' => $event->status,
                'amount' => $event->amount,
                'timestamp' => $event->created_at->toIso8601String(),
                'details' => $event->data,
            ]);
    }

    /**
     * Refund payment atomically
     */
    public function refundPayment(Laundry $laundry, string $reason = null): array
    {
        return DB::transaction(function () use ($laundry, $reason) {
            $lockedLaundry = Laundry::lockForUpdate()->findOrFail($laundry->id);

            if ($lockedLaundry->payment_status !== 'approved') {
                throw new Exception("Cannot refund payment that is not approved");
            }

            // Update laundry payment status
            $lockedLaundry->update([
                'payment_status' => 'refunded',
            ]);

            // Create payment event
            PaymentEvent::create([
                'laundry_id' => $laundry->id,
                'customer_id' => $laundry->customer_id,
                'event_type' => 'refund_issued',
                'amount' => $laundry->total_amount,
                'status' => 'refunded',
                'data' => [
                    'refunded_by' => auth()->id() ?? null,
                    'refunded_at' => now()->toIso8601String(),
                    'reason' => $reason,
                ],
            ]);

            Log::info("Payment refunded", [
                'laundry_id' => $laundry->id,
                'amount' => $laundry->total_amount,
                'reason' => $reason,
            ]);

            return [
                'success' => true,
                'message' => 'Payment refunded successfully',
            ];
        });
    }
}
