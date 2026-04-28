<?php

namespace App\Observers;

use App\Models\User;
use App\Models\AdminNotification;
use App\Models\BranchNotification;
use App\Models\ActivityLog;
use App\Models\DeletedRecord;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Only notify for staff members
        if ($user->role === 'staff' && $user->branch_id) {
            ActivityLog::log('created', "Staff member {$user->name} added to {$user->branch->name}", 'staff', $user, [
                'role'   => $user->role,
                'branch' => $user->branch->name,
            ], $user->branch_id);

            // 🔔 NOTIFY ADMIN: New staff member
            AdminNotification::create([
                'type' => 'new_staff',
                'title' => 'New Staff Member',
                'message' => "{$user->name} has been added to the team at {$user->branch->name}",
                'icon' => 'person-plus-fill',
                'color' => 'success',
                'link' => route('admin.staff.show', $user->id),
                'data' => [
                    'user_id' => $user->id,
                    'staff_name' => $user->name,
                    'branch_name' => $user->branch->name,
                ],
                'branch_id' => $user->branch_id,
            ]);

            // 🔔 NOTIFY BRANCH: New staff member
            BranchNotification::create([
                'branch_id' => $user->branch_id,
                'type' => 'new_staff',
                'title' => 'New Team Member',
                'message' => "{$user->name} has joined your branch",
                'icon' => 'person-plus-fill',
                'color' => 'success',
                'link' => route('branch.staff.show', $user->id),
            ]);
        }
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if staff status changed to inactive
        if ($user->isDirty('is_active') && !$user->is_active && $user->role === 'staff') {
            ActivityLog::log('deactivated', "Staff member {$user->name} was deactivated", 'staff', $user, [], $user->branch_id);

            // 🔔 NOTIFY ADMIN: Staff deactivated
            AdminNotification::create([
                'type' => 'staff_deactivated',
                'title' => 'Staff Member Deactivated',
                'message' => "{$user->name} has been deactivated",
                'icon' => 'person-dash',
                'color' => 'warning',
                'link' => route('admin.staff.show', $user->id),
                'branch_id' => $user->branch_id,
            ]);

            // 🔔 NOTIFY BRANCH: Staff deactivated
            if ($user->branch_id) {
                BranchNotification::create([
                    'branch_id' => $user->branch_id,
                    'type' => 'staff_deactivated',
                    'title' => 'Staff Member Deactivated',
                    'message' => "{$user->name} has been deactivated",
                    'icon' => 'person-dash',
                    'color' => 'warning',
                    'link' => route('branch.staff.show', $user->id),
                ]);
            }
        }

        // Check if staff was transferred to another branch
        if ($user->isDirty('branch_id') && $user->role === 'staff') {
            $oldBranchId = $user->getOriginal('branch_id');
            $newBranchId = $user->branch_id;

            ActivityLog::log('transferred', "Staff member {$user->name} transferred to {$user->branch->name}", 'staff', $user, [
                'from_branch_id' => $oldBranchId,
                'to_branch_id'   => $newBranchId,
                'to_branch'      => $user->branch->name,
            ], $newBranchId);

            // 🔔 NOTIFY ADMIN: Staff transferred
            AdminNotification::create([
                'type' => 'staff_transferred',
                'title' => 'Staff Transfer',
                'message' => "{$user->name} has been transferred to {$user->branch->name}",
                'icon' => 'arrow-left-right',
                'color' => 'info',
                'link' => route('admin.staff.show', $user->id),
                'branch_id' => $newBranchId,
            ]);

            // 🔔 NOTIFY OLD BRANCH: Staff leaving
            if ($oldBranchId) {
                BranchNotification::create([
                    'branch_id' => $oldBranchId,
                    'type' => 'staff_leaving',
                    'title' => 'Staff Transfer Out',
                    'message' => "{$user->name} has been transferred to another branch",
                    'icon' => 'arrow-right',
                    'color' => 'warning',
                    'link' => route('branch.dashboard'),
                ]);
            }

            // 🔔 NOTIFY NEW BRANCH: Staff joining
            if ($newBranchId) {
                BranchNotification::create([
                    'branch_id' => $newBranchId,
                    'type' => 'staff_joining',
                    'title' => 'New Team Member',
                    'message' => "{$user->name} has been transferred to your branch",
                    'icon' => 'arrow-left',
                    'color' => 'success',
                    'link' => route('branch.staff.show', $user->id),
                ]);
            }
        }
    }

    public function deleting(User $user): void
    {
        DeletedRecord::snapshot($user, 'staff');
        ActivityLog::log('deleted', "Staff member {$user->name} deleted", 'staff', null, [
            'role'   => $user->role,
            'branch' => $user->branch?->name,
        ], $user->branch_id);
    }
}
