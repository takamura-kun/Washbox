<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Services\TwoFactorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new customer
     *
     * POST /api/v1/register
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:customers'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:20', 'unique:customers'],
            'branch_id' => ['nullable', 'exists:branches,id'], // Made optional for backward compatibility
            'preferred_branch_id' => ['nullable', 'exists:branches,id'], // Keep for backward compatibility
            'address' => ['nullable', 'string', 'max:500'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Use branch_id if provided, otherwise use preferred_branch_id
        $branchId = $request->branch_id ?? $request->preferred_branch_id;

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password, // Model will auto-hash via Attribute
            'phone' => $request->phone,
            'address' => $request->address,
            'branch_id' => $branchId,
            'preferred_branch_id' => $branchId,
            'registration_type' => 'self_registered',
            'is_active' => true,
        ]);

        $token = $customer->createToken('mobile-app')->plainTextToken;

        // Notify admins and branch staff about new customer registration
        try {
            $customerName = $customer->name;
            $branchName   = $customer->preferredBranch?->name ?? 'No branch';

            \App\Models\AdminNotification::create([
                'type'    => 'new_customer',
                'title'   => 'New Customer Registered',
                'message' => "{$customerName} has registered via the mobile app. Branch: {$branchName}.",
                'icon'    => 'person-plus-fill',
                'color'   => 'success',
                'link'    => '/admin/customers',
            ]);

            if ($customer->preferred_branch_id) {
                \App\Services\NotificationService::sendToBranchStaff(
                    $customer->preferred_branch_id,
                    'new_customer',
                    'New Customer Registered',
                    "{$customerName} has registered and selected your branch.",
                    null, null, $customer->id,
                    ['customer_id' => $customer->id, 'customer_name' => $customerName]
                );
            }
        } catch (\Exception $e) {
            \Log::warning('Failed to send new customer notification: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'branch_id' => $customer->branch_id,
                    'preferred_branch_id' => $customer->preferred_branch_id,
                ],
                'token' => $token,
            ]
        ], 201);
    }

    /**
     * Login customer
     *
     * POST /api/v1/login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'            => ['required', 'email'],
            'password'         => ['required'],
            'two_factor_code'  => ['nullable', 'string', 'size:6'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $validator->errors()
            ], 422);
        }

        // ── Rate limiting ────────────────────────────────────────────────────
        $key      = 'login:' . strtolower($request->email) . '|' . $request->ip();
        $attempts = RateLimiter::attempts($key);

        // Determine lockout duration based on attempt count
        // 3+ attempts  → 20 seconds
        // 4+ attempts  → 60 seconds
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds  = RateLimiter::availableIn($key);
            $attempts = RateLimiter::attempts($key);
            $message  = $attempts >= 4
                ? "Too many failed attempts. Please wait {$seconds} second(s) before trying again."
                : "Too many failed attempts. Please wait {$seconds} second(s) before trying again.";

            return response()->json([
                'success'          => false,
                'message'          => $message,
                'locked_out'       => true,
                'retry_after'      => $seconds,
            ], 429);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer || !Hash::check($request->password, $customer->password)) {
            $currentAttempts = RateLimiter::attempts($key); // before hit
            $decay = ($currentAttempts + 1) >= 3 ? 60 : 20;
            RateLimiter::hit($key, $decay);

            $afterAttempts = RateLimiter::attempts($key);
            $remaining     = max(0, 3 - $afterAttempts);

            return response()->json([
                'success'       => false,
                'message'       => 'Invalid credentials',
                'attempts_left' => $remaining,
            ], 401);
        }

        // Credentials valid — clear rate limiter
        RateLimiter::clear($key);

        if (!$customer->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated. Please contact support.',
            ], 403);
        }

        // Check if 2FA is enabled
        if ($customer->two_factor_enabled) {
            // If 2FA code is provided, verify it
            if ($request->has('two_factor_code')) {
                if (!TwoFactorService::verifyCode($customer, $request->two_factor_code)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid or expired 2FA code',
                    ], 401);
                }

                // Clear the code after successful verification
                TwoFactorService::clearCode($customer);
            } else {
                // Generate and send 2FA code
                $code = TwoFactorService::generateCode();
                $customer->update([
                    'two_factor_code' => $code,
                    'two_factor_expires_at' => now()->addMinutes(10),
                ]);

                TwoFactorService::sendCode($customer, $code);

                return response()->json([
                    'success' => false,
                    'message' => '2FA code sent to your email. Please check your inbox.',
                    'requires_2fa' => true,
                ], 200);
            }
        }

        // Revoke old tokens
        $customer->tokens()->delete();

        $token = $customer->createToken('mobile-app')->plainTextToken;
        
        // Load branch relationship
        $customer->load('preferredBranch');

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'branch_id' => $customer->branch_id,
                    'preferred_branch_id' => $customer->preferred_branch_id,
                    'two_factor_enabled' => $customer->two_factor_enabled,
                    'branch' => $customer->preferredBranch ? [
                        'id' => $customer->preferredBranch->id,
                        'name' => $customer->preferredBranch->name,
                        'code' => $customer->preferredBranch->code,
                        'address' => $customer->preferredBranch->address,
                        'city' => $customer->preferredBranch->city,
                        'phone' => $customer->preferredBranch->phone,
                    ] : null,
                ],
                'token' => $token,
            ]
        ]);
    }



    /**
     * Logout customer (revoke current token)
     *
     * POST /api/v1/logout
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logout failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get authenticated customer profile
     *
     * GET /api/v1/user
     */
    public function user(Request $request)
    {
        $customer = $request->user();
        
        // Load branch relationship
        $customer->load('branch');

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'branch_id' => $customer->branch_id,
                    'preferred_branch_id' => $customer->preferred_branch_id,
                    'is_active' => $customer->is_active,
                    'created_at' => $customer->created_at->format('Y-m-d H:i:s'),
                    'branch' => $customer->branch ? [
                        'id' => $customer->branch->id,
                        'name' => $customer->branch->name,
                        'code' => $customer->branch->code,
                        'address' => $customer->branch->address,
                        'city' => $customer->branch->city,
                        'phone' => $customer->branch->phone,
                    ] : null,
                ],
            ]
        ]);
    }

    /**
     * Update customer profile
     *
     * PUT /api/v1/profile
     */
    public function updateProfile(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'string', 'max:20', 'unique:customers,phone,' . $customer->id],
            'address' => ['nullable', 'string', 'max:500'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'preferred_branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only(['name', 'phone', 'address', 'branch_id', 'preferred_branch_id']);
        
        // If branch_id is updated, also update preferred_branch_id for consistency
        if (isset($updateData['branch_id'])) {
            $updateData['preferred_branch_id'] = $updateData['branch_id'];
        }

        $customer->update($updateData);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully',
            'data' => [
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'branch_id' => $customer->branch_id,
                    'preferred_branch_id' => $customer->preferred_branch_id,
                ],
            ]
        ]);
    }

    /**
     * Change customer password
     *
     * PUT /api/v1/password
     */
    public function changePassword(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->current_password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Current password is incorrect',
            ], 401);
        }

        $customer->update([
            'password' => Hash::make($request->new_password)
        ]);

        // Revoke all tokens
        $customer->tokens()->delete();

        $token = $customer->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Password changed successfully',
            'data' => [
                'token' => $token,
            ]
        ]);
    }

    /**
     * Forgot password - send reset code
     *
     * POST /api/v1/forgot-password
     */
    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $customer = Customer::where('email', $request->email)->first();

        // Always return the same response to prevent user enumeration
        if (!$customer) {
            return response()->json([
                'success' => true,
                'message' => 'If an account with that email exists, a reset code has been sent.',
            ]);
        }

        // Generate 6-digit reset code
        $resetCode = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store reset code in database (expires in 15 minutes)
        \DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => Hash::make($resetCode),
                'created_at' => now()
            ]
        );

        try {
            // Send notification
            $customer->notify(new \App\Notifications\PasswordResetNotification($resetCode));
            
            return response()->json([
                'success' => true,
                'message' => 'Password reset code sent to your email address. Please check your inbox and spam folder.',
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send password reset email: ' . $e->getMessage(), [
                'email' => $request->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In development, log the code for testing
            if (app()->environment('local')) {
                \Log::info("Password reset code for {$request->email}: {$resetCode}");
            }
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to send reset code. Please check your email configuration or try again later.',
            ], 500);
        }
    }

    /**
     * Reset password with code
     *
     * POST /api/v1/reset-password
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'code' => ['required', 'string', 'size:6'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if reset token exists and is valid (15 minutes)
        $resetRecord = \DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->where('created_at', '>=', now()->subMinutes(15))
            ->first();

        if (!$resetRecord || !Hash::check($request->code, $resetRecord->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired reset code',
            ], 400);
        }

        $customer = Customer::where('email', $request->email)->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Customer not found',
            ], 404);
        }

        // Update password
        $customer->update([
            'password' => Hash::make($request->password)
        ]);

        // Delete reset token
        \DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Revoke all tokens
        $customer->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successful. Please login with your new password.',
        ]);
    }

    /**
     * Delete customer account
     *
     * DELETE /api/v1/account
     */
    public function deleteAccount(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string'],
            'confirmation' => ['required', 'string', 'in:DELETE_MY_ACCOUNT'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verify password
        if (!Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect',
            ], 401);
        }

        try {
            \DB::beginTransaction();

            // Get customer ID before deletion
            $customerId = $customer->id;
            $customerName = $customer->name;
            $customerEmail = $customer->email;

            // Check for active laundries
            $activeLaundries = $customer->laundries()
                ->whereIn('status', ['pending', 'processing', 'ready_for_pickup'])
                ->count();

            if ($activeLaundries > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account with active laundries. Please wait for all laundries to be completed or contact support.',
                    'active_laundries_count' => $activeLaundries,
                ], 400);
            }

            // Check for pending pickup requests
            $pendingPickups = $customer->pickupRequests()
                ->whereIn('status', ['pending', 'accepted', 'en_route'])
                ->count();

            if ($pendingPickups > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete account with pending pickup requests. Please wait for all pickups to be completed or contact support.',
                    'pending_pickups_count' => $pendingPickups,
                ], 400);
            }

            // Soft delete related data (keep for audit purposes)
            // Update laundries to mark as deleted customer
            $customer->laundries()->update([
                'customer_notes' => \DB::raw("CONCAT(COALESCE(customer_notes, ''), ' [Customer account deleted]')")
            ]);

            // Update pickup requests
            $customer->pickupRequests()->update([
                'notes' => \DB::raw("CONCAT(COALESCE(notes, ''), ' [Customer account deleted]')")
            ]);

            // Delete customer ratings
            $customer->ratings()->delete();

            // Delete customer addresses
            $customer->addresses()->delete();

            // Delete payment methods
            $customer->paymentMethods()->delete();

            // Delete FCM tokens
            \DB::table('customer_fcm_tokens')->where('customer_id', $customerId)->delete();

            // Delete notification preferences
            \DB::table('customer_notification_preferences')->where('customer_id', $customerId)->delete();

            // Revoke all tokens
            $customer->tokens()->delete();

            // Delete password reset tokens
            \DB::table('password_reset_tokens')->where('email', $customerEmail)->delete();

            // Finally, delete the customer account
            $customer->delete();

            \DB::commit();

            // Log the account deletion
            \Log::info("Customer account deleted", [
                'customer_id' => $customerId,
                'customer_name' => $customerName,
                'customer_email' => $customerEmail,
                'deleted_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Your account has been permanently deleted. We\'re sorry to see you go!',
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Account deletion failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete account. Please try again or contact support.',
                'error' => app()->environment('local') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Enable 2FA for customer
     *
     * POST /api/v1/2fa/enable
     */
    public function enable2FA(Request $request)
    {
        $customer = $request->user();

        $customer->update(['two_factor_enabled' => true]);

        return response()->json([
            'success' => true,
            'message' => '2FA enabled successfully. You will receive a code via email on your next login.',
        ]);
    }

    /**
     * Disable 2FA for customer
     *
     * POST /api/v1/2fa/disable
     */
    public function disable2FA(Request $request)
    {
        $customer = $request->user();

        $validator = Validator::make($request->all(), [
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Hash::check($request->password, $customer->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Password is incorrect',
            ], 401);
        }

        $customer->update([
            'two_factor_enabled' => false,
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => '2FA disabled successfully.',
        ]);
    }



}
