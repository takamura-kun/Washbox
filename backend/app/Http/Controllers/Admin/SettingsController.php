<?php

namespace App\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class SettingsController extends Controller
{
    /**
     * Display the settings dashboard grouped by category.
     * $health is passed here so the Blade view never needs to call the controller directly.
     */
    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group');
        $health   = $this->systemStatus();
        return view('admin.settings.index', compact('settings', 'health'));
    }

    /**
     * Update global business rules and branding
     */
    public function update(Request $request)
    {
        // 1. Image Upload for Branding (App Logo)
        if ($request->hasFile('app_logo')) {
            $path = $request->file('app_logo')->store('settings', 'public');
            SystemSetting::set('app_logo', $path, 'string', 'general');
        }

        // 2. Boolean/checkbox keys — must be handled BEFORE the main loop because
        //    unchecked checkboxes are NOT submitted by the browser, so they would
        //    never appear in $request->all() and could never be set to false otherwise.
        $booleanKeys = [
            // Notifications
            'enable_push_notifications',
            'notify_laundry_received',
            'notify_laundry_ready',
            'notify_laundry_completed',
            'notify_unclaimed',
            'reminder_day_3',
            'reminder_day_5',
            'reminder_day_7',
            // Pickup & Delivery
            'enable_pickup',
            'enable_delivery',
            'require_customer_proof_photo',
            // Receipt options
            'receipt_show_branch',
            'receipt_show_staff',
            // Business Hours (open/closed per day)
            'hours_monday_open',
            'hours_tuesday_open',
            'hours_wednesday_open',
            'hours_thursday_open',
            'hours_friday_open',
            'hours_saturday_open',
            'hours_sunday_open',
            // Unclaimed notifications
            'enable_unclaimed_notifications',
            // Maintenance Mode
            'maintenance_mode',
            'maintenance_allow_admin',
        ];

        foreach ($booleanKeys as $key) {
            $group = str_starts_with($key, 'hours_')      ? 'hours'
                   : (str_starts_with($key, 'enable_pickup') || str_starts_with($key, 'enable_delivery') ? 'pickup'
                   : (str_starts_with($key, 'receipt_')     ? 'receipt'
                   : (str_starts_with($key, 'enable_unclaimed') ? 'general'
                   : (str_starts_with($key, 'maintenance_') ? 'maintenance'
                   : 'notifications'))));

            SystemSetting::set($key, $request->has($key) ? '1' : '0', 'boolean', $group);
        }

        // 3. Process all remaining text and numeric inputs (skip booleans already handled)
        $skipKeys = array_merge(['_token', '_method', 'app_logo'], $booleanKeys);
        $data = $request->except($skipKeys);

        // Group map for non-boolean keys
        $groupMap = [
            'shop_name'                => 'general',
            'contact_number'           => 'general',
            'business_email'           => 'general',
            'default_price_per_piece'     => 'pricing',
            'default_price_per_load'   => 'pricing',
            'minimum_order_amount'     => 'pricing',
            'storage_fee_per_day'      => 'pricing',
            'storage_grace_period_days'=> 'pricing',
            'vat_rate'                 => 'pricing',
            'vat_inclusive'            => 'pricing',
            'default_pickup_fee'       => 'pickup',
            'default_delivery_fee'     => 'pickup',
            'max_service_radius_km'    => 'pickup',
            'pickup_advance_days_min'  => 'pickup',
            'pickup_advance_days_max'  => 'pickup',
            'max_pickup_requests_per_day' => 'pickup',
            'tracking_prefix'          => 'receipt',
            'business_email'           => 'receipt',
            'receipt_header'           => 'receipt',
            'receipt_footer'           => 'receipt',
            'receipt_claim_reminder'   => 'receipt',
            'disposal_threshold_days'  => 'general',
            'fcm_server_key'           => 'notifications',
            'fcm_sender_id'            => 'notifications',
            'maintenance_message'      => 'maintenance',
            'maintenance_start'        => 'maintenance',
            'maintenance_end'          => 'maintenance',
        ];

        foreach ($data as $key => $value) {
            // Determine type
            if (is_numeric($value)) {
                $type = 'integer';
            } else {
                $type = 'string';
            }

            // Determine group
            $group = $groupMap[$key]
                ?? (str_starts_with($key, 'hours_') ? 'hours' : 'general');

            SystemSetting::set($key, $value, $type, $group);
        }

        // Auto-sync operating hours to all branches when hours are updated
        $this->autoSyncHoursToBranches();

        // 4. Handle pickup/delivery service toggles - update system-wide availability
        $this->updateServiceAvailability($request);

        return redirect()->back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Generate a new database SQL dump (Objective C.5)
     */
    public function backup()
    {
        try {
            $filename = "backup-" . now()->format('Y-m-d-H-i-s') . ".sql";
            $path = storage_path('app/backups/' . $filename);

            if (!File::exists(storage_path('app/backups'))) {
                File::makeDirectory(storage_path('app/backups'), 0755, true);
            }

            // escapeshellarg() prevents shell injection if credentials contain
            // special characters like $, !, spaces, or semicolons.
            $command = sprintf(
                'mysqldump --user=%s --password=%s --host=%s %s > %s',
                escapeshellarg(config('database.connections.mysql.username')),
                escapeshellarg(config('database.connections.mysql.password')),
                escapeshellarg(config('database.connections.mysql.host')),
                escapeshellarg(config('database.connections.mysql.database')),
                escapeshellarg($path)
            );

            exec($command);

            return response()->json(['success' => true, 'message' => 'Backup created: ' . $filename]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Download an existing backup file
     */
    public function downloadBackup($filename)
    {
        // Sanitize filename to prevent path traversal attacks
        $filename = basename($filename);
        if (!preg_match('/^backup-[\d\-]+\.sql$/', $filename)) {
            abort(400, 'Invalid backup filename.');
        }
        $path = storage_path('app/backups/' . $filename);
        if (File::exists($path)) {
            return response()->download($path);
        }
        return redirect()->back()->with('error', 'File not found.');
    }

    /**
     * Automatic Cleanup: Delete backups older than 30 days
     */
    public function cleanupBackups()
    {
        try {
            $path = storage_path('app/backups');

            if (!File::exists($path)) {
                return response()->json(['success' => true, 'message' => 'Backup folder does not exist.']);
            }

            $files = File::files($path);
            $deletedCount = 0;
            $now = Carbon::now();

            foreach ($files as $file) {
                $lastModified = Carbon::createFromTimestamp($file->getMTime());

                if ($lastModified->diffInDays($now) > 30) {
                    File::delete($file->getPathname());
                    $deletedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Cleanup complete. Removed $deletedCount old backup(s)."
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Show the user's personal profile
     */
    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile.index', compact('user'));
    }

    /**
     * Update Admin/Staff personal details
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        User::find($user->id)->update($request->only('name', 'email', 'phone'));

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Securely update account password
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user(); // was missing — caused ErrorException on every call

        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        User::find($user->id)->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->back()->with('success', 'Password changed successfully.');
    }

    /**
     * Show the staff member's restricted profile
     */
    public function staffProfile()
    {
        $user = Auth::user();
        return view('staff.profile.index', compact('user'));
    }

    /**
     * Update staff profile (restricted to phone only)
     */
    public function staffUpdateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'phone' => ['nullable', 'string', 'max:20'],
        ]);

        // Only update phone; name and email are protected
        $user->update($request->only('phone'));

        return redirect()->back()->with('success', 'Profile updated successfully.');
    }
    /**
     * Check the health status of system integrations
     */
    public function systemStatus()
    {
        $status = [
            'database' => false,
            'storage' => false,
            'fcm' => false,
            'last_backup' => 'Never'
        ];

        // 1. Check Database Connection
        try {
            DB::connection()->getPdo();
            $status['database'] = true;
        } catch (\Exception $e) {
        }

        // 2. Check Storage Permissions
        // Create the backups directory if it doesn't exist yet (e.g. fresh install),
        // otherwise is_writable() returns false and shows a misleading error.
        $backupDir = storage_path('app/backups');
        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true);
        }
        $status['storage'] = is_writable(storage_path('app/public')) && is_writable($backupDir);

        // 3. Check FCM Configuration (V1 API — uses service account file, not a server key)
        $status['fcm'] = File::exists(storage_path('app/firebase/service-account.json'));

        // 4. Get Last Backup Date
        $backupPath = storage_path('app/backups');
        if (File::exists($backupPath)) {
            $files = File::files($backupPath);
            if (count($files) > 0) {
                $latest = collect($files)->sortByDesc(fn($f) => $f->getMTime())->first();
                $status['last_backup'] = date('Y-m-d H:i', $latest->getMTime());
            }
        }

        return $status;
    }
    /**
     * Update FCM specific settings via AJAX
     */
    public function updateFCM(Request $request)
    {
        $request->validate([
            'fcm_server_key' => 'required|string',
            'fcm_sender_id' => 'required|string',
        ]);

        \App\Models\SystemSetting::set('fcm_server_key', $request->fcm_server_key, 'string', 'notifications');
        \App\Models\SystemSetting::set('fcm_sender_id', $request->fcm_sender_id, 'string', 'notifications');

        return response()->json(['success' => true, 'message' => 'FCM credentials updated successfully.']);
    }

    /**
     * Update Notification & FCM Settings
     */
    public function updateNotifications(Request $request)
    {
        $request->validate([
            'fcm_server_key' => 'nullable|string',
            'fcm_sender_id' => 'nullable|string',
        ]);

        // Save API Keys
        \App\Models\SystemSetting::set('fcm_server_key', $request->fcm_server_key, 'string', 'notifications');
        \App\Models\SystemSetting::set('fcm_sender_id', $request->fcm_sender_id, 'string', 'notifications');

        // Save Toggles (Laundry status alerts)
        $toggles = [
            'notify_laundry_ready',
            'notify_laundry_completed',
            'notify_unclaimed_reminder'
        ];

        foreach ($toggles as $key) {
            \App\Models\SystemSetting::set($key, $request->has($key) ? '1' : '0', 'boolean', 'notifications');
        }

        return redirect()->back()->with('success', 'Notification settings updated successfully.');
    }

    /**
     * Apply global operating hours to all branches
     */
    public function applyHoursToBranches(Request $request)
    {
        try {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $operatingHours = [];

            foreach ($days as $day) {
                $isOpen = SystemSetting::get("hours_{$day}_open", true);
                $operatingHours[$day] = $isOpen
                    ? ['open' => SystemSetting::get("hours_{$day}_start", '07:00'), 'close' => SystemSetting::get("hours_{$day}_end", '20:00'), 'status' => 'open']
                    : 'closed';
            }

            $applyAll  = $request->boolean('apply_all', true);
            $branchIds = $request->input('branch_ids', []);

            $query = \App\Models\Branch::query();

            if (!$applyAll && !empty($branchIds)) {
                $query->whereIn('id', $branchIds);
            }

            $updatedCount = $query->update(['operating_hours' => $operatingHours]);

            $label = (!$applyAll && !empty($branchIds)) ? $updatedCount . ' selected branch(es)' : $updatedCount . ' branch(es)';

            return response()->json([
                'success' => true,
                'message' => "Operating hours applied to {$label} successfully."
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error applying hours to branches: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Update service availability based on pickup/delivery settings
     */
    private function updateServiceAvailability(Request $request)
    {
        try {
            $enablePickup = $request->has('enable_pickup');
            $enableDelivery = $request->has('enable_delivery');

            // Check if pickup/delivery fees have changed
            $oldPickupFee = SystemSetting::get('default_pickup_fee', 50);
            $oldDeliveryFee = SystemSetting::get('default_delivery_fee', 50);
            $newPickupFee = $request->input('default_pickup_fee', $oldPickupFee);
            $newDeliveryFee = $request->input('default_delivery_fee', $oldDeliveryFee);

            $feesChanged = ($oldPickupFee != $newPickupFee) || ($oldDeliveryFee != $newDeliveryFee);

            // Check if service pricing has changed
            $oldPricePerPiece = SystemSetting::get('default_price_per_piece', 60);
            $oldPricePerLoad = SystemSetting::get('default_price_per_load', 120);
            $newPricePerPiece = $request->input('default_price_per_piece', $oldPricePerPiece);
            $newPricePerLoad = $request->input('default_price_per_load', $oldPricePerLoad);

            $servicePricingChanged = ($oldPricePerPiece != $newPricePerPiece) || ($oldPricePerLoad != $newPricePerLoad);

            // Update all branch fees if default fees changed
            if ($feesChanged) {
                $updatedBranches = \App\Models\DeliveryFee::updateAllBranchFeesFromSettings();
                \Log::info('Updated branch fees from settings', [
                    'old_pickup_fee' => $oldPickupFee,
                    'new_pickup_fee' => $newPickupFee,
                    'old_delivery_fee' => $oldDeliveryFee,
                    'new_delivery_fee' => $newDeliveryFee,
                    'branches_updated' => $updatedBranches,
                    'updated_by' => auth()->user()->name ?? 'System'
                ]);
            }

            // Update service prices for services with null prices
            if ($servicePricingChanged) {
                $serviceUpdates = \App\Models\Service::updateNullPricesFromSettings();
                \Log::info('Updated service prices from settings', [
                    'old_price_per_piece' => $oldPricePerPiece,
                    'new_price_per_piece' => $newPricePerPiece,
                    'old_price_per_load' => $oldPricePerLoad,
                    'new_price_per_load' => $newPricePerLoad,
                    'services_updated' => $serviceUpdates,
                    'updated_by' => auth()->user()->name ?? 'System'
                ]);
            }

            // Log the service availability changes
            \Log::info('Service availability updated', [
                'pickup_enabled' => $enablePickup,
                'delivery_enabled' => $enableDelivery,
                'fees_updated' => $feesChanged,
                'service_pricing_updated' => $servicePricingChanged,
                'updated_by' => auth()->user()->name ?? 'System'
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update service availability: ' . $e->getMessage());
        }
    }

    /**
     * Automatically sync operating hours to all branches (called internally)
     */
    private function autoSyncHoursToBranches()
    {
        try {
            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
            $operatingHours = [];

            // Build operating hours array from system settings
            foreach ($days as $day) {
                $isOpen = SystemSetting::get("hours_{$day}_open", true);
                
                if ($isOpen) {
                    $operatingHours[$day] = [
                        'open' => SystemSetting::get("hours_{$day}_start", '07:00'),
                        'close' => SystemSetting::get("hours_{$day}_end", '20:00'),
                        'status' => 'open'
                    ];
                } else {
                    $operatingHours[$day] = 'closed';
                }
            }

            // Update all branches with the new operating hours
            \App\Models\Branch::query()->update([
                'operating_hours' => $operatingHours
            ]);

        } catch (\Exception $e) {
            // Log error but don't break the settings update
            \Log::error('Failed to auto-sync hours to branches: ' . $e->getMessage());
        }
    }
}
