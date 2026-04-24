<?php

namespace App\Observers;

use App\Models\PickupRequest;
use App\Models\Notification;
use App\Models\AdminNotification;
use App\Models\BranchNotification;

class PickupRequestObserver
{
   /**
 * Handle "created" event - Customer submits pickup request
 */
public function created(PickupRequest $pickupRequest): void
{
    $pickupRequest->loadMissing('customer', 'branch');

    // 🔔 NOTIFY ADMIN: New pickup request
    AdminNotification::create([
        'type' => 'pickup_request',
        'title' => 'New Pickup Request',
        'message' => "Customer {$pickupRequest->customer->name} requested pickup at {$pickupRequest->pickup_address}",
        'icon' => 'truck',
        'color' => 'info',
        'link' => route('admin.pickups.show', $pickupRequest->id),
        'data' => [
            'pickup_request_id' => $pickupRequest->id,
            'customer_id' => $pickupRequest->customer_id,
            'customer_name' => $pickupRequest->customer->name,
            'customer_phone' => $pickupRequest->customer->phone ?? null,
            'pickup_address' => $pickupRequest->pickup_address,
            'preferred_date' => $pickupRequest->preferred_date?->format('Y-m-d'),
            'preferred_time' => $pickupRequest->preferred_time,
        ],
        'branch_id' => $pickupRequest->branch_id,
    ]);

    // 🔔 NOTIFY BRANCH: New pickup request
    if ($pickupRequest->branch_id) {
        BranchNotification::create([
            'branch_id' => $pickupRequest->branch_id,
            'type' => 'pickup_request',
            'title' => 'New Pickup Request',
            'message' => "Customer {$pickupRequest->customer->name} requested pickup at {$pickupRequest->pickup_address}",
            'icon' => 'truck',
            'color' => 'info',
            'link' => route('branch.pickups.show', $pickupRequest->id),
            'data' => [
                'pickup_request_id' => $pickupRequest->id,
                'customer_name' => $pickupRequest->customer->name,
                'pickup_address' => $pickupRequest->pickup_address,
                'preferred_date' => $pickupRequest->preferred_date?->format('Y-m-d'),
            ],
        ]);
    }

    // 🔔 NOTIFY STAFF IN BRANCH: New pickup request (using UserNotification)
    if ($pickupRequest->branch_id) {
        $staffUsers = \App\Models\User::where('branch_id', $pickupRequest->branch_id)
            ->where('role', 'staff')
            ->where('is_active', true)
            ->get();

        foreach ($staffUsers as $staff) {
            \App\Models\UserNotification::create([
                'user_id' => $staff->id,
                'type' => 'pickup_request',
                'title' => 'New Pickup Request',
                'message' => "Customer {$pickupRequest->customer->name} requested pickup at {$pickupRequest->pickup_address}",
                'icon' => 'truck',
                'color' => 'info',
                'link' => route('branch.pickups.show', $pickupRequest->id),
                'data' => [
                    'pickup_request_id' => $pickupRequest->id,
                    'customer_name' => $pickupRequest->customer->name,
                    'pickup_address' => $pickupRequest->pickup_address,
                    'preferred_date' => $pickupRequest->preferred_date?->format('Y-m-d'),
                ],
            ]);
        }
    }

    // 🔔 NOTIFY CUSTOMER: Request submitted
    Notification::create([
        'customer_id' => $pickupRequest->customer_id,
        'type' => 'pickup_submitted',
        'title' => 'Pickup Request Submitted! 📬',
        'body' => "Your pickup request for {$pickupRequest->preferred_date->format('M d, Y')} has been submitted. We'll confirm shortly!",
        'pickup_request_id' => $pickupRequest->id,
        'is_read' => false,
    ]);
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

        switch ($newStatus) {
            case 'accepted':
                // Customer notification
                Notification::create([
                    'customer_id' => $pickupRequest->customer_id,
                    'type' => 'pickup_accepted',
                    'title' => 'Pickup Confirmed! ✅',
                    'body' => "Great news! Your pickup for {$pickupRequest->preferred_date->format('M d, Y')} has been confirmed.",
                    'pickup_request_id' => $pickupRequest->id,
                    'is_read' => false,
                ]);

                // Branch notification
                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type' => 'pickup_accepted',
                        'title' => 'Pickup Accepted',
                        'message' => "Pickup request from {$pickupRequest->customer->name} has been accepted",
                        'icon' => 'check-circle',
                        'color' => 'success',
                        'link' => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;

            case 'en_route':
                $staffName = $pickupRequest->assignedStaff?->name ?? 'Our rider';
                
                // Customer notification
                Notification::create([
                    'customer_id' => $pickupRequest->customer_id,
                    'type' => 'pickup_en_route',
                    'title' => 'Rider On The Way! 🚚',
                    'body' => "{$staffName} is heading to your location. Please prepare your laundry!",
                    'pickup_request_id' => $pickupRequest->id,
                    'is_read' => false,
                ]);

                // Branch notification
                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type' => 'pickup_en_route',
                        'title' => 'Pickup En Route',
                        'message' => "{$staffName} is on the way to pickup from {$pickupRequest->customer->name}",
                        'icon' => 'truck',
                        'color' => 'primary',
                        'link' => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;

            case 'picked_up':
                // Customer notification
                Notification::create([
                    'customer_id' => $pickupRequest->customer_id,
                    'type' => 'pickup_completed',
                    'title' => 'Laundry Picked Up! 🧺',
                    'body' => "Your laundry has been collected! We'll notify you when it's ready.",
                    'pickup_request_id' => $pickupRequest->id,
                    'is_read' => false,
                ]);

                // Admin notification
                AdminNotification::create([
                    'type' => 'pickup_completed',
                    'title' => 'Pickup Completed',
                    'message' => "Pickup from {$pickupRequest->customer->name} collected successfully",
                    'icon' => 'check-circle',
                    'color' => 'success',
                    'link' => route('admin.pickups.show', $pickupRequest->id),
                    'branch_id' => $pickupRequest->branch_id,
                ]);

                // Branch notification
                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type' => 'pickup_completed',
                        'title' => 'Pickup Completed',
                        'message' => "Pickup from {$pickupRequest->customer->name} collected successfully",
                        'icon' => 'check-circle',
                        'color' => 'success',
                        'link' => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;

            case 'cancelled':
                $reason = $pickupRequest->cancellation_reason ?? 'No reason provided';
                
                // Customer notification
                Notification::create([
                    'customer_id' => $pickupRequest->customer_id,
                    'type' => 'pickup_cancelled',
                    'title' => 'Pickup Cancelled ❌',
                    'body' => "Your pickup request has been cancelled. Reason: {$reason}",
                    'pickup_request_id' => $pickupRequest->id,
                    'is_read' => false,
                ]);

                // Admin notification
                AdminNotification::create([
                    'type' => 'pickup_cancelled',
                    'title' => 'Pickup Cancelled',
                    'message' => "Pickup from {$pickupRequest->customer->name} was cancelled",
                    'icon' => 'x-circle',
                    'color' => 'danger',
                    'link' => route('admin.pickups.show', $pickupRequest->id),
                    'branch_id' => $pickupRequest->branch_id,
                ]);

                // Branch notification
                if ($pickupRequest->branch_id) {
                    BranchNotification::create([
                        'branch_id' => $pickupRequest->branch_id,
                        'type' => 'pickup_cancelled',
                        'title' => 'Pickup Cancelled',
                        'message' => "Pickup from {$pickupRequest->customer->name} was cancelled - {$reason}",
                        'icon' => 'x-circle',
                        'color' => 'danger',
                        'link' => route('branch.pickups.show', $pickupRequest->id),
                    ]);
                }
                break;
        }
    }
}
