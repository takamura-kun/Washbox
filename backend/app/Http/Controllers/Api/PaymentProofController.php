<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\PaymentProof;
use App\Services\SecureFileUploadService;
use App\Services\NotificationManager;
use App\Services\TransactionService;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PaymentProofController extends Controller
{
    private function resolveLaundryOrFail(string $laundryIdOrTracking): Laundry
    {
        return Laundry::query()
            ->where('tracking_number', $laundryIdOrTracking)
            ->orWhere('id', $laundryIdOrTracking)
            ->firstOrFail();
    }

    public function store(Request $request, $laundryId, TransactionService $transactionService)
    {
        try {
            $customer = $request->user();

            // Validate input
            $validated = $request->validate([
                'reference_number' => 'nullable|string|max:255',
                'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
                'notes' => 'nullable|string|max:500',
            ]);

            // Find laundry
            $laundry = Laundry::where(function ($query) use ($laundryId) {
                $query->where('tracking_number', $laundryId)
                      ->orWhere('id', $laundryId);
            })->first();

            if (!$laundry) {
                throw new ResourceNotFoundException('Laundry', $laundryId);
            }

            // Verify ownership
            if ($laundry->customer_id !== $customer->id) {
                throw new UnauthorizedException('You do not have access to this laundry');
            }

            // Check if payment is already approved
            if ($laundry->payment_status === 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment already approved for this laundry',
                    'code' => 'PAYMENT_ALREADY_APPROVED'
                ], 400);
            }

            // Upload proof image securely
            try {
                $uploadResult = SecureFileUploadService::uploadImage(
                    $request->file('proof_image'),
                    'payment-proofs'
                );
                $validated['screenshot_path'] = $uploadResult['filename'];
            } catch (\Exception $e) {
                Log::error("Payment proof upload failed: {$e->getMessage()}", [
                    'laundry_id' => $laundry->id,
                    'customer_id' => $customer->id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed. Please try again.',
                    'code' => 'FILE_UPLOAD_FAILED'
                ], 400);
            }

            // Process payment with atomic transaction and idempotency
            $result = $transactionService->processPaymentProof($laundry, [
                'payment_method' => 'gcash',
                'transaction_id' => $validated['reference_number'] ?? null,
                'reference_number' => $validated['reference_number'] ?? null,
                'screenshot_path' => $validated['screenshot_path'],
                'notes' => $validated['notes'] ?? null,
            ]);

            // Send notification to customer
            $notificationManager = new NotificationManager();
            $notificationManager->sendPaymentStatusChanged($laundry, $laundry->payment_status, 'pending');

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'code' => 'PAYMENT_SUBMITTED',
                'data' => [
                    'payment_proof_id' => $result['payment_proof_id'],
                    'status' => 'pending',
                    'submitted_at' => now()->toIso8601String(),
                    'idempotent' => $result['idempotent'] ?? false,
                ]
            ], 201);

        } catch (ResourceNotFoundException $e) {
            return $e->render();
        } catch (UnauthorizedException $e) {
            return $e->render();
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'code' => 'VALIDATION_ERROR',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error("Payment proof submission failed: {$e->getMessage()}", [
                'laundry_id' => $laundryId,
                'customer_id' => $customer->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit payment proof. Please try again later.',
                'code' => 'PAYMENT_SUBMISSION_FAILED'
            ], 500);
        }
    }

    public function show(Request $request, $laundryId)
    {
        try {
            $customer = $request->user();
            
            // Find laundry
            $laundry = Laundry::where(function ($query) use ($laundryId) {
                $query->where('tracking_number', $laundryId)
                      ->orWhere('id', $laundryId);
            })->first();

            if (!$laundry) {
                throw new ResourceNotFoundException('Laundry', $laundryId);
            }

            // Verify ownership
            if ($laundry->customer_id !== $customer->id) {
                throw new UnauthorizedException('You do not have access to this laundry');
            }

            // Get latest payment proof
            $paymentProof = PaymentProof::where('laundry_id', $laundry->id)
                ->latest('created_at')
                ->first();

            if (!$paymentProof) {
                return response()->json([
                    'success' => false,
                    'message' => 'No payment proof found for this laundry',
                    'code' => 'NO_PAYMENT_PROOF'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $paymentProof->id,
                    'laundry_id' => $paymentProof->laundry_id,
                    'amount' => (float) $paymentProof->amount,
                    'reference_number' => $paymentProof->reference_number,
                    'payment_method' => $paymentProof->payment_method,
                    'status' => $paymentProof->status,
                    'notes' => $paymentProof->notes,
                    'submitted_at' => $paymentProof->created_at->toIso8601String(),
                    'approved_at' => $paymentProof->approved_at?->toIso8601String(),
                    'rejected_at' => $paymentProof->rejected_at?->toIso8601String(),
                ]
            ]);

        } catch (ResourceNotFoundException $e) {
            return $e->render();
        } catch (UnauthorizedException $e) {
            return $e->render();
        } catch (\Exception $e) {
            Log::error("Failed to fetch payment proof: {$e->getMessage()}", [
                'laundry_id' => $laundryId,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment proof',
                'code' => 'FETCH_FAILED'
            ], 500);
        }
    }

    public function getGCashQR($branchId = null)
    {
        $branch = null;
        if ($branchId) {
            $branch = \App\Models\Branch::find($branchId);
        }

        // Use branch-specific QR if available, otherwise use default
        $qrCodeUrl = $branch && $branch->gcash_qr_image 
            ? url('storage/gcash-qr/' . $branch->gcash_qr_image)
            : url('images/gcash-qr-placeholder.png');
            
        $accountName = $branch && $branch->gcash_account_name 
            ? $branch->gcash_account_name 
            : 'WashBox Laundry Services';
            
        $accountNumber = $branch && $branch->gcash_account_number 
            ? $branch->gcash_account_number 
            : '09123456789';

        // Log the generated URL for debugging
        \Log::info('Generated QR Code URL', [
            'branch_id' => $branchId,
            'branch_name' => $branch ? $branch->name : 'Default',
            'has_custom_qr' => $branch && $branch->gcash_qr_image ? true : false,
            'generated_url' => $qrCodeUrl
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'qr_code_url' => $qrCodeUrl,
                'account_name' => $accountName,
                'account_number' => $accountNumber,
                'branch_name' => $branch ? $branch->name : 'Default Branch',
                'has_custom_qr' => $branch && $branch->gcash_qr_image ? true : false,
                'instructions' => [
                    'Scan the QR code using your GCash app',
                    'Enter the exact amount shown in your laundry details',
                    'Complete the payment',
                    'Take a screenshot of the payment confirmation',
                    'Upload the screenshot as proof of payment'
                ]
            ]
        ]);
    }
}
