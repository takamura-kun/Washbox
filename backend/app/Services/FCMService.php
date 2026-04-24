<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
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
     * Send notification to single device
     */
    public function sendToDevice(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        if (!$this->messaging || empty($fcmToken)) {
            Log::warning('FCM: Cannot send - messaging not configured or empty token');
            return false;
        }

        try {
            $message = CloudMessage::withTarget('token', $fcmToken)
                ->withNotification(Notification::create($title, $body))
                ->withData($data);

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
