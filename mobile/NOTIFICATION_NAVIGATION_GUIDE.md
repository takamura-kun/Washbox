# Mobile Notification Navigation Guide

## How to Send Clickable Notifications from Backend

When sending FCM notifications from your Laravel backend, include the proper `data` payload to enable navigation when users tap notifications.

### Required Data Structure

```php
// In your Laravel notification class or FCM service
$data = [
    'type' => 'notification_type',  // Required: determines navigation
    'laundries_id' => '123',       // Optional: for laundry-related notifications (matches your DB)
    'pickup_id' => '456',          // Optional: for pickup-related notifications  
    'promotion_id' => '789',       // Optional: for promotional notifications
    'branch_id' => '101',          // Optional: for branch-related notifications
];

$fcmPayload = [
    'notification' => [
        'title' => 'Your Laundry is Ready!',
        'body' => 'Tap to view details',
        'sound' => 'order_update.mp3',
    ],
    'data' => $data  // This enables navigation
];
```

### Notification Types & Navigation

| Notification Type | Required Data | Navigation Destination |
|------------------|---------------|----------------------|
| `laundry_received` | `laundries_id` | `/laundries/{id}` |
| `laundry_ready` | `laundries_id` | `/laundries/{id}` |
| `laundry_completed` | `laundries_id` | `/laundries/{id}` |
| `laundry_cancelled` | `laundries_id` | `/laundries/{id}` |
| `payment_pending` | `laundries_id` | `/laundries/{id}` |
| `payment_received` | `laundries_id` | `/laundries/{id}` |
| `payment_verification` | `laundries_id` | `/laundries/{id}` |
| `payment_rejected` | `laundries_id` | `/laundries/{id}` |
| `pickup_submitted` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `pickup_accepted` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `pickup_en_route` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `pickup_completed` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `pickup_cancelled` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `delivery_scheduled` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `delivery_en_route` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `delivery_completed` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `delivery_failed` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `unclaimed_reminder` | `pickup_id`, `laundries_id` | `/pickup-tracking?pickup_id={id}` |
| `promotion` | `promotion_id` | `/promotions?highlight={id}` |
| `loyalty_reward` | `promotion_id` | `/promotions?highlight={id}` |
| `birthday_greeting` | `promotion_id` | `/promotions?highlight={id}` |
| `feedback_request` | `laundries_id` | `/ratings?laundry_id={id}` |
| `system_maintenance` | - | `/notifications` |
| `app_update` | - | `/notifications` |
| `branch_closure` | `branch_id` | `/notifications` |
| `service_update` | - | `/notifications` |
| `emergency_alert` | - | `/notifications` |
| `welcome` | - | `/(tabs)` (home screen) |

### Laravel Implementation Examples

#### 1. Laundry Ready Notification
```php
// In your LaundryStatusNotification class
public function toFcm($notifiable)
{
    return [
        'notification' => [
            'title' => 'Laundry Ready! 🧺',
            'body' => "Your {$this->laundry->service_name} is ready for pickup",
            'sound' => 'order_update.mp3',
        ],
        'data' => [
            'type' => 'laundry_ready',
            'laundries_id' => (string) $this->laundry->id,
        ]
    ];
}
```

#### 2. Pickup En Route Notification
```php
// In your PickupStatusNotification class
public function toFcm($notifiable)
{
    return [
        'notification' => [
            'title' => 'Driver En Route! 🚚',
            'body' => 'Your pickup driver is on the way',
            'sound' => 'pickup_alert.mp3',
        ],
        'data' => [
            'type' => 'pickup_en_route',
            'pickup_id' => (string) $this->pickup->id,
            'laundries_id' => (string) $this->pickup->laundry_id,
        ]
    ];
}
```

#### 3. Promotion Notification
```php
// In your PromotionNotification class
public function toFcm($notifiable)
{
    return [
        'notification' => [
            'title' => 'Special Offer! 🎉',
            'body' => $this->promotion->description,
            'sound' => 'promo_chime.mp3',
        ],
        'data' => [
            'type' => 'promotion',
            'promotion_id' => (string) $this->promotion->id,
        ]
    ];
}
```

### Sound Files Available

- `order_update.mp3` - For laundry/payment updates
- `pickup_alert.mp3` - For pickup/delivery alerts
- `promo_chime.mp3` - For promotional notifications
- `default` - System default sound

### Testing Navigation

1. Send a test notification with proper data structure
2. Tap the notification on your device
3. App should navigate to the correct screen
4. Check console logs for navigation debugging

### Important Notes

- Always include `type` in the data payload
- Use string values for all IDs (not integers)
- Test navigation on development builds (not Expo Go)
- Ensure the target screens exist in your app routing