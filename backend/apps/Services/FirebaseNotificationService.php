<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Kreait\Firebase\Exception\MessagingException;
use Illuminate\Support\Facades\Log;
use App\Models\SystemSetting;

class FirebaseNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $factory = (new Factory)->withServiceAccount(
            storage_path('app/firebase/service-account.json')
        );

        $this->messaging = $factory->createMessaging();
    }

    /**
     * Send a notification to a single device token
     */
    public function sendToDevice(string $deviceToken, string $title, string $body, array $data = [], ?string $sound = 'default', string $channelId = 'washbox-default'): bool
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification)
                ->withData($data);

            if ($sound) {
                $message = $message->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                    'notification' => [
                        'sound' => $sound,
                        'channel_id' => $channelId,
                        'default_vibrate_timings' => true,
                        'default_light_settings' => true,
                    ]
                ]));

                $message = $message->withApnsConfig(ApnsConfig::fromArray([
                    'headers' => ['apns-priority' => '10'],
                    'payload' => [
                        'aps' => [
                            'sound' => $sound === 'default' ? 'default' : $sound . '.caf',
                            'badge' => 1,
                            'alert' => ['title' => $title, 'body' => $body],
                        ],
                    ],
                ]));
            }

            $this->messaging->send($message);
            return true;

        } catch (MessagingException $e) {
            Log::error('FCM send failed: ' . $e->getMessage(), [
                'token' => substr($deviceToken, 0, 10) . '...',
                'title' => $title,
            ]);
            return false;
        }
    }

    /**
     * Send to multiple device tokens at once
     */
    public function sendToMultiple(array $deviceTokens, string $title, string $body, array $data = [], ?string $sound = 'default', string $channelId = 'washbox-default'): void
    {
        if (empty($deviceTokens)) return;

        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::new()
                ->withNotification($notification)
                ->withData($data);

            if ($sound) {
                $message = $message->withAndroidConfig(AndroidConfig::fromArray([
                    'priority' => 'high',
                    'notification' => [
                        'sound' => $sound,
                        'channel_id' => $channelId,
                        'default_vibrate_timings' => true,
                        'default_light_settings' => true,
                    ]
                ]));

                $message = $message->withApnsConfig(ApnsConfig::fromArray([
                    'headers' => ['apns-priority' => '10'],
                    'payload' => [
                        'aps' => [
                            'sound' => $sound === 'default' ? 'default' : $sound . '.caf',
                            'badge' => 1,
                            'alert' => ['title' => $title, 'body' => $body],
                        ],
                    ],
                ]));
            }

            $this->messaging->sendMulticast($message, $deviceTokens);

        } catch (MessagingException $e) {
            Log::error('FCM multicast failed: ' . $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // WashBox Order Notification Helpers
    // -------------------------------------------------------------------------

    /**
     * Stage 1 → 2: Order received at branch
     */
    public function notifyOrderReceived(string $token, int $laundryId, string $branchName): bool
    {
        if (!SystemSetting::get('notify_laundry_received', true)) return false;
        return $this->sendToDevice($token, '📦 Laundry Received!',
            "Your laundry has been received at $branchName.",
            ['laundries_id' => (string) $laundryId, 'laundry_id' => (string) $laundryId, 'type' => 'laundry_received'], 'order_update', 'washbox-orders'
        );
    }

    public function notifyLaundryReady(string $token, int $laundryId, string $branchName): bool
    {
        if (!SystemSetting::get('notify_laundry_ready', true)) return false;
        return $this->sendToDevice($token, '✅ Laundry Ready!',
            "Your laundry is clean and ready for pickup at $branchName.",
            ['laundries_id' => (string) $laundryId, 'laundry_id' => (string) $laundryId, 'type' => 'laundry_ready'], 'order_update', 'washbox-orders'
        );
    }

    public function notifyOrderCompleted(string $token, int $laundryId): bool
    {
        if (!SystemSetting::get('notify_laundry_completed', true)) return false;
        return $this->sendToDevice($token, '🎉 Order Completed!',
            "Your laundry order has been completed. Thank you for choosing WashBox!",
            ['laundries_id' => (string) $laundryId, 'laundry_id' => (string) $laundryId, 'type' => 'laundry_completed'], 'order_update', 'washbox-orders'
        );
    }

    public function notifyUnclaimedReminder(string $token, int $laundryId, int $daysUnclaimed): bool
    {
        if (!SystemSetting::get('notify_unclaimed', true)) return false;
        if (!SystemSetting::get("reminder_day_$daysUnclaimed", false)) return false;
        $fee = SystemSetting::get('storage_fee_per_day', 5) * $daysUnclaimed;
        return $this->sendToDevice($token, '⚠️ Unclaimed Laundry Reminder',
            "Your laundry has been unclaimed for $daysUnclaimed day(s). Storage fee: ₱$fee. Please pick up soon.",
            ['laundries_id' => (string) $laundryId, 'laundry_id' => (string) $laundryId, 'type' => 'unclaimed_reminder', 'days' => (string) $daysUnclaimed],
            'order_update', 'washbox-pickup'
        );
    }

    public function notifyOutForDelivery(string $token, int $laundryId, string $driverName): bool
    {
        return $this->sendToDevice($token, '🚚 Out for Delivery!',
            "Your laundry is on its way! Driver: $driverName.",
            ['laundries_id' => (string) $laundryId, 'laundry_id' => (string) $laundryId, 'type' => 'delivery_en_route'], 'pickup_alert', 'washbox-pickup'
        );
    }
}
