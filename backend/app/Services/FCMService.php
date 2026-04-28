<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Firebase\Messaging\AndroidConfig;
use Kreait\Firebase\Messaging\ApnsConfig;
use Illuminate\Support\Facades\Log;

class FCMService
{
    protected $messaging;

    public function __construct()
    {
        $credentialsPath = storage_path('app/firebase/service-account.json');
        if (file_exists($credentialsPath)) {
            $factory = (new Factory)->withServiceAccount($credentialsPath);
            $this->messaging = $factory->createMessaging();
        }
    }

    /**
     * Map notification type to Android channel and sound
     */
    private function getChannelForType(string $type): array
    {
        $map = [
            'laundry_received'     => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'laundry_ready'        => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'laundry_completed'    => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'laundry_cancelled'    => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'payment_pending'      => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'payment_received'     => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'payment_verification' => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'payment_rejected'     => ['channel' => 'washbox-orders',  'sound' => 'order_update'],
            'pickup_submitted'     => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'pickup_accepted'      => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'pickup_en_route'      => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'pickup_completed'     => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'pickup_cancelled'     => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'delivery_scheduled'   => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'delivery_en_route'    => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'delivery_completed'   => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'delivery_failed'      => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'unclaimed_reminder'   => ['channel' => 'washbox-pickup',  'sound' => 'pickup_alert'],
            'promotion'            => ['channel' => 'washbox-promo',   'sound' => 'promo_chime'],
            'welcome'              => ['channel' => 'washbox-promo',   'sound' => 'promo_chime'],
            'feedback_request'     => ['channel' => 'washbox-promo',   'sound' => 'promo_chime'],
            'loyalty_reward'       => ['channel' => 'washbox-promo',   'sound' => 'promo_chime'],
            'birthday_greeting'    => ['channel' => 'washbox-promo',   'sound' => 'promo_chime'],
            'app_update'           => ['channel' => 'washbox-promo',   'sound' => 'promo_chime'],
            'emergency_alert'      => ['channel' => 'washbox-orders',  'sound' => 'pickup_alert'],
        ];

        return $map[$type] ?? ['channel' => 'washbox-default', 'sound' => 'default'];
    }

    /**
     * Send notification to single device
     */
    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging || empty($fcmToken)) {
            Log::warning('FCM: Cannot send - messaging not configured or empty token');
            return false;
        }

        try {
            $type = $data['type'] ?? 'default';
            $channelInfo = $this->getChannelForType($type);

            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data)
                ->withAndroidConfig(AndroidConfig::fromArray([
                    'notification' => [
                        'channel_id' => $channelInfo['channel'],
                        'sound'      => $channelInfo['sound'],
                    ],
                ]))
                ->withApnsConfig(ApnsConfig::fromArray([
                    'payload' => [
                        'aps' => [
                            'sound' => $channelInfo['sound'] . '.mp3',
                        ],
                    ],
                ]));

            $this->messaging->send($message);

            Log::info("FCM: Sent to device", ['token' => substr($fcmToken, 0, 20) . '...']);
            return true;
        } catch (\Exception $e) {
            Log::error("FCM Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send notification to multiple devices
     */
    public function sendToDevices(array $fcmTokens, string $title, string $body, array $data = []): array
    {
        if (!$this->messaging || empty($fcmTokens)) {
            return ['success' => 0, 'failure' => count($fcmTokens)];
        }

        $successCount = 0;
        $failureCount = 0;

        foreach ($fcmTokens as $token) {
            if ($this->sendToDevice($token, $title, $body, $data)) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        return ['success' => $successCount, 'failure' => $failureCount];
    }

    /**
     * Send notification to topic (all subscribers)
     */
    public function sendToTopic(string $topic, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging) {
            return false;
        }

        try {
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

            $this->messaging->send($message);
            return true;
        } catch (\Exception $e) {
            Log::error("FCM Topic Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send to all staff in a branch
     */
    public function sendToBranchStaff(int $branchId, string $title, string $body, array $data = []): array
    {
        $tokens = \App\Models\User::where('branch_id', $branchId)
            ->where('role', 'staff')
            ->whereNotNull('fcm_token')
            ->pluck('fcm_token')
            ->toArray();

        return $this->sendToDevices($tokens, $title, $body, $data);
    }

    /**
     * Send to customer
     */
    public function sendToCustomer(\App\Models\Customer $customer, string $title, string $body, array $data = []): bool
    {
        if (empty($customer->fcm_token)) {
            return false;
        }

        return $this->sendToDevice($customer->fcm_token, $title, $body, $data);
    }
}
