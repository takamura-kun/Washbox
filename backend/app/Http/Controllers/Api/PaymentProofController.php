<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Laundry;
use App\Models\PaymentProof;
use App\Services\SecureFileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

    public function store(Request $request, $laundryId)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0',
            'reference_number' => 'nullable|string|max:255',
            'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $laundry = $this->resolveLaundryOrFail((string) $laundryId);

        // Check if laundry belongs to authenticated customer
        if (!$customer || $laundry->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this laundry'
            ], 403);
        }

        // Check if payment is already completed
        if ($laundry->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Payment already completed for this laundry'
            ], 400);
        }

        // Store the proof image securely
        try {
            $uploadResult = SecureFileUploadService::uploadImage(
                $request->file('proof_image'),
                'payment-proofs'
            );
            $filename = $uploadResult['filename'];
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'File upload failed: ' . $e->getMessage(),
            ], 400);
        }

        // Create payment proof record
        $paymentProof = PaymentProof::create([
            'laundry_id' => $laundry->id,
            'payment_method' => 'gcash',
            'amount' => $request->amount,
            'reference_number' => $request->reference_number,
            'proof_image' => $filename,
            'status' => 'pending'
        ]);

        // Update laundry payment status to pending verification
        $laundry->update(['payment_status' => 'pending_verification']);

        return response()->json([
            'success' => true,
            'message' => 'Payment proof submitted successfully. Please wait for admin verification.',
            'data' => [
                'payment_proof_id' => $paymentProof->id,
                'status' => $paymentProof->status,
                'submitted_at' => $paymentProof->created_at
            ]
        ]);
    }

    public function show(Request $request, $laundryId)
    {
        $customer = $request->user();
        $laundry = $this->resolveLaundryOrFail((string) $laundryId);

        // Check if laundry belongs to authenticated customer
        if (!$customer || $laundry->customer_id !== $customer->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access to this laundry'
            ], 403);
        }

        $paymentProof = $laundry->latestPaymentProof;

        if (!$paymentProof) {
            return response()->json([
                'success' => false,
                'message' => 'No payment proof found for this laundry'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $paymentProof->id,
                'amount' => $paymentProof->amount,
                'reference_number' => $paymentProof->reference_number,
                'status' => $paymentProof->status,
                'admin_notes' => $paymentProof->admin_notes,
                'submitted_at' => $paymentProof->created_at,
                'verified_at' => $paymentProof->verified_at
            ]
        ]);
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
