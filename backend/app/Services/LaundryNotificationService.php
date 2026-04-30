<?php

namespace App\Services;

use App\Models\Laundry;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Log;

class LaundryNotificationService
{

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

        $laundry->loadMissing(['customer', 'branch', 'pickupRequest']);
        $tracking       = $laundry->tracking_number ?? "#{$laundry->id}";
        $branch         = optional($laundry->branch)->name ?? 'our branch';
        $isPickupOrigin = (bool) $laundry->pickup_request_id;

        // sendToCustomer handles both DB record + FCM push in one call
        $this->createDatabaseNotification($laundry, $newStatus, $tracking, $branch, $isPickupOrigin);
    }

    /**
     * Create database notification record
     */
    private function createDatabaseNotification(Laundry $laundry, string $status, string $tracking, string $branch, bool $isPickupOrigin = false): void
    {
        $notificationData = match ($status) {
            'received' => [
                'type'  => 'laundry_received',
                'title' => '📦 Laundry Received',
                'body'  => $isPickupOrigin
                    ? "Your laundry #{$tracking} has arrived at {$branch} and is being prepared."
                    : "Your laundry #{$tracking} has been received at {$branch} and is being processed.",
            ],
            'processing' => [
                'type'  => 'laundry_processing',
                'title' => '⚙️ Laundry Being Processed',
                'body'  => "Your laundry #{$tracking} is now being washed and cared for.",
            ],
            'ready' => [
                'type'  => 'laundry_ready',
                'title' => $isPickupOrigin ? '🚚 Ready for Delivery!' : '✅ Laundry Ready!',
                'body'  => $isPickupOrigin
                    ? "Your laundry #{$tracking} is clean and will be delivered back to you soon."
                    : "Your laundry #{$tracking} is clean and ready for pickup at {$branch}.",
            ],
            'out_for_delivery' => [
                'type'  => 'delivery_en_route',
                'title' => '🚚 Out for Delivery!',
                'body'  => "Your laundry #{$tracking} is on its way back to you!",
            ],
            'delivered' => [
                'type'  => 'delivery_completed',
                'title' => '🏠 Laundry Delivered!',
                'body'  => "Your laundry #{$tracking} has been delivered. Please check and confirm receipt.",
            ],
            'paid' => [
                'type'  => 'payment_received',
                'title' => '💳 Payment Confirmed!',
                'body'  => "Payment for Laundry #{$tracking} has been received. Thank you!",
            ],
            'completed' => [
                'type'  => 'laundry_completed',
                'title' => '🎉 Laundry Completed!',
                'body'  => "Laundry #{$tracking} has been completed. Thank you for choosing WashBox!",
            ],
            'cancelled' => [
                'type'  => 'laundry_cancelled',
                'title' => '❌ Laundry Cancelled',
                'body'  => "Laundry #{$tracking} has been cancelled. Please contact us if this was a mistake.",
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
                $laundry->pickup_request_id,
                [
                    'laundry_id'      => $laundry->id,
                    'tracking_number' => $tracking,
                    'status'          => $status,
                ]
            );
        }
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
                if (!$laundry->customer_id) return;

                $daysUnclaimed = now()->diffInDays($laundry->ready_at);

                if ($reminderDays->contains($daysUnclaimed)) {
                    $tracking = $laundry->tracking_number ?? "#{$laundry->id}";
                    $fee = SystemSetting::get('storage_fee_per_day', 5) * $daysUnclaimed;

                    NotificationService::sendToCustomer(
                        $laundry->customer_id,
                        'unclaimed_reminder',
                        '⚠️ Unclaimed Laundry Reminder',
                        "Your laundry #{$tracking} has been unclaimed for {$daysUnclaimed} day(s). Storage fee: ₱{$fee}. Please pick up soon.",
                        $laundry->id,
                        null,
                        [
                            'laundry_id'    => $laundry->id,
                            'tracking'      => $tracking,
                            'days_unclaimed'=> $daysUnclaimed,
                            'storage_fee'   => $fee,
                        ]
                    );
                }
            });
    }
}
