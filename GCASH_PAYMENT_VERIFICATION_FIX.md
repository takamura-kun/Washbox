# GCash QR Code Payment Verification Fix

## Problem
Branch staff were not receiving notifications when customers submitted GCash payment proofs via QR code scan. This caused delays in payment verification as staff had to manually check the payment verification page.

## Root Cause
When a customer scanned a GCash QR code and submitted payment proof through the mobile app:
1. The `PaymentProofController::store()` method created the payment proof record
2. The laundry's payment status was updated to `'pending_verification'`
3. **BUT** no notification was sent to branch staff

This meant staff had no way of knowing when new payment proofs were submitted unless they manually refreshed the payment verification page.

## Solution Implemented

### 1. Added Notification Method
**File:** `backend/app/Services/NotificationService.php`

Added a new method `notifyPaymentProofSubmitted()` that:
- Notifies all staff in the branch when a payment proof is submitted
- Includes payment details (amount, reference number, customer name)
- Uses the existing notification infrastructure

```php
public static function notifyPaymentProofSubmitted(
    Laundry $laundry, 
    float $amount, 
    ?string $referenceNumber = null
): int
```

### 2. Updated Payment Proof Controller
**File:** `backend/app/Http/Controllers/Api/PaymentProofController.php`

Modified the `store()` method to:
- Call `NotificationService::notifyPaymentProofSubmitted()` after creating the payment proof
- Pass the laundry, amount, and reference number to the notification service

### 3. Added Auto-Refresh to Branch Panel
**File:** `backend/resources/views/branch/payments/verification/index.blade.php`

Enhanced the payment verification page with:
- **Auto-refresh every 30 seconds** when viewing pending or all payments
- Visual indicator showing "Auto-refreshing every 30 seconds for new payments"
- Smart refresh that only works when the page is visible (saves resources)
- Stops refreshing when viewing approved/rejected payments (no need for constant updates)

## How It Works Now

### Customer Side (Mobile App)
1. Customer selects GCash payment method
2. Customer scans branch-specific GCash QR code
3. Customer completes payment in GCash app
4. Customer uploads payment proof screenshot
5. Payment proof is submitted with status `'pending'`

### Branch Staff Side (Web Panel)
1. **Instant notification** appears in staff's notification bell
2. Notification shows:
   - Customer name
   - Laundry tracking number
   - Payment amount
   - Reference number (if provided)
3. **Auto-refresh** updates the payment verification page every 30 seconds
4. Staff can click notification to go directly to payment verification
5. Staff reviews payment proof and approves/rejects

## Benefits

✅ **Real-time awareness** - Staff are immediately notified of new payment submissions
✅ **Reduced delays** - No more manual page refreshing needed
✅ **Better customer experience** - Faster payment verification means faster service
✅ **Resource efficient** - Auto-refresh only when needed and page is visible
✅ **Audit trail** - All notifications are logged in the database

## Testing Checklist

- [ ] Customer submits GCash payment proof via mobile app
- [ ] Branch staff receives notification in notification bell
- [ ] Notification contains correct payment details
- [ ] Payment verification page auto-refreshes every 30 seconds
- [ ] Auto-refresh stops when page is hidden/minimized
- [ ] Auto-refresh only works for pending/all payments view
- [ ] Staff can approve/reject payment from notification link
- [ ] Customer receives approval/rejection notification

## Files Modified

1. `backend/app/Services/NotificationService.php` - Added notification method
2. `backend/app/Http/Controllers/Api/PaymentProofController.php` - Added notification call
3. `backend/resources/views/branch/payments/verification/index.blade.php` - Added auto-refresh

## Additional Notes

- The notification system uses the existing `notifications` table
- Notifications are sent to all active staff in the branch
- If no branch is specified, notifications go to all active staff
- Auto-refresh interval can be adjusted by changing the `30000` milliseconds value
- The fix is backward compatible and doesn't break existing functionality
