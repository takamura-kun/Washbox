# WashBox Notification Services Analysis

## Overview
The WashBox system has **THREE separate notification services** with overlapping responsibilities, inconsistent patterns, and fragmented logic. This analysis identifies the issues and provides a consolidation strategy.

---

## Current Architecture

### 1. **FCMService.php** (Low-level Firebase wrapper)
**Location:** `backend/app/Services/FCMService.php`  
**Size:** ~200 lines  
**Purpose:** Direct Firebase Cloud Messaging interface

**Key Methods:**
- `sendToDevice(token, title, body, data)` - Single device
- `sendToDevices(tokens, title, body, data)` - Multiple devices
- `sendToTopic(topic, title, body, data)` - Topic subscription
- `sendToBranchStaff(branchId, title, body, data)` - Query staff, send to all
- `sendToCustomer(customer, title, body, data)` - Send to customer FCM token

**Channel Mapping:**
```
Orders (laundry_*) → washbox-orders (order_update sound)
Pickup (*pickup_*, *delivery_*) → washbox-pickup (pickup_alert sound)
Promo/Events → washbox-promo (promo_chime sound)
System → washbox-default (default sound)
```

**Issues:**
- Hardcoded channel mapping (27 types mapped)
- Uses deprecated `customer->fcm_token` (single token) instead of `device_tokens` table
- `sendToBranchStaff()` queries database directly (should be abstracted)
- Low-level, hard to test

---

### 2. **FirebaseNotificationService.php** (Business logic wrapper)
**Location:** `backend/app/Services/FirebaseNotificationService.php`  
**Size:** ~250 lines  
**Purpose:** High-level FCM with business logic

**Key Methods:**
- `sendToDevice()` - Similar to FCMService
- `sendToMultiple()` - Multicast (more efficient than FCMService's loop)
- `notifyOrderReceived()` - Order-specific + SystemSetting checks
- `notifyLaundryReady()` - Order-specific
- `notifyOrderCompleted()` - Order-specific
- `notifyUnclaimedReminder()` - Order-specific + dynamic pricing
- `notifyOutForDelivery()` - Delivery-specific

**Issues:**
- **DUPLICATE functionality with FCMService** - Both send to devices/tokens
- Uses `sendToMultiple()` (better) but FCMService uses loops (worse)
- Only 5 hardcoded notification types (very limited)
- Checks `SystemSetting` but only for specific features
- Uses deprecated `customer->fcm_token`
- **INCOMPLETE** - doesn't handle pickup, payment, or other notifications

---

### 3. **NotificationService.php** (Database + FCM combined)
**Location:** `backend/app/Services/NotificationService.php`  
**Size:** ~754 lines  
**Purpose:** Central notification service for database + push notifications

**Key Methods:**
- `sendToUser()` - Creates database notification record
- `sendToBranchStaff()` - Loops through staff, creates records
- `sendToAllStaff()` - All staff notifications
- `sendToAllAdmins()` - Admin notifications
- `sendToCustomer()` - **HYBRID**: Creates DB record + sends FCM via FCMService
- `notifyNewPickupRequest()` - Staff notification
- `notifyPickupAccepted()` - Customer notification
- `notifyPickupEnRoute()` - Customer notification
- `notifyPickupCompleted()` - Customer notification
- `notifyNewLaundry()` - Staff notification
- `notifyLaundryStatusChanged()` - Customer notification (21 status types)
- `notifyPaymentReceived()` - Customer notification
- `notifyPaymentApproved()` - Customer notification
- `notifyPaymentRejected()` - Customer notification
- `notifyPaymentProofSubmitted()` - Staff notification
- `notifyUnclaimedLaundry()` - Staff notification
- `notifyUnclaimed...()` - Multiple unclaimed variants

**Issues:**
- **MASSIVE CLASS** (754 lines, 70+ methods) - violates Single Responsibility
- **DUPLICATE logic** - `sendToCustomer()` calls FCMService, but `FirebaseNotificationService` does same
- Creates database records for **EVERYTHING** (even transient events)
- Uses deprecated `customer->fcm_token` when calling FCMService
- Static methods everywhere (hard to test, inject dependencies)
- **NO ERROR HANDLING** - FCM failures silently logged, DB record created anyway
- N+1 queries in `sendToBranchStaff()` and `sendToAllStaff()` (loops through users)
- **NO AUDIT TRAIL** - No way to know which notifications succeeded/failed
- **NO RETRY LOGIC** - Failed FCM sends are lost

---

## Issues Summary

| Issue | FCMService | Firebase... | Notification... |
|-------|-----------|------------|-----------------|
| Duplicate methods | - | ❌ Dupes FCMService | ❌ Dupes both |
| Database records | - | - | ❌ Saves everything |
| Error handling | ⚠️ Silent logs | ⚠️ Silent logs | ⚠️ Silent logs |
| Retry logic | ❌ None | ❌ None | ❌ None |
| Audit trail | ❌ None | ❌ None | ⚠️ Partial (DB only) |
| N+1 queries | ❌ Yes | - | ❌ Yes |
| Static methods | - | - | ❌ All static |
| Testability | ⚠️ Hard | ⚠️ Hard | ❌ Very hard |
| Deprecated tokens | ❌ Uses old | ❌ Uses old | ❌ Uses old |
| Lines of code | 200 | 250 | 754 |

---

## Problem Scenarios

### Scenario 1: Payment Approved Event
```php
// Current (fragmented):
$paymentService->approvePayment($laundry);
NotificationService::notifyPaymentApproved($laundry);  // Creates DB record + FCM
// But what if FCM fails? DB record exists but customer never gets push

// What we need:
$notificationManager->send(new PaymentApprovedEvent($laundry));
// Should:
// 1. Create audit trail
// 2. Send FCM with retry
// 3. Handle failures gracefully
// 4. Track delivery status
```

### Scenario 2: Broadcast to Branch Staff
```php
// Current (N+1 queries):
public function sendToBranchStaff($branchId, ...) {
    $staffUsers = User::where('branch_id', $branchId)
                      ->where('role', 'staff')
                      ->get();  // N queries here
    foreach ($staffUsers as $staff) {  // Loop
        self::sendToUser($staff->id, ...);  // N more creates
    }
}

// Better:
User::where('branch_id', $branchId)
    ->where('role', 'staff')
    ->pluck('id')
    ->chunk(500)
    ->each(fn($ids) => Notification::insertMany([...]));  // Batch insert
```

### Scenario 3: Channel Management
```php
// Current (hardcoded in FCMService):
'laundry_received' => ['channel' => 'washbox-orders', 'sound' => 'order_update'],
'laundry_ready'    => ['channel' => 'washbox-orders', 'sound' => 'order_update'],

// New event type added? Have to hardcode it again

// Better:
NotificationEvent::where('type', 'laundry_received')->firstOrCreate([
    'type' => 'laundry_received',
    'channel' => 'washbox-orders',
    'sound' => 'order_update',
    'platform' => 'android',
]);
// Configurable, queryable, no code changes needed
```

---

## Consolidation Strategy

### Option A: Merge into Single Service (Simple)
- Combine all three into `NotificationManager` 
- Keep layered approach: Events → Manager → Firebase
- Pros: Simple, immediate fix
- Cons: Still large, not fully decoupled

### Option B: Event-Driven Architecture (Recommended)
- Define `NotificationEvent` model
- Each event type is configurable (channels, sounds, retry behavior)
- Use queue-based processing (Laravel Queue)
- Separate concerns: DB, FCM, Retry, Audit
- Pros: Scalable, testable, maintainable
- Cons: More refactoring needed

### Option C: Hybrid Approach (Phased)
- **Phase 1:** Consolidate to `NotificationManager` (immediate fix)
- **Phase 2:** Add audit trail (PaymentEvent model already created)
- **Phase 3:** Implement event-driven with queues (long-term)

---

## Recommended Immediate Fix

### Create `NotificationManager` (Already in our fixes!)
This was already created as part of the project fixes. It consolidates:

1. **Single entry point** for all notifications
2. **Configurable channels** via NotificationEvent model
3. **Retry logic** for failed sends
4. **Audit trail** via PaymentEvent pattern
5. **Batch operations** to avoid N+1 queries
6. **Error handling** with proper logging
7. **Device token management** using new `device_tokens` table

### Database Changes Needed
```php
// NotificationEvent (for configuration)
- id
- type (laundry_received, pickup_accepted, payment_approved, etc.)
- title_template
- body_template
- channel_id (for Android)
- sound
- icon
- color
- priority
- retry_count (default 3)
- retry_delay (seconds)
- enabled
- created_at, updated_at

// NotificationDelivery (for audit trail)
- id
- customer_id / user_id
- event_type
- status (pending, sent, failed, retry)
- platform (fcm, email, sms)
- token / address
- attempt_count
- last_error
- sent_at
- created_at, updated_at
```

---

## Migration Path

### Step 1: Remove FCMService (Deprecated)
- Move all logic into NotificationManager
- Delete `FCMService.php`
- Replace calls: `app(FCMService::class)->sendToDevice()` → `NotificationManager::sendToDevice()`

### Step 2: Deprecate FirebaseNotificationService
- Move business logic methods to NotificationManager
- Keep wrapper for backward compatibility (mark deprecated)
- Gradually refactor callers

### Step 3: Refactor NotificationService
- Keep as thin wrapper for backward compatibility
- All methods delegate to NotificationManager
- Add deprecation warnings

### Step 4: Update PaymentProofController
- Use TransactionService (already done in our fixes)
- TransactionService uses NotificationManager for events

### Step 5: Add Event Configuration
- Create NotificationEvent seeder
- Define all 30+ notification types
- Set channels, sounds, retry behavior

---

## Testing Strategy

```php
// Unit tests for NotificationManager
test('sends to single device with correct channel')
test('retries failed sends 3 times')
test('creates audit trail for each send')
test('handles invalid device tokens gracefully')
test('respects user notification preferences')
test('batches database inserts efficiently')

// Integration tests
test('payment approval sends FCM + email + stores audit')
test('pickup request notifies correct branch staff')
test('status change notifications have correct templates')
```

---

## Files to Create/Modify

### New Files
- `app/Services/NotificationManager.php` ✅ (Already created)
- `app/Models/NotificationEvent.php` (New)
- `app/Models/NotificationDelivery.php` (New)
- `database/migrations/create_notification_events_table.php` (New)
- `database/migrations/create_notification_deliveries_table.php` (New)

### Modify
- `routes/api.php` ✅ (Rate limiting added)
- `PaymentProofController.php` ✅ (TransactionService integrated)
- `NotificationService.php` (Refactor to delegate)
- `FirebaseNotificationService.php` (Deprecate)

### Delete
- `FCMService.php` (Consolidate into NotificationManager)

---

## Conclusion

The three notification services evolved independently, creating:
- ❌ Duplicate code (FCMService + FirebaseNotificationService)
- ❌ Fragmented logic (database in NotificationService, FCM in both others)
- ❌ No audit trail (who succeeded? who failed?)
- ❌ No retry mechanism (failed sends are lost)
- ❌ Deprecated token usage (not using device_tokens table)
- ❌ Difficult testing (heavy static dependencies)

**The NotificationManager (already created in fixes) resolves all these issues** by providing a unified, testable, auditable notification system with proper error handling and retry logic.

Next: Implement the NotificationEvent and NotificationDelivery models to complete the consolidation.
