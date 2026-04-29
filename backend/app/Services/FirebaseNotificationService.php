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
    public function notifyOrderReceived(string $token, string $orderId, string $branchName): bool
    {
        if (!SystemSetting::get('notify_laundry_received', true)) return false;
        return $this->sendToDevice($token, '📦 Order Received!',
            "Your laundry (Order #$orderId) has been received at $branchName.",
            ['order_id' => $orderId, 'type' => 'laundry_received', 'laundries_id' => $orderId], 'default', 'washbox-orders'
        );
    }

    public function notifyLaundryReady(string $token, string $orderId, string $branchName): bool
    {
        if (!SystemSetting::get('notify_laundry_ready', true)) return false;
        return $this->sendToDevice($token, '✅ Laundry Ready!',
            "Your laundry (Order #$orderId) is clean and ready for pickup at $branchName.",
            ['order_id' => $orderId, 'type' => 'laundry_ready', 'laundries_id' => $orderId], 'default', 'washbox-orders'
        );
    }

    public function notifyOrderCompleted(string $token, string $orderId): bool
    {
        if (!SystemSetting::get('notify_laundry_completed', true)) return false;
        return $this->sendToDevice($token, '🎉 Order Completed!',
            "Order #$orderId has been completed. Thank you for choosing WashBox!",
            ['order_id' => $orderId, 'type' => 'laundry_completed', 'laundries_id' => $orderId], 'default', 'washbox-orders'
        );
    }

    public function notifyUnclaimedReminder(string $token, string $orderId, int $daysUnclaimed): bool
    {
        if (!SystemSetting::get('notify_unclaimed', true)) return false;
        if (!SystemSetting::get("reminder_day_$daysUnclaimed", false)) return false;
        $fee = SystemSetting::get('storage_fee_per_day', 5) * $daysUnclaimed;
        return $this->sendToDevice($token, '⚠️ Unclaimed Laundry Reminder',
            "Order #$orderId has been unclaimed for $daysUnclaimed day(s). Storage fee: ₱$fee. Please pick up soon.",
            ['order_id' => $orderId, 'type' => 'unclaimed_reminder', 'days' => (string) $daysUnclaimed, 'laundries_id' => $orderId],
            'default', 'washbox-orders'
        );
    }

    public function notifyOutForDelivery(string $token, string $orderId, string $driverName): bool
    {
        return $this->sendToDevice($token, '🚚 Out for Delivery!',
            "Your laundry (Order #$orderId) is on its way! Driver: $driverName.",
            ['order_id' => $orderId, 'type' => 'delivery_en_route', 'laundries_id' => $orderId], 'default', 'washbox-pickup'
        );
    }
}
