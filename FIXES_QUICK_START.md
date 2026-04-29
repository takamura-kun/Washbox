# WashBox Fixes - Quick Start Guide

## What's New?

12 critical issues have been fixed. This guide shows you the essential changes needed to use them.

---

## 1. Database Migration (Required)

```bash
# Run the payment events migration
php artisan migrate

# Verify migration
php artisan migrate:status | grep payment_events
```

---

## 2. Payment Processing (Updated)

### Before ❌
```php
$paymentProof = PaymentProof::create([
    'laundry_id' => $laundry->id,
    'amount' => $request->amount,
    'proof_image' => $filename,
    'status' => 'pending'
]);
$laundry->update(['payment_status' => 'pending_verification']);
```

### After ✅
```php
use App\Services\TransactionService;

$transactionService = new TransactionService();
$result = $transactionService->processPaymentProof($laundry, [
    'payment_method' => 'gcash',
    'transaction_id' => $txId,
    'screenshot_path' => $path,
    'notes' => 'User notes',
]);

// Automatically handles:
// - Atomic database transaction
// - Row-level locking
// - Idempotency (prevents duplicates)
// - Deadlock retry
// - Audit trail
// - Error logging
```

---

## 3. Notifications (New)

### Usage
```php
use App\Services\NotificationManager;

$notificationManager = new NotificationManager();

// Send laundry notifications
$notificationManager->sendLaundryCreated($laundry);
$notificationManager->sendLaundryStatusChanged($laundry, 'received', 'processing');
$notificationManager->sendPaymentStatusChanged($laundry, 'unpaid', 'approved');

// Send pickup notifications
$notificationManager->sendPickupCreated($pickup);
$notificationManager->sendPickupStatusChanged($pickup, 'pending', 'accepted');
```

---

## 4. Error Handling (New)

### Before ❌
```php
if (!$laundry) {
    return response()->json([
        'success' => false,
        'message' => 'Laundry not found'
    ], 404);
}

if ($laundry->customer_id !== $customer->id) {
    return response()->json([
        'success' => false,
        'message' => 'Unauthorized'
    ], 403);
}
```

### After ✅
```php
use App\Exceptions\ResourceNotFoundException;
use App\Exceptions\UnauthorizedException;

// If not found
throw new ResourceNotFoundException('Laundry', $id);

// If unauthorized
throw new UnauthorizedException('You cannot access this laundry');

// Exception is automatically rendered as JSON with proper status code
```

---

## 5. File Uploads (Enhanced Security)

### Before ❌
```php
$uploadResult = SecureFileUploadService::uploadImage($file, 'proofs');
$filename = $uploadResult['filename'];
```

### After ✅
```php
use App\Services\FileUploadSecurityService;

try {
    $result = FileUploadSecurityService::uploadImage(
        $request->file('photo'),
        'payment-proofs'
    );
    
    // Result includes:
    // - Secure random filename
    // - Image dimensions verified
    // - MIME type validated
    // - Content verified
    // - Stored in private directory
    
    $filename = $result['filename'];
    $width = $result['width'];
    $height = $result['height'];
    
} catch (Exception $e) {
    // Handle validation errors
    return response()->json([
        'success' => false,
        'message' => $e->getMessage()
    ], 400);
}
```

---

## 6. Caching (New)

### Automatic Caching
```php
use App\Services\CacheService;

// These automatically cache with intelligent TTLs
$branches = CacheService::getBranches();        // Cached 1 hour
$services = CacheService::getServices();        // Cached 1 hour
$promotions = CacheService::getPromotions();    // Cached 30 min
$profile = CacheService::getCustomerProfile($id); // Cached 5 min

// Invalidate when data changes
CacheService::invalidateBranchesCache();
CacheService::invalidateServicesCache();
CacheService::invalidatePromotionsCache();
CacheService::invalidateCustomerCache($customerId);
```

### Production Configuration
Update `.env`:
```env
CACHE_DRIVER=redis
QUEUE_DRIVER=redis
SESSION_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

---

## 7. Rate Limiting (Expanded)

Routes are automatically rate limited:

```
POST /api/v1/laundries           → 10 per hour
PUT /api/v1/laundries/{id}/cancel → 10 per hour
POST /api/v1/pickups             → 10 per hour
PUT /api/v1/pickups/{id}/cancel  → 10 per hour
POST /api/v1/addresses           → 20 per hour
PUT /api/v1/addresses/{id}       → 30 per hour
POST /api/v1/payment-methods     → 10 per hour
POST /api/v1/location-tracking/update → 100 per minute
```

When rate limited, response:
```json
{
    "success": false,
    "error": "THROTTLE_ERROR",
    "message": "Too many requests. Please try again later.",
    "retry_after": 3600
}
```

---

## 8. Payment Audit Trail (New)

Track all payment events:

```php
use App\Models\PaymentEvent;

// Get all payment events for a laundry
$events = PaymentEvent::forLaundry($laundryId)->get();

// Query specific event types
$submissions = PaymentEvent::byType('proof_submitted')->get();
$approvals = PaymentEvent::byType('proof_approved')->get();
$rejections = PaymentEvent::byType('proof_rejected')->get();
$refunds = PaymentEvent::byType('refund_issued')->get();

// Approve/reject payment atomically
$transactionService->approvePayment($laundry);  // Sets status to 'approved'
$transactionService->rejectPayment($laundry, 'Image quality too low');
$transactionService->refundPayment($laundry, 'Customer request');

// Get complete audit trail
$audit = $transactionService->getPaymentAuditTrail($laundry);
```

---

## 9. Authorization Checks (Better)

Add to any controller method:

```php
use App\Exceptions\UnauthorizedException;

// In your controller
$laundry = Laundry::findOrFail($id);

// Verify ownership
if ($laundry->customer_id !== $customer->id) {
    throw new UnauthorizedException();
}

// Continue processing...
```

---

## 10. Testing Payment Flows

```php
// Test atomic transaction
test('payment_proof_submission_is_atomic', function () {
    $laundry = Laundry::factory()->create();
    $service = new TransactionService();
    
    $result = $service->processPaymentProof($laundry, [
        'payment_method' => 'gcash',
        'transaction_id' => '123456',
        'screenshot_path' => 'path/to/image.jpg',
    ]);
    
    expect($result['success'])->toBeTrue();
    expect($laundry->refresh()->payment_status)->toBe('pending');
});

// Test idempotency
test('duplicate_payments_are_handled', function () {
    $service = new TransactionService();
    $result1 = $service->processPaymentProof($laundry, $data);
    $result2 = $service->processPaymentProof($laundry, $data);
    
    expect($result2['idempotent'])->toBeTrue();
    expect($result1['payment_proof_id'])->toBe($result2['payment_proof_id']);
});
```

---

## 11. Logging & Monitoring

### Key Logs to Monitor
```
storage/logs/laravel.log

Search for:
- "Payment proof submitted successfully"
- "Payment approved"
- "Payment rejected"
- "Deadlock detected, retrying"
- "File upload failed"
- "Unauthorized access attempt"
```

### Query Payment Events
```php
// Get recent failed payments
$failed = PaymentEvent::where('status', 'rejected')
    ->orderBy('created_at', 'desc')
    ->limit(10)
    ->get();

// Get approval rate
$approved = PaymentEvent::byType('proof_approved')->count();
$submitted = PaymentEvent::byType('proof_submitted')->count();
$approvalRate = ($approved / $submitted) * 100;
```

---

## 12. Troubleshooting

### Rate Limiting Too Strict?
Edit `/backend/routes/api.php`:
```php
Route::post('/', [LaundryController::class, 'store'])
    ->middleware('throttle:20,60'); // Change from 10 to 20
```

### Cache Not Working?
```bash
# Check Redis connection
redis-cli ping

# Clear cache if stuck
php artisan cache:clear

# Check cache driver
php artisan tinker
>>> config('cache.default')
# Should return 'redis'
```

### File Uploads Failing?
```bash
# Ensure storage directory is writable
chmod -R 775 storage/ bootstrap/cache/

# Check Laravel log
tail -f storage/logs/laravel.log
```

### Payment Transaction Deadlock?
```bash
# Check transaction logs
SELECT * FROM payment_events 
WHERE event_type = 'proof_submitted' 
ORDER BY created_at DESC 
LIMIT 10;

# Add index if needed
ALTER TABLE laundries ADD INDEX idx_payment_status (payment_status);
```

---

## Common Tasks

### Task: Approve a Payment
```php
use App\Services\TransactionService;

$laundry = Laundry::find($id);
$service = new TransactionService();

try {
    $result = $service->approvePayment($laundry);
    echo "Payment approved: " . $result['message'];
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
```

### Task: View Payment Audit Trail
```php
use App\Services\TransactionService;

$laundry = Laundry::find($id);
$service = new TransactionService();
$events = $service->getPaymentAuditTrail($laundry);

foreach ($events as $event) {
    echo $event['event_type'] . " - " . $event['status'] . "\n";
    echo "  Time: " . $event['timestamp'] . "\n";
}
```

### Task: Clear Old Cache
```php
use App\Services\CacheService;

CacheService::invalidateAll();
echo "All cache cleared";
```

---

## Need Help?

See detailed documentation:
- `FIXES_IMPLEMENTATION_GUIDE.md` - Complete implementation details
- `FIXES_COMPLETED_SUMMARY.md` - Overview of all changes
- Service classes have inline comments and examples

---

**Last Updated**: April 29, 2026
**Version**: 1.0
