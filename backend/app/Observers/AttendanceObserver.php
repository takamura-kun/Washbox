<?php

namespace App\Observers;

use App\Models\Attendance;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class AttendanceObserver
{
    /**
     * Handle the Attendance "created" event.
     */
    public function created(Attendance $attendance): void
    {
        $attendance->loadMissing('user', 'user.branch');

        // Only notify for late arrivals or absences
        if ($attendance->status === 'late') {
            // 🔔 NOTIFY ADMIN: Late arrival
            AdminNotification::create([
                'type' => 'attendance_late',
                'title' => 'Late Arrival',
                'message' => "{$attendance->user->name} arrived late at {$attendance->time_in->format('h:i A')}",
                'icon' => 'clock',
                'color' => 'warning',
                'link' => route('admin.staff.attendance.index'),
                'data' => [
                    'attendance_id' => $attendance->id,
                    'staff_name' => $attendance->user->name,
                    'time_in' => $attendance->time_in->format('H:i:s'),
                ],
                'branch_id' => $attendance->user->branch_id,
            ]);

            // 🔔 NOTIFY BRANCH: Late arrival
            if ($attendance->user->branch_id) {
                BranchNotification::create([
                    'branch_id' => $attendance->user->branch_id,
                    'type' => 'attendance_late',
                    'title' => 'Late Arrival',
                    'message' => "{$attendance->user->name} arrived late at {$attendance->time_in->format('h:i A')}",
                    'icon' => 'clock',
                    'color' => 'warning',
                    'link' => route('branch.dashboard'),
                ]);
            }
        }

        if ($attendance->status === 'absent') {
            // 🔔 NOTIFY ADMIN: Absence
            AdminNotification::create([
                'type' => 'attendance_absent',
                'title' => 'Staff Absence',
                'message' => "{$attendance->user->name} is marked absent for {$attendance->date->format('M d, Y')}",
                'icon' => 'person-x',
                'color' => 'danger',
                'link' => route('admin.staff.attendance.index'),
                'data' => [
                    'attendance_id' => $attendance->id,
                    'staff_name' => $attendance->user->name,
                    'date' => $attendance->date->format('Y-m-d'),
                ],
                'branch_id' => $attendance->user->branch_id,
            ]);

            // 🔔 NOTIFY BRANCH: Absence
            if ($attendance->user->branch_id) {
                BranchNotification::create([
                    'branch_id' => $attendance->user->branch_id,
                    'type' => 'attendance_absent',
                    'title' => 'Staff Absence',
                    'message' => "{$attendance->user->name} is absent today",
                    'icon' => 'person-x',
                    'color' => 'danger',
                    'link' => route('branch.dashboard'),
                ]);
            }
        }
    }
}
