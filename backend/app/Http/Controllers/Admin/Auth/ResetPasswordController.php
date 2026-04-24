<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{
    // Validation configuration constants
    private const MIN_PASSWORD_LENGTH = 8;
    private const REMEMBER_TOKEN_LENGTH = 60;
    
    /**
     * Show reset password form
     */
    public function showResetForm(Request $request, string $token)
    {
        return view('admin.auth.reset-password', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle password reset
     */
    public function reset(Request $request)
    {
        $validationRules = [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:' . self::MIN_PASSWORD_LENGTH . '|confirmed',
        ];
        
        $request->validate($validationRules);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(self::REMEMBER_TOKEN_LENGTH));

                $user->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('admin.login')->with('success', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }
}
