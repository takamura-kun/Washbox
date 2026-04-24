<?php

namespace App\Services;

use App\Models\Laundry;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class LaundryNotificationService
{
    protected FirebaseNotificationService $fcm;

    public function __construct(FirebaseNotificationService $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * Call this whenever a laundry's status changes.
     *
     * Mapped to your actual statuses:
     *   received → processing → ready → paid → completed
     *                                        ↘ cancelled
     *
     * Usage:
     *   app(LaundryNotificationService::class)->onStatusChange($laundry, 'ready');
     */
    public function onStatusChange(Laundry $laundry, string $newStatus): void
    {
        if (!SystemSetting::get('enable_push_notifications', true)) return;

        // Skip if no customer
        if (!$laundry->customer_id) {
            Log::info("No customer for Laundry #{$laundry->id}, skipping notification");
            return;
        }

        $tracking = $laundry->tracking_number ?? "#{$laundry->id}";
        $branch   = optional($laundry->branch)->name ?? 'our branch';

        // Send FCM push notification
        $token = optional($laundry->customer)->fcm_token;
        if (!empty($token)) {
            match ($newStatus) {
                'received'   => $this->fcm->notifyOrderReceived($token, $tracking, $branch),
                'ready'      => $this->fcm->notifyLaundryReady($token, $tracking, $branch),
                'paid'       => $this->notifyPaymentReceived($token, $tracking),
                'completed'  => $this->fcm->notifyOrderCompleted($token, $tracking),
                'cancelled'  => $this->notifyCancelled($token, $tracking),
                default      => null, // 'processing' — no notification needed
            };
        }

        // Create database notification record for mobile app notifications screen
        $this->createDatabaseNotification($laundry, $newStatus, $tracking, $branch);
    }

    /**
     * Create database notification record
     */
    private function createDatabaseNotification(Laundry $laundry, string $status, string $tracking, string $branch): void
    {
        $notificationData = match ($status) {
            'received' => [
                'type' => 'laundry_received',
                'title' => 'Laundry Received',
                'body' => "Your laundry #{$tracking} has been received at {$branch} and is being processed.",
            ],
            'processing' => [
                'type' => 'laundry_processing',
                'title' => 'Laundry Being Processed',
                'body' => "Your laundry #{$tracking} is now being processed.",
            ],
            'ready' => [
                'type' => 'laundry_ready',
                'title' => '✅ Laundry Ready!',
                'body' => "Your laundry #{$tracking} is clean and ready for pickup at {$branch}.",
            ],
            'paid' => [
                'type' => 'payment_received',
                'title' => '💳 Payment Confirmed!',
                'body' => "Payment for Laundry #{$tracking} has been received. Thank you!",
            ],
            'completed' => [
                'type' => 'laundry_completed',
                'title' => '🎉 Order Completed!',
                'body' => "Order #{$tracking} has been completed. Thank you for choosing WashBox!",
            ],
            'cancelled' => [
                'type' => 'laundry_cancelled',
                'title' => '❌ Order Cancelled',
                'body' => "Laundry #{$tracking} has been cancelled. Please contact us if this was a mistake.",
            ],
            default => null,
        };

        if ($notificationData) {
            NotificationService::sendToCustomer(
                $laundry->customer_id,
                $notificationData['type'],
                $notificationData['title'],
                $notificationData['body'],
                $laundry->id,
                null,
                [
                    'laundry_id' => $laundry->id,
                    'tracking_number' => $tracking,
                    'status' => $status,
                ]
            );
        }
    }

    /**
     * Payment confirmed notification
     */
    private function notifyPaymentReceived(string $token, string $tracking): bool
    {
        return $this->fcm->sendToDevice(
            $token,
            '💳 Payment Confirmed!',
            "Payment for Laundry $tracking has been received. Thank you!",
            ['tracking' => $tracking, 'type' => 'payment_received']
        );
    }

    /**
     * Cancellation notification
     */
    private function notifyCancelled(string $token, string $tracking): bool
    {
        return $this->fcm->sendToDevice(
            $token,
            '❌ Order Cancelled',
            "Laundry $tracking has been cancelled. Please contact us if this was a mistake.",
            ['tracking' => $tracking, 'type' => 'cancelled']
        );
    }

    /**
     * Run daily via Laravel Scheduler for unclaimed reminders.
     * Fires at Day 3, 5, and 7 based on system settings toggles.
     * "Unclaimed" = status is 'ready' but customer hasn't picked up yet.
     */
    public function sendUnclaimedReminders(): void
    {
        if (!SystemSetting::get('enable_push_notifications', true)) return;

        $reminderDays = collect([3, 5, 7])->filter(
            fn($day) => SystemSetting::get("reminder_day_$day", false)
        );

        if ($reminderDays->isEmpty()) return;

        Laundry::where('status', 'ready')
            ->whereNotNull('ready_at')
            ->with('customer')
            ->get()
            ->each(function (Laundry $laundry) use ($reminderDays) {
                $daysUnclaimed = now()->diffInDays($laundry->ready_at);
                $token = optional($laundry->customer)->fcm_token;

                if (!$token) return;

                if ($reminderDays->contains($daysUnclaimed)) {
                    $this->fcm->notifyUnclaimedReminder(
                        $token,
                        $laundry->tracking_number ?? $laundry->id,
                        $daysUnclaimed
                    );
                }
            });
    }
}
