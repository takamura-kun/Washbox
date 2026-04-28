<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use App\Mail\TwoFactorCode;

class TwoFactorService
{
    /**
     * Generate 6-digit 2FA code
     */
    public static function generateCode(): string
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Send 2FA code via email
     */
    public static function sendCode($user, string $code): void
    {
        Mail::to($user->email)->send(new TwoFactorCode($user, $code));
    }

    /**
     * Verify 2FA code
     */
    public static function verifyCode($user, string $code): bool
    {
        // Check if code is expired (10 minutes) — check first to avoid timing leak
        if (!$user->two_factor_expires_at || now()->isAfter($user->two_factor_expires_at)) {
            return false;
        }

        // Use hash_equals to prevent timing attacks
        if (!hash_equals((string) $user->two_factor_code, $code)) {
            return false;
        }

        return true;
    }

    /**
     * Clear 2FA code after successful verification
     */
    public static function clearCode($user): void
    {
        $user->update([
            'two_factor_code' => null,
            'two_factor_expires_at' => null,
        ]);
    }
}
