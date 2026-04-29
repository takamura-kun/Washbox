<?php

namespace App\Services;

use App\Models\Laundry;
use App\Models\PickupRequest;
use App\Models\Notification;
use App\Models\DeviceToken;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;

class NotificationManager
{
    /**
     * Send notification when laundry is created
     */
    public function sendLaundryCreated(Laundry $laundry)
    {
        try {
            $laundry->load(['customer', 'branch', 'service']);
            
            // Create database notification
            Notification::create([
                'notifiable_type' => get_class($laundry->customer),
                'notifiable_id' => $laundry->customer_id,
                'type' => 'LaundryCreated',
                'data' => [
                    'laundry_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'branch_name' => $laundry->branch->name,
                    'service_name' => $laundry->service->name,
                    'amount' => $laundry->total_amount,
                ],
            ]);

            // Send FCM notification
            $this->sendFCMNotification(
                $laundry->customer,
                'Laundry Received',
                "Your laundry order #{$laundry->tracking_number} has been received at {$laundry->branch->name}",
                ['type' => 'laundry_received', 'laundry_id' => $laundry->id]
            );

            Log::info("Laundry created notification sent: {$laundry->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send laundry created notification: {$e->getMessage()}");
        }
    }

    /**
     * Send notification when laundry status changes
     */
    public function sendLaundryStatusChanged(Laundry $laundry, string $oldStatus, string $newStatus)
    {
        try {
            $laundry->load(['customer', 'branch']);
            $statusMessages = [
                'received' => 'Your laundry has been received and is ready for processing',
                'processing' => 'Your laundry is now being processed',
                'washing' => 'Your laundry is being washed',
                'drying' => 'Your laundry is being dried',
                'ironing' => 'Your laundry is being ironed',
                'ready' => 'Your laundry is ready for pickup or delivery',
                'completed' => 'Your laundry has been completed',
                'cancelled' => 'Your laundry has been cancelled',
            ];

            // Create database notification
            Notification::create([
                'notifiable_type' => get_class($laundry->customer),
                'notifiable_id' => $laundry->customer_id,
                'type' => 'LaundryStatusChanged',
                'data' => [
                    'laundry_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
            ]);

            // Send FCM notification
            $this->sendFCMNotification(
                $laundry->customer,
                "Laundry {$newStatus}",
                $statusMessages[$newStatus] ?? "Your laundry status has been updated to {$newStatus}",
                ['type' => "laundry_{$newStatus}", 'laundry_id' => $laundry->id]
            );

            Log::info("Laundry status changed notification sent: {$laundry->id} ({$oldStatus} -> {$newStatus})");
        } catch (\Exception $e) {
            Log::error("Failed to send laundry status notification: {$e->getMessage()}");
        }
    }

    /**
     * Send notification when payment status changes
     */
    public function sendPaymentStatusChanged(Laundry $laundry, string $oldStatus, string $newStatus)
    {
        try {
            $laundry->load(['customer', 'branch']);
            $statusMessages = [
                'unpaid' => 'Payment pending for your laundry order',
                'pending' => 'Payment is pending verification',
                'approved' => 'Payment has been received and approved',
                'rejected' => 'Payment was rejected. Please try again or contact support',
                'refunded' => 'Your payment has been refunded',
            ];

            // Create database notification
            Notification::create([
                'notifiable_type' => get_class($laundry->customer),
                'notifiable_id' => $laundry->customer_id,
                'type' => 'PaymentStatusChanged',
                'data' => [
                    'laundry_id' => $laundry->id,
                    'tracking_number' => $laundry->tracking_number,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
            ]);

            // Send FCM notification
            $this->sendFCMNotification(
                $laundry->customer,
                "Payment {$newStatus}",
                $statusMessages[$newStatus] ?? "Your payment status has been updated to {$newStatus}",
                ['type' => "payment_{$newStatus}", 'laundry_id' => $laundry->id]
            );

            Log::info("Payment status changed notification sent: {$laundry->id} ({$oldStatus} -> {$newStatus})");
        } catch (\Exception $e) {
            Log::error("Failed to send payment status notification: {$e->getMessage()}");
        }
    }

    /**
     * Send notification when pickup request is created
     */
    public function sendPickupCreated(PickupRequest $pickup)
    {
        try {
            $pickup->load(['customer', 'branch']);
            
            // Create database notification
            Notification::create([
                'notifiable_type' => get_class($pickup->customer),
                'notifiable_id' => $pickup->customer_id,
                'type' => 'PickupCreated',
                'data' => [
                    'pickup_id' => $pickup->id,
                    'branch_name' => $pickup->branch->name,
                    'preferred_date' => $pickup->preferred_date,
                    'preferred_time' => $pickup->preferred_time,
                ],
            ]);

            // Send FCM notification
            $this->sendFCMNotification(
                $pickup->customer,
                'Pickup Request Submitted',
                "Your pickup request for {$pickup->preferred_date->format('M d, Y')} has been submitted to {$pickup->branch->name}",
                ['type' => 'pickup_submitted', 'pickup_id' => $pickup->id]
            );

            Log::info("Pickup created notification sent: {$pickup->id}");
        } catch (\Exception $e) {
            Log::error("Failed to send pickup created notification: {$e->getMessage()}");
        }
    }

    /**
     * Send notification when pickup status changes
     */
    public function sendPickupStatusChanged(PickupRequest $pickup, string $oldStatus, string $newStatus)
    {
        try {
            $pickup->load(['customer', 'branch']);
            $statusMessages = [
                'pending' => 'Your pickup request has been submitted and is waiting for acceptance',
                'accepted' => 'Your pickup request has been accepted',
                'en_route' => 'Our driver is on the way to pick up your laundry',
                'picked_up' => 'Your laundry has been picked up',
                'cancelled' => 'Your pickup request has been cancelled',
                'completed' => 'Your pickup has been completed',
            ];

            // Create database notification
            Notification::create([
                'notifiable_type' => get_class($pickup->customer),
                'notifiable_id' => $pickup->customer_id,
                'type' => 'PickupStatusChanged',
                'data' => [
                    'pickup_id' => $pickup->id,
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                ],
            ]);

            // Send FCM notification
            $this->sendFCMNotification(
                $pickup->customer,
                "Pickup {$newStatus}",
                $statusMessages[$newStatus] ?? "Your pickup status has been updated to {$newStatus}",
                ['type' => "pickup_{$newStatus}", 'pickup_id' => $pickup->id]
            );

            Log::info("Pickup status changed notification sent: {$pickup->id} ({$oldStatus} -> {$newStatus})");
        } catch (\Exception $e) {
            Log::error("Failed to send pickup status notification: {$e->getMessage()}");
        }
    }

    /**
     * Send FCM push notification to customer
     */
    private function sendFCMNotification($customer, string $title, string $message, array $data = [])
    {
        try {
            // Get active FCM tokens for the customer
            $tokens = DeviceToken::where('customer_id', $customer->id)
                ->where('is_active', true)
                ->pluck('token')
                ->toArray();

            if (empty($tokens)) {
                Log::warning("No active FCM tokens for customer {$customer->id}");
                return;
            }

            // Queue FCM sending for background processing
            // This prevents blocking the main request
            foreach (array_chunk($tokens, 500) as $tokenChunk) {
                $this->dispatchFCMNotification($title, $message, $data, $tokenChunk);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send FCM notification: {$e->getMessage()}");
        }
    }

    /**
     * Dispatch FCM notification (can be queued or sent immediately)
     */
    private function dispatchFCMNotification(string $title, string $message, array $data, array $tokens)
    {
        // TODO: Implement Firebase Cloud Messaging integration
        // For now, just log that the notification would be sent
        Log::info("FCM Notification would be sent", [
            'title' => $title,
            'message' => $message,
            'tokens_count' => count($tokens),
            'data' => $data,
        ]);
    }

    /**
     * Retry failed notifications
     */
    public function retryFailedNotifications()
    {
        try {
            // TODO: Query failed notification records and retry
            Log::info("Retrying failed notifications");
        } catch (\Exception $e) {
            Log::error("Failed to retry notifications: {$e->getMessage()}");
        }
    }
}
