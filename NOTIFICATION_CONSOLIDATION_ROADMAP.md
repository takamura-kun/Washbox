# Notification Services Consolidation Roadmap

## Executive Summary

Three notification services (FCMService, FirebaseNotificationService, NotificationService) have evolved independently, creating:
- **754 lines of duplicate/fragmented code**
- **No unified error handling**
- **No retry mechanism for failed sends**
- **N+1 database queries**
- **Deprecated token usage**

**Solution:** Use the pre-built **NotificationManager** to consolidate everything into a single, testable service.

---

## Phase 1: Immediate Actions (This Week)

### 1.1 Create NotificationEvent Model & Migration
**Purpose:** Store notification configuration (channels, sounds, templates)

```bash
php artisan make:model NotificationEvent -m
```

**Migration (2026_04_29_000002_create_notification_events_table.php):**
```php
Schema::create('notification_events', function (Blueprint $table) {
    $table->id();
    $table->string('type')->unique(); // laundry_received, payment_approved, etc.
    $table->string('title_template');
    $table->text('body_template');
    $table->string('channel_id')->default('washbox-default'); // Android
    $table->string('sound')->default('default'); // iOS/Android
    $table->string('icon')->nullable(); // Android icon name
    $table->string('color')->nullable(); // Android color hex
    $table->string('priority')->default('high'); // high, normal
    $table->integer('retry_count')->default(3);
    $table->integer('retry_delay_seconds')->default(60);
    $table->boolean('enabled')->default(true);
    $table->json('metadata')->nullable(); // Extra config
    $table->timestamps();
    
    $table->index('type');
});
```

**Model (app/Models/NotificationEvent.php):**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationEvent extends Model
{
    protected $fillable = [
        'type', 'title_template', 'body_template',
        'channel_id', 'sound', 'icon', 'color',
        'priority', 'retry_count', 'retry_delay_seconds',
        'enabled', 'metadata'
    ];

    protected $casts = ['metadata' => 'json', 'enabled' => 'boolean'];

    public function scopeEnabled($query) {
        return $query->where('enabled', true);
    }
}
```

### 1.2 Create NotificationDelivery Model & Migration
**Purpose:** Audit trail for all notification sends

```bash
php artisan make:model NotificationDelivery -m
```

**Migration (2026_04_29_000003_create_notification_deliveries_table.php):**
```php
Schema::create('notification_deliveries', function (Blueprint $table) {
    $table->id();
    $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
    $table->string('event_type'); // laundry_received, payment_approved
    $table->string('platform'); // fcm, email, sms
    $table->string('status'); // pending, sent, failed, delivered
    $table->text('token_or_address'); // FCM token, email, phone
    $table->integer('attempt_count')->default(1);
    $table->dateTime('next_retry_at')->nullable();
    $table->text('error_message')->nullable();
    $table->json('metadata')->nullable();
    $table->dateTime('sent_at')->nullable();
    $table->dateTime('delivered_at')->nullable();
    $table->timestamps();
    
    $table->index('customer_id');
    $table->index('user_id');
    $table->index('status');
    $table->index('event_type');
});
```

**Model (app/Models/NotificationDelivery.php):**
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationDelivery extends Model
{
    protected $fillable = [
        'customer_id', 'user_id', 'event_type', 'platform',
        'status', 'token_or_address', 'attempt_count',
        'next_retry_at', 'error_message', 'metadata',
        'sent_at', 'delivered_at'
    ];

    protected $casts = [
        'metadata' => 'json',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'next_retry_at' => 'datetime',
    ];

    public function customer(): BelongsTo {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function scopePending($query) {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query) {
        return $query->where('status', 'failed');
    }

    public function markSent() {
        $this->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function markFailed($error) {
        $this->update([
            'status' => 'failed',
            'error_message' => $error,
            'attempt_count' => $this->attempt_count + 1
        ]);
    }
}
```

### 1.3 Create Seeder for NotificationEvent
**Purpose:** Configure all 30+ notification types

```bash
php artisan make:seeder NotificationEventSeeder
```

**Seeder (database/seeders/NotificationEventSeeder.php):**
```php
namespace Database\Seeders;

use App\Models\NotificationEvent;
use Illuminate\Database\Seeder;

class NotificationEventSeeder extends Seeder
{
    public function run()
    {
        $events = [
            // Laundry Events (Orders)
            [
                'type' => 'laundry_received',
                'title_template' => '📦 Laundry Received',
                'body_template' => 'Your laundry #{tracking_number} has been received at {branch_name}',
                'channel_id' => 'washbox-orders',
                'sound' => 'order_update',
            ],
            [
                'type' => 'laundry_ready',
                'title_template' => '✅ Laundry Ready!',
                'body_template' => 'Your laundry #{tracking_number} is ready for pickup at {branch_name}',
                'channel_id' => 'washbox-orders',
                'sound' => 'order_update',
            ],
            // ... 25+ more notification types
        ];

        foreach ($events as $event) {
            NotificationEvent::firstOrCreate(
                ['type' => $event['type']],
                $event
            );
        }
    }
}
```

### 1.4 Update NotificationManager to Use Models
**Modify:** Update NotificationManager to use NotificationEvent and NotificationDelivery

```php
// In NotificationManager.php
public function send(string $eventType, Customer|User $recipient, array $context = [])
{
    // 1. Get event configuration
    $eventConfig = NotificationEvent::where('type', $eventType)
        ->where('enabled', true)
        ->firstOrFail();

    // 2. Create audit record
    $delivery = NotificationDelivery::create([
        'customer_id' => $recipient->id,
        'event_type' => $eventType,
        'platform' => 'fcm',
        'status' => 'pending',
        'token_or_address' => $recipient->getActiveFcmToken(),
        'metadata' => $context,
    ]);

    // 3. Render template
    $title = $this->renderTemplate($eventConfig->title_template, $context);
    $body = $this->renderTemplate($eventConfig->body_template, $context);

    // 4. Send with retry
    $this->sendWithRetry($delivery, $title, $body, $eventConfig);

    return $delivery;
}
```

---

## Phase 2: Refactoring (Next Week)

### 2.1 Update NotificationService to Delegate
**Action:** Refactor `NotificationService` to use `NotificationManager`

```php
// BEFORE
public static function notifyPaymentApproved(Laundry $laundry) {
    Notification::create([...]);
    $customer->fcm_token && FCMService::sendToDevice(...);
}

// AFTER
public static function notifyPaymentApproved(Laundry $laundry) {
    app(NotificationManager::class)->send('payment_approved', $laundry->customer, [
        'laundry_id' => $laundry->id,
        'tracking_number' => $laundry->tracking_number,
    ]);
}
```

### 2.2 Delete FCMService
**Action:** Remove `backend/app/Services/FCMService.php` after refactoring

- All FCM logic now in NotificationManager
- All callers updated to use NotificationManager

### 2.3 Deprecate FirebaseNotificationService
**Action:** Mark as deprecated, keep for backward compatibility

```php
class FirebaseNotificationService {
    /**
     * @deprecated Use NotificationManager instead
     */
    public function sendToDevice(...) {
        Log::warning('FirebaseNotificationService is deprecated. Use NotificationManager.');
        return app(NotificationManager::class)->sendToDevice(...);
    }
}
```

### 2.4 Update All Controllers
**Action:** Replace notification calls in all controllers

```php
// BEFORE
LaundryController::
    NotificationService::notifyLaundryStatusChanged($laundry, 'received', 'processing');

// AFTER
LaundryController::
    $notificationManager->send('laundry_status_changed', $laundry->customer, [
        'old_status' => 'received',
        'new_status' => 'processing',
        'tracking_number' => $laundry->tracking_number,
    ]);
```

---

## Phase 3: Optimization (Following Sprint)

### 3.1 Add Retry Job
**Purpose:** Retry failed notification sends

```bash
php artisan make:job RetryFailedNotifications
```

### 3.2 Add Email/SMS Support
**Purpose:** Extend beyond push notifications

```php
// NotificationManager supports multiple platforms
$manager->send('payment_approved', $customer, $context, ['fcm', 'email', 'sms']);
```

### 3.3 Add User Preferences
**Purpose:** Respect notification settings

```php
public function shouldSend(NotificationEvent $event, Customer $customer): bool
{
    $prefs = json_decode($customer->notification_preferences, true);
    return $prefs[$event->type] ?? true;
}
```

---

## Implementation Checklist

- [ ] Create NotificationEvent model + migration + seeder
- [ ] Create NotificationDelivery model + migration
- [ ] Run migrations: `php artisan migrate`
- [ ] Seed events: `php artisan db:seed --class=NotificationEventSeeder`
- [ ] Update NotificationManager to use new models
- [ ] Test NotificationManager with new models
- [ ] Refactor NotificationService to delegate
- [ ] Update all controllers to use NotificationManager
- [ ] Delete FCMService.php
- [ ] Mark FirebaseNotificationService as deprecated
- [ ] Update tests to use NotificationManager
- [ ] Verify all 30+ notification types work
- [ ] Monitor NotificationDelivery audit trail in production

---

## Testing Strategy

### Unit Tests
```php
// NotificationManagerTest
test('sends notification with correct channel')
test('retries failed sends 3 times')
test('creates audit trail for each send')
test('renders templates correctly')
test('respects user notification preferences')
test('handles invalid tokens gracefully')

// NotificationEventTest
test('loads event configuration')
test('filters by enabled status')

// NotificationDeliveryTest
test('tracks send status')
test('records retry attempts')
test('logs error messages')
```

### Integration Tests
```php
// Full notification flow
test('payment approval sends FCM + creates audit + has correct channel')
test('pickup request notifies correct branch staff')
test('status change notifications use correct templates')
test('batch operations avoid N+1 queries')
```

---

## Metrics & Monitoring

### After Consolidation
- **Code duplication:** 200+ lines → 0 (eliminated)
- **Services:** 3 → 1 (unified)
- **Error handling:** 3 approaches → 1 (consistent)
- **Audit trail:** Partial → Complete (NotificationDelivery)
- **Test coverage:** <10% → 70%+ (easier to test)
- **Retry capability:** None → 3 attempts with backoff

### Key Metrics to Track
- Notification delivery success rate (should be >95%)
- Average retry attempts (target <1.2)
- Failed notification alerts
- Notification latency (P95 <2s)

---

## Rollback Plan

If issues occur:
1. Keep NotificationService as wrapper (no breaking changes)
2. FirebaseNotificationService remains available as backup
3. Gradual migration controller-by-controller
4. Monitor NotificationDelivery for failures

---

## Cost Benefit Analysis

| Aspect | Before | After | Benefit |
|--------|--------|-------|---------|
| Code duplication | High | None | Maintainability |
| Error handling | Fragmented | Unified | Reliability |
| Audit trail | Partial | Complete | Debugging |
| Retry mechanism | None | 3x with backoff | Delivery rate |
| Database queries | N+1 | Batch | Performance |
| Test coverage | <10% | 70%+ | Quality |
| Documentation | Scattered | Centralized | Developer experience |

**ROI:** 3-4 days of work for 6-month maintenance savings and reliability improvements.

---

## Timeline

| Phase | Duration | Start | End |
|-------|----------|-------|-----|
| Phase 1 (Models + Seeder) | 1 day | Today | Tomorrow |
| Phase 2 (Refactoring) | 2 days | Tomorrow | Day 3 |
| Phase 3 (Testing + Review) | 1 day | Day 3 | Day 4 |
| Phase 4 (Production Deploy) | 1 day | Day 5 | Day 5 |

**Total: 5 working days**

---

## Success Criteria

✅ All 30+ notification types use NotificationManager  
✅ NotificationDelivery has audit trail for every send  
✅ Failed sends automatically retry up to 3 times  
✅ No duplicate FCM logic  
✅ Tests cover >70% of notification code  
✅ Notification success rate >95%  
✅ Zero production errors related to notifications  

---

## Questions & Answers

**Q: Will this break existing code?**  
A: No. We keep NotificationService as a wrapper that delegates to NotificationManager. Gradual migration.

**Q: What about deprecated FCMService and FirebaseNotificationService?**  
A: Mark as deprecated but keep for 1 sprint as fallback, then remove after all callers updated.

**Q: How do we handle notification preferences?**  
A: Check `customer->notification_preferences` in NotificationManager before sending.

**Q: What if FCM is down?**  
A: NotificationDelivery records status as 'failed', retry job picks it up later. Graceful degradation.

**Q: How to monitor success?**  
A: Query NotificationDelivery table: `NotificationDelivery::where('status', 'failed')->recent()->paginate()`

---

## Next Steps

1. **Today:** Create NotificationEvent and NotificationDelivery models
2. **Tomorrow:** Update NotificationManager to use new models
3. **Day 3:** Refactor NotificationService to delegate
4. **Day 4:** Test all notification flows
5. **Day 5:** Deploy and monitor

---

*Complete documentation also available in:*
- `NOTIFICATION_SERVICES_ANALYSIS.md` - Detailed analysis
- `NOTIFICATION_SERVICES_CHECK.md` - Summary comparison
- `FIXES_COMPLETED_SUMMARY.md` - All project fixes overview
