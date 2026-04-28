<?php

namespace App\Http\Controllers\Branch;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SettingsController extends Controller
{
    /**
     * Display settings page.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            return view('branch.settings.index', compact('user'));

        } catch (\Exception $e) {
            Log::error('Staff Settings Index Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error loading settings: ' . $e->getMessage());
        }
    }

    /**
     * Update general settings.
     */
    public function update(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
            ]);

            $user->update($validated);

            return redirect()->route('branch.settings.index')
                ->with('success', 'Settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Staff Settings Update Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating settings: ' . $e->getMessage());
        }
    }

    /**
     * Display account settings page.
     */
    public function account()
    {
        try {
            $user = Auth::user();
            return view('branch.settings.account', compact('user'));

        } catch (\Exception $e) {
            Log::error('Staff Account Settings Error: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Error loading account settings: ' . $e->getMessage());
        }
    }

    /**
     * Update account settings.
     */
    public function updateAccount(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'email',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:255',
            ]);

            $user->update($validated);

            return redirect()->route('branch.settings.account')
                ->with('success', 'Account updated successfully!');

        } catch (\Exception $e) {
            Log::error('Staff Account Update Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating account: ' . $e->getMessage());
        }
    }

    /**
     * Display profile page.
     */
    public function profile()
    {
        $user = Auth::user();
        
        // Get staff count for the branch
        $staffCount = 0;
        if ($user->branch_id) {
            $staffCount = User::where('branch_id', $user->branch_id)
                ->where('role', 'staff')
                ->where('is_active', true)
                ->count();
        }
        
        return view('branch.profile.index', compact('user', 'staffCount'));
    }

    /**
     * Update profile.
     */
    public function updateProfile(Request $request)
    {
        try {
            $user = Auth::user();

            $validated = $request->validate([
                'phone' => 'nullable|string|max:20',
            ]);

            $user->update($validated);

            return redirect()->route('branch.profile')
                ->with('success', 'Phone number updated successfully!');

        } catch (\Exception $e) {
            Log::error('Staff Profile Update Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating profile: ' . $e->getMessage());
        }
    }

    /**
     * Update password.
     */
    public function updatePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = Auth::user();
            
            // Check if current password is correct
            if (!Hash::check($validated['current_password'], $user->password)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'Current password is incorrect.');
            }

            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()->route('branch.profile')
                ->with('success', 'Password updated successfully!');

        } catch (\Exception $e) {
            Log::error('Staff Password Update Error: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating password: ' . $e->getMessage());
        }
    }

    public function extraServices()
    {
        $extraServices = \App\Models\ExtraServiceSetting::orderBy('display_order')->get();
        return view('branch.settings.extra-services', compact('extraServices'));
    }

    public function updateExtraServices(Request $request)
    {
        $validated = $request->validate([
            'services' => 'required|array',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.display_order' => 'nullable|integer|min:0',
            'services.*.is_active' => 'nullable|boolean',
        ]);

        foreach ($validated['services'] as $id => $data) {
            \App\Models\ExtraServiceSetting::where('id', $id)->update([
                'price' => $data['price'],
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => isset($data['is_active']) ? true : false,
            ]);
        }

        return redirect()->route('branch.settings.extra-services')
            ->with('success', 'Extra services settings updated successfully!');
    }
}
