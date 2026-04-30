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

        if (!$laundry->customer_id) {
            Log::info("No customer for Laundry #{$laundry->id}, skipping notification");
            return;
        }

        $tracking = $laundry->tracking_number ?? "#{$laundry->id}";
        $branch   = optional($laundry->branch)->name ?? 'our branch';

        // createDatabaseNotification also sends FCM via NotificationService::sendToCustomer
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
                'title' => '📦 Laundry Received',
                'body' => "Your laundry #{$tracking} has been received at {$branch} and is being processed.",
            ],
            'processing' => [
                'type' => 'laundry_processing',
                'title' => '🔄 Laundry Being Processed',
                'body' => "Your laundry #{$tracking} is now being washed and cared for.",
            ],
            'ready' => [
                'type' => 'laundry_ready',
                'title' => '✅ Laundry Ready!',
                'body' => "Your laundry #{$tracking} is clean and ready for pickup at {$branch}.",
            ],
            'out_for_delivery' => [
                'type' => 'delivery_en_route',
                'title' => '🚚 Out for Delivery!',
                'body' => "Your laundry #{$tracking} is on its way to you!",
            ],
            'delivered' => [
                'type' => 'delivery_completed',
                'title' => '🏠 Laundry Delivered!',
                'body' => "Your laundry #{$tracking} has been delivered. Please check and confirm.",
            ],
            'paid' => [
                'type' => 'payment_received',
                'title' => '💳 Payment Confirmed!',
                'body' => "Payment for laundry #{$tracking} has been confirmed. Thank you!",
            ],
            'completed' => [
                'type' => 'laundry_completed',
                'title' => '🎉 Laundry Completed!',
                'body' => "Laundry #{$tracking} is done! Thank you for choosing WashBox!",
            ],
            'cancelled' => [
                'type' => 'laundry_cancelled',
                'title' => '❌ Laundry Cancelled',
                'body' => "Laundry #{$tracking} has been cancelled. Contact us if this was a mistake.",
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
                    'laundries_id'   => (string) $laundry->id,
                    'laundry_id'     => (string) $laundry->id,
                    'tracking_number' => $tracking,
                    'status'         => $status,
                ]
            );
        }
    }

    /**
     * Payment confirmed notification
     */
    private function notifyPaymentReceived(string $token, string $tracking, int $laundryId): bool
    {
        return $this->fcm->sendToDevice(
            $token,
            '💳 Payment Confirmed!',
            "Payment for Laundry $tracking has been received. Thank you!",
            ['laundries_id' => (string) $laundryId, 'laundry_id' => (string) $laundryId, 'tracking_number' => $tracking, 'type' => 'payment_received']
        );
    }

    /**
     * Cancellation notification
     */
    private function notifyCancelled(string $token, string $tracking, int $laundryId): bool
    {
        return $this->fcm->sendToDevice(
            $token,
            '❌ Order Cancelled',
            "Laundry $tracking has been cancelled. Please contact us if this was a mistake.",
            ['laundries_id' => (string) $laundryId, 'laundry_id' => (string) $laundryId, 'tracking_number' => $tracking, 'type' => 'laundry_cancelled']
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
                        $laundry->id,
                        $daysUnclaimed
                    );
                }
            });
    }
}
