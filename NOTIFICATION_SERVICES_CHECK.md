# Notification Services Deep Check - Summary

## Three Files Analyzed

### 1. FCMService.php (200 lines)
**Purpose:** Low-level Firebase Cloud Messaging wrapper  
**Type:** Infrastructure service

**What it does:**
- `sendToDevice()` - Single FCM token
- `sendToDevices()` - Multiple FCM tokens (loops)
- `sendToTopic()` - Topic broadcasting
- `sendToBranchStaff()` - Queries staff, sends to each
- `sendToCustomer()` - Sends to customer's single FCM token

**Strengths:**
- Clean channel mapping (27 notification types)
- Handles Android + iOS configurations
- Proper error logging

**Critical Issues:**
1. **Uses deprecated `customer->fcm_token`** - Should use `device_tokens` table
2. **Duplicate code with FirebaseNotificationService** - Two services doing same thing
3. **N+1 queries in `sendToBranchStaff()`** - Loops and queries for each staff
4. **No retry logic** - Failed sends are lost
5. **No audit trail** - Can't track which notifications succeeded/failed
6. **Database queries in service** - `sendToBranchStaff()` violates SRP

---

### 2. FirebaseNotificationService.php (250 lines)
**Purpose:** Business logic wrapper around FCM  
**Type:** Business service

**What it does:**
- `sendToDevice()` - Single token (duplicates FCMService!)
- `sendToMultiple()` - Uses multicast (better than FCMService)
- `notifyOrderReceived()` - Order-specific with SystemSetting checks
- `notifyLaundryReady()` - Order-specific
- `notifyOrderCompleted()` - Order-specific
- `notifyUnclaimedReminder()` - With dynamic pricing
- `notifyOutForDelivery()` - Driver name, etc.

**Strengths:**
- Uses `sendMulticast()` (efficient batch)
- Checks SystemSettings (configuration-driven)
- Includes dynamic pricing logic

**Critical Issues:**
1. **DUPLICATE of FCMService** - sendToDevice() exists in both!
2. **Very limited scope** - Only 5 notification types (incomplete)
3. **Inconsistent with NotificationService** - Uses different methods
4. **Uses deprecated `customer->fcm_token`** - Old pattern
5. **Doesn't handle ALL notification types** - Missing pickup, payment, etc.
6. **Silent FCM failures** - No retry, no logging

---

### 3. NotificationService.php (754 lines!)
**Purpose:** Central notification hub - database + FCM combined  
**Type:** Core service

**What it does:**
- **Database operations:** Creates notification records for UI
  - `sendToUser()` - Creates DB record for staff/admin
  - `sendToBranchStaff()` - Loops through staff, creates N records
  - `sendToAllStaff()` - All staff notifications (N records)
  - `sendToAllAdmins()` - All admin notifications

- **FCM integration:** Also sends push notifications
  - `sendToCustomer()` - Creates DB record AND sends FCM (hybrid!)

- **Business notifications:** 70+ methods for specific events
  - Pickup: `notifyNewPickupRequest()`, `notifyPickupAccepted()`, etc. (8 methods)
  - Laundry: `notifyNewLaundry()`, `notifyLaundryStatusChanged()` (21 status types)
  - Payment: `notifyPaymentReceived()`, `notifyPaymentApproved()`, etc. (5 methods)
  - Unclaimed: `notifyUnclaimedLaundry()`, `notifyUnclaimed...()` (10+ variants)

**Strengths:**
- Comprehensive - Covers all notification types
- Includes database audit trail
- Detailed status messages with templates
- Handles edge cases (walk-in customers, admin notes, etc.)

**Critical Issues:**
1. **MASSIVE FILE** (754 lines) - Violates Single Responsibility
2. **ALL STATIC METHODS** - Can't inject dependencies, hard to test
3. **DUPLICATE FCM logic** - Also sends FCM like FCMService
4. **N+1 queries** - Loops in `sendToBranchStaff()` and `sendToAllStaff()`
5. **Saves EVERYTHING to DB** - Bloats notifications table
6. **No audit trail for FCM** - DB record exists but FCM status unknown
7. **Silent failures** - FCM errors logged but DB record still created
8. **No retry mechanism** - Failed FCM sends are permanent failures
9. **Uses deprecated `fcm_token`** - When calling FCMService
10. **No transaction safety** - DB + FCM could be out of sync

---

## The Core Problem

**Three services evolved independently, causing:**

```
FCMService + FirebaseNotificationService + NotificationService
     ↓              ↓                              ↓
  Firebase      Firebase                    Database + Firebase
  (low-level)   (business logic)            (kitchen sink!)

Result:
- Duplicate code (sendToDevice in 2 services)
- Inconsistent patterns (static vs instance, loops vs multicast)
- Mixed concerns (data + notification in same service)
- No error handling coordination
- No unified audit trail
- Three ways to send same notification
```

---

## Visual Architecture

```
Current (Fragmented):
┌─────────────────────────────────────────┐
│ PaymentProofController                  │
└────────────┬────────────────────────────┘
             │
    ┌────────┴──────────┐
    ↓                   ↓
┌──────────────┐  ┌──────────────────────┐
│FCMService    │  │NotificationService   │
└──────────────┘  └──────────────────────┘
    ↓                   ↓ (also uses)
└────────────┬──────────┘
             ↓
  FirebaseNotificationService
             ↓
        Firebase API

Problems: 
- Duplicate code between FCMService & Firebase...Service
- Duplicate logic between FCMService & NotificationService.sendToCustomer()
- No coordination on retries, errors, audit trail
- Three different ways to send same notification
```

---

## The Solution (Already Implemented!)

The **NotificationManager** (created in PHASE 2A of project fixes) consolidates all three:

```
New (Unified):
┌─────────────────────────────────────────┐
│ PaymentProofController (uses Transact)  │
│ LaundryController                       │
│ PickupController                        │
│ All other services                      │
└────────────┬────────────────────────────┘
             │
             ↓
┌─────────────────────────────────────────┐
│ NotificationManager (Single Hub!)       │
├─────────────────────────────────────────┤
│ - Unified notification API              │
│ - Handles FCM + Database                │
│ - Retry logic (3 attempts with backoff) │
│ - Audit trail (PaymentEvent model)      │
│ - Error handling & logging              │
│ - Batch operations (no N+1)             │
└────────────┬────────────────────────────┘
             │
    ┌────────┴────────┐
    ↓                 ↓
Firebase API      Database
                  (audit trail)
```

---

## Code Comparison

### BEFORE (Using NotificationService - Fragmented)
```php
// In LaundryController
NotificationService::notifyPaymentProofSubmitted($laundry, $amount, $ref);
// Issues:
// - Calls FCMService internally (hidden dependency)
// - DB record created regardless of FCM success
// - No retry if FCM fails
// - No way to know if notification succeeded

// In PaymentProofController
NotificationService::notifyPaymentApproved($laundry);
// Same issues...
```

### AFTER (Using NotificationManager - Unified)
```php
// In LaundryController
$notificationManager->sendPaymentProofSubmitted($laundry, $amount, $ref);
// Results in:
// ✅ DB record created with proper structure
// ✅ FCM sent with channel + sound
// ✅ Audit trail created (success/failure status)
// ✅ Automatic retry on failure
// ✅ Structured logging
// ✅ Easy to test (dependency injection)
```

---

## Deprecation Plan

| Service | Status | Action | Timeline |
|---------|--------|--------|----------|
| FCMService | ❌ Deprecated | Delete - move to NotificationManager | ASAP |
| FirebaseNotificationService | ⚠️ Deprecated | Keep for backward compat, mark deprecated | Immediate |
| NotificationService | ⚠️ Refactored | Delegate all to NotificationManager | Gradual |
| **NotificationManager** | ✅ Active | Use everywhere | Now |

---

## What's Already Fixed

From the project fixes implementation:
- ✅ NotificationManager created with proper error handling
- ✅ Device tokens using proper `device_tokens` table (FCM token fix)
- ✅ Transaction safety with atomic operations (PaymentEvent audit trail)
- ✅ Retry logic with exponential backoff
- ✅ Batch operations to avoid N+1 queries
- ✅ Comprehensive logging for debugging

---

## What Still Needs Completing

To fully consolidate, create these models:
1. **NotificationEvent** - Configuration (channels, sounds, templates)
2. **NotificationDelivery** - Audit trail (sent, failed, retry attempts)

Then refactor:
1. Delete `FCMService.php`
2. Deprecate `FirebaseNotificationService.php`
3. Refactor `NotificationService.php` to delegate to NotificationManager
4. Update all callers to use NotificationManager

---

## Key Findings Summary

| Metric | FCMService | Firebase... | Notification... |
|--------|-----------|------------|-----------------|
| Lines of Code | 200 | 250 | **754** |
| Methods | 5 | 7 | **70+** |
| Duplicate code | ❌ With Firebase | ❌ With FCM | ❌ With both |
| Error handling | ⚠️ Logs only | ⚠️ Logs only | ⚠️ Logs only |
| Retry logic | ❌ None | ❌ None | ❌ None |
| Audit trail | ❌ None | ❌ None | ⚠️ DB only |
| Testability | ⚠️ Hard | ⚠️ Hard | ❌ Very hard |
| Database queries | ❌ Yes (N+1) | - | ❌ Yes (N+1) |
| Notification types | 27 mapped | 5 types | **70+ methods** |

**Conclusion:** Three services doing overlapping jobs, resulting in code duplication, fragmented error handling, and difficult maintenance. NotificationManager consolidates everything into a single, testable, auditable service.
