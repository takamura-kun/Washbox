<?php

namespace App\Observers;

use App\Models\PickupRequest;
use App\Models\Notification;
use App\Models\AdminNotification;
use App\Models\BranchNotification;
use App\Models\ActivityLog;
use App\Services\NotificationService;

class PickupRequestObserver
{
    /**
     * Handle "created" event - Customer submits pickup request
     */
    public function created(PickupRequest $pickupRequest): void
    {
        $pickupRequest->loadMissing('customer', 'branch');

        ActivityLog::log('created', "Pickup request from {$pickupRequest->customer->name} at {$pickupRequest->pickup_address}", 'pickup', $pickupRequest, [
            'customer'       => $pickupRequest->customer->name,
            'pickup_address' => $pickupRequest->pickup_address,
            'preferred_date' => $pickupRequest->preferred_date?->format('Y-m-d'),
        ], $pickupRequest->branch_id);

        // 🔔 NOTIFY ADMIN
        AdminNotification::create([
            'type'    => 'pickup_request',
            'title'   => 'New Pickup Request',
            'message' => "Customer {$pickupRequest->customer->name} requested pickup at {$pickupRequest->pickup_address}",
            'icon'    => 'truck',
            'color'   => 'info',
            'link'    => route('admin.pickups.show', $pickupRequest->id),
            'data'    => [
                'pickup_request_id' => $pickupRequest->id,
                'customer_id'       => $pickupRequest->customer_id,
                'customer_name'     => $pickupRequest->customer->name,
                'customer_phone'    => $pickupRequest->customer->phone ?? null,
                'pickup_address'    => $pickupRequest->pickup_address,
                'preferred_date'    => $pickupRequest->preferred_date?->format('Y-m-d'),
                'preferred_time'    => $pickupRequest->preferred_time,
            ],
            'branch_id' => $pickupRequest->branch_id,
        ]);

        // 🔔 NOTIFY BRANCH
        if ($pickupRequest->branch_id) {
            BranchNotification::create([
                'branch_id' => $pickupRequest->branch_id,
                'type'      => 'pickup_request',
                'title'     => 'New Pickup Request',
                'message'   => "Customer {$pickupRequest->customer->name} requested pickup at {$pickupRequest->pickup_address}",
                'icon'      => 'truck',
                'color'     => 'info',
                'link'      => route('branch.pickups.show', $pickupRequest->id),
                'data'      => [
                    'pickup_request_id' => $pickupRequest->id,
                    'customer_name'     => $pickupRequest->customer->name,
                    'pickup_address'    => $pickupRequest->pickup_address,
                    'preferred_date'    => $pickupRequest->preferred_date?->format('Y-m-d'),
                ],
            ]);
        }

        // 🔔 NOTIFY STAFF IN BRANCH
        if ($pickupRequest->branch_id) {
            $staffUsers = \App\Models\User::where('branch_id', $pickupRequest->branch_id)
                ->where('role', 'staff')
                ->where('is_active', true)
                ->get();

            foreach ($staffUsers as $staff) {
                \App\Models\UserNotification::create([
                    'user_id' => $staff->id,
                    'type'    => 'pickup_request',
                    'title'   => 'New Pickup Request',
                    'message' => "Customer {$pickupRequest->customer->name} requested pickup at {$pickupRequest->pickup_address}",
                    'icon'    => 'truck',
                    'color'   => 'info',
                    'link'    => route('branch.pickups.show', $pickupRequest->id),
                    'data'    => [
                        'pickup_request_id' => $pickupRequest->id,
                        'customer_name'     => $pickupRequest->customer->name,
                        'pickup_address'    => $pickupRequest->pickup_address,
                        'preferred_date'    => $pickupRequest->preferred_date?->format('Y-m-d'),
                    ],
                ]);
            }
        }

        // 🔔 NOTIFY CUSTOMER: DB record + FCM via NotificationService (single call handles both)
        NotificationService::sendToCustomer(
            $pickupRequest->customer_id,
            'pickup_submitted',
            'Pickup Request Submitted! 📬',
            "Your pickup for {$pickupRequest->preferred_date->format('M d, Y')} has been submitted. We'll confirm shortly!",
            null,
            $pickupRequest->id,
            ['type' => 'pickup_submitted', 'pickup_id' => (string) $pickupRequest->id]
        );
    }

    /**
     * Handle "updated" event - Status changes
     */
    public function updated(PickupRequest $pickupRequest): void
    {
        if (!$pickupRequest->isDirty('status')) {
            return;
        }

        $pickupRequest->loadMissing('customer', 'assignedStaff', 'branch');
        $newStatus = $pickupRequest->status;

        ActivityLog::log('status_changed', "Pickup #{$pickupRequest->id} status changed to {$newStatus} for {$pickupRequest->customer->name}", 'pickup', $pickupRequest, [
            'from'     => $pickupRequest->getOriginal('status'),
            'to'       => $newStatus,
            'customer' => $pickupRequest->customer->name,
        ], $pickupRequest->branch_id);

        switch ($newStatus) {
            case 'accepted':
                // Customer: DB + FCM via NotificationService
                NotificationService::sendToCustomer(
                    $pickupRequest->customer_id,
                    'pickup_accepted',
                    'Pickup Confirmed! ✅',
                    "Great news! Your pickup for {$pickupRequest->preferred_date->format('M d, Y')} has been confirmed.",
                    null,
                    $pickupRequest->id,
                    ['type' => 'pickup_accepted', 'pickup_id' => (string) $pickupRequest->id]
                );

                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type'      => 'pickup_accepted',
                        'title'     => 'Pickup Accepted',
                        'message'   => "Pickup request from {$pickupRequest->customer->name} has been accepted",
                        'icon'      => 'check-circle',
                        'color'     => 'success',
                        'link'      => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;

            case 'en_route':
                $staffName = $pickupRequest->assignedStaff?->name ?? 'Our rider';

                NotificationService::sendToCustomer(
                    $pickupRequest->customer_id,
                    'pickup_en_route',
                    'Rider On The Way! 🚚',
                    "{$staffName} is heading to your location. Please prepare your laundry!",
                    null,
                    $pickupRequest->id,
                    ['type' => 'pickup_en_route', 'pickup_id' => (string) $pickupRequest->id]
                );

                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type'      => 'pickup_en_route',
                        'title'     => 'Pickup En Route',
                        'message'   => "{$staffName} is on the way to pickup from {$pickupRequest->customer->name}",
                        'icon'      => 'truck',
                        'color'     => 'primary',
                        'link'      => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;

            case 'picked_up':
                NotificationService::sendToCustomer(
                    $pickupRequest->customer_id,
                    'pickup_completed',
                    'Laundry Picked Up! 🧺',
                    "Your laundry has been collected! We'll notify you when it's ready.",
                    null,
                    $pickupRequest->id,
                    ['type' => 'pickup_completed', 'pickup_id' => (string) $pickupRequest->id]
                );

                AdminNotification::create([
                    'type'      => 'pickup_completed',
                    'title'     => 'Pickup Completed',
                    'message'   => "Pickup from {$pickupRequest->customer->name} collected successfully",
                    'icon'      => 'check-circle',
                    'color'     => 'success',
                    'link'      => route('admin.pickups.show', $pickupRequest->id),
                    'branch_id' => $pickupRequest->branch_id,
                ]);

                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type'      => 'pickup_completed',
                        'title'     => 'Pickup Completed',
                        'message'   => "Pickup from {$pickupRequest->customer->name} collected successfully",
                        'icon'      => 'check-circle',
                        'color'     => 'success',
                        'link'      => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;

            case 'cancelled':
                $reason = $pickupRequest->cancellation_reason ?? 'No reason provided';

                NotificationService::sendToCustomer(
                    $pickupRequest->customer_id,
                    'pickup_cancelled',
                    'Pickup Cancelled ❌',
                    "Your pickup request has been cancelled. Reason: {$reason}",
                    null,
                    $pickupRequest->id,
                    ['type' => 'pickup_cancelled', 'pickup_id' => (string) $pickupRequest->id]
                );

                AdminNotification::create([
                    'type'      => 'pickup_cancelled',
                    'title'     => 'Pickup Cancelled',
                    'message'   => "Pickup from {$pickupRequest->customer->name} was cancelled",
                    'icon'      => 'x-circle',
                    'color'     => 'danger',
                    'link'      => route('admin.pickups.show', $pickupRequest->id),
                    'branch_id' => $pickupRequest->branch_id,
                ]);

                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type'      => 'pickup_cancelled',
                        'title'     => 'Pickup Cancelled',
                        'message'   => "Pickup from {$pickupRequest->customer->name} was cancelled - {$reason}",
                        'icon'      => 'x-circle',
                        'color'     => 'danger',
                        'link'      => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;
        }
    }
}
