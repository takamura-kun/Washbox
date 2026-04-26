<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use App\Models\AdminNotification;
use App\Models\BranchNotification;
use App\Models\ActivityLog;

class LeaveRequestObserver
{
    /**
     * Handle the LeaveRequest "created" event.
     */
    public function created(LeaveRequest $leave): void
    {
        $leave->loadMissing('user', 'user.branch');

        ActivityLog::log('created', "{$leave->user->name} requested {$leave->leave_type} leave", 'attendance', $leave, [
            'leave_type' => $leave->leave_type,
            'from'       => $leave->leave_date_from->format('Y-m-d'),
            'to'         => $leave->leave_date_to->format('Y-m-d'),
        ], $leave->user->branch_id);

        // 🔔 NOTIFY ADMIN: New leave request
        AdminNotification::create([
            'type' => 'leave_request',
            'title' => 'New Leave Request',
            'message' => "{$leave->user->name} requested {$leave->leave_type} leave from {$leave->leave_date_from->format('M d')} to {$leave->leave_date_to->format('M d, Y')}",
            'icon' => 'calendar-x',
            'color' => 'info',
            'link' => route('admin.leave-requests.show', $leave->id),
            'data' => [
                'leave_id' => $leave->id,
                'staff_name' => $leave->user->name,
                'leave_type' => $leave->leave_type,
                'start_date' => $leave->leave_date_from->format('Y-m-d'),
                'end_date' => $leave->leave_date_to->format('Y-m-d'),
            ],
            'branch_id' => $leave->user->branch_id,
        ]);

        // 🔔 NOTIFY BRANCH: New leave request
        if ($leave->user->branch_id) {
            BranchNotification::create([
                'branch_id' => $leave->user->branch_id,
                'type' => 'leave_request',
                'title' => 'New Leave Request',
                'message' => "{$leave->user->name} requested {$leave->leave_type} leave",
                'icon' => 'calendar-x',
                'color' => 'info',
                'link' => route('branch.dashboard'),
            ]);
        }
    }

    /**
     * Handle the LeaveRequest "updated" event.
     */
    public function updated(LeaveRequest $leave): void
    {
        // Check if status changed
        if ($leave->isDirty('status')) {
            $leave->loadMissing('user', 'user.branch');

            ActivityLog::log('status_changed', "Leave request for {$leave->user->name} was {$leave->status}", 'attendance', $leave, [
                'from'   => $leave->getOriginal('status'),
                'to'     => $leave->status,
            ], $leave->user->branch_id);

            $status = $leave->status;
            $color = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'warning');
            $icon = $status === 'approved' ? 'check-circle' : ($status === 'rejected' ? 'x-circle' : 'clock');

            // 🔔 NOTIFY ADMIN: Leave request status changed
            AdminNotification::create([
                'type' => 'leave_' . $status,
                'title' => 'Leave Request ' . ucfirst($status),
                'message' => "{$leave->user->name}'s leave request has been {$status}",
                'icon' => $icon,
                'color' => $color,
                'link' => route('admin.leave-requests.show', $leave->id),
                'branch_id' => $leave->user->branch_id,
            ]);

            // 🔔 NOTIFY BRANCH: Leave request status changed
            if ($leave->user->branch_id) {
                BranchNotification::create([
                    'branch_id' => $leave->user->branch_id,
                    'type' => 'leave_' . $status,
                    'title' => 'Leave Request ' . ucfirst($status),
                    'message' => "{$leave->user->name}'s leave request has been {$status}",
                    'icon' => $icon,
                    'color' => $color,
                    'link' => route('branch.dashboard'),
                ]);
            }
        }
    }
}
