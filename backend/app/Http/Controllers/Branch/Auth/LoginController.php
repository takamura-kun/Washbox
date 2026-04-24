<?php

namespace App\Http\Controllers\Branch\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Show the branch login form.
     */
    public function showLoginForm()
    {
        // Redirect if already logged in
        if (Auth::guard('branch')->check()) {
            return redirect()->route('branch.dashboard');
        }

        return view('branch.auth.login');
    }

    /**
     * Handle branch login request.
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');
        $remember = $request->filled('remember');

        // Attempt to login
        if (Auth::guard('branch')->attempt($credentials, $remember)) {
            $request->session()->regenerate();

            // Update last login timestamp
            $branch = Auth::guard('branch')->user();
            $branch->updateLastLogin();

            return redirect()->intended(route('branch.dashboard'))
                ->with('success', "Welcome back, {$branch->name}!");
        }

        throw ValidationException::withMessages([
            'username' => ['The provided credentials do not match our records.'],
        ]);
    }

    /**
     * Handle branch logout request.
     */
    public function logout(Request $request)
    {
        Auth::guard('branch')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('branch.login')
            ->with('success', 'You have been logged out successfully.');
    }
}
