# Payment Verification Flow Diagram

## Complete Flow: Customer Payment to Branch Verification

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         CUSTOMER SIDE (Mobile App)                       │
└─────────────────────────────────────────────────────────────────────────┘

    1. Customer selects GCash payment
           ↓
    2. App fetches branch-specific QR code
       GET /api/v1/customer/gcash/qr/{branchId}
           ↓
    3. Customer scans QR code with GCash app
           ↓
    4. Customer completes payment in GCash
           ↓
    5. Customer takes screenshot of payment confirmation
           ↓
    6. Customer uploads payment proof
       POST /api/v1/customer/laundries/{laundry}/payment-proof
           ↓
    ┌─────────────────────────────────────────────────────────┐
    │  PaymentProofController::store()                        │
    │  - Validates payment proof                              │
    │  - Uploads image securely                               │
    │  - Creates PaymentProof record (status: 'pending')      │
    │  - Updates Laundry (payment_status: 'pending_verification') │
    │  - ✅ NEW: Calls NotificationService::notifyPaymentProofSubmitted() │
    └─────────────────────────────────────────────────────────┘
           ↓
    ┌─────────────────────────────────────────────────────────┐
    │  NotificationService::notifyPaymentProofSubmitted()     │
    │  - Gets branch staff list                               │
    │  - Creates notification for each staff member           │
    │  - Includes: amount, customer, tracking number, ref#    │
    └─────────────────────────────────────────────────────────┘
           ↓
           ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                      BRANCH STAFF SIDE (Web Panel)                       │
└─────────────────────────────────────────────────────────────────────────┘

    ✅ Notification appears in bell icon
           ↓
    Staff clicks notification or navigates to:
    GET /branch/payments/verification
           ↓
    ┌─────────────────────────────────────────────────────────┐
    │  PaymentVerificationController::index()                 │
    │  - Gets authenticated staff's branch_id                 │
    │  - ✅ Filters PaymentProofs by branch_id                │
    │  - ✅ Calculates accurate statistics for branch         │
    │  - Returns paginated payment proofs + stats             │
    └─────────────────────────────────────────────────────────┘
           ↓
    ┌─────────────────────────────────────────────────────────┐
    │  Branch Panel View                                      │
    │  - ✅ Shows only branch's payment proofs                │
    │  - ✅ Displays accurate statistics                      │
    │  - ✅ Auto-refreshes every 30 seconds                   │
    │  - Shows payment details, customer info, amount         │
    └─────────────────────────────────────────────────────────┘
           ↓
    Staff reviews payment proof
           ↓
    ┌─────────────────┬───────────────────┐
    │   APPROVE       │      REJECT       │
    └─────────────────┴───────────────────┘
           ↓                    ↓
    POST /branch/payments/verification/{id}/approve
           ↓
    ┌─────────────────────────────────────────────────────────┐
    │  PaymentVerificationController::approve()               │
    │  - ✅ Verifies branch ownership                         │
    │  - Updates PaymentProof (status: 'approved')            │
    │  - Updates Laundry (payment_status: 'paid')             │
    │  - Updates Laundry status if ready                      │
    │  - Calls NotificationService::notifyPaymentApproved()   │
    └─────────────────────────────────────────────────────────┘
           ↓
    ┌─────────────────────────────────────────────────────────┐
    │  NotificationService::notifyPaymentApproved()           │
    │  - Sends notification to customer                       │
    │  - Includes: tracking number, admin notes               │
    └─────────────────────────────────────────────────────────┘
           ↓
┌─────────────────────────────────────────────────────────────────────────┐
│                    CUSTOMER SIDE (Mobile App)                            │
└─────────────────────────────────────────────────────────────────────────┘

    ✅ Customer receives approval notification
    ✅ Laundry status updated to 'paid'
    ✅ Customer can proceed with pickup/delivery
```

---

## Security Layers

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         SECURITY LAYERS                                  │
└─────────────────────────────────────────────────────────────────────────┘

Layer 1: Authentication
    ├─ Customer must be authenticated (Sanctum token)
    └─ Staff must be authenticated (Laravel session)

Layer 2: Authorization
    ├─ Customer can only submit proofs for their own laundries
    └─ Staff can only view/approve proofs from their branch

Layer 3: Branch Isolation
    ├─ All queries filter by staff->branch_id
    ├─ Direct access to other branch's proofs returns 403
    └─ Bulk operations only process staff's branch

Layer 4: Data Validation
    ├─ Payment proof image validated (type, size)
    ├─ Amount validated (numeric, positive)
    └─ Reference number validated (optional, max length)

Layer 5: Audit Trail
    ├─ All actions logged in status_histories
    ├─ All notifications stored in notifications table
    └─ verified_by and verified_at tracked
```

---

## Database Schema (Relevant Tables)

```
payment_proofs
├─ id (PK)
├─ laundry_id (FK → laundries.id)
├─ payment_method (gcash)
├─ amount (decimal)
├─ reference_number (nullable)
├─ proof_image (filename)
├─ status (pending/approved/rejected)
├─ admin_notes (nullable)
├─ verified_at (nullable)
├─ verified_by (FK → users.id, nullable)
├─ created_at
└─ updated_at

laundries
├─ id (PK)
├─ tracking_number
├─ customer_id (FK → customers.id)
├─ branch_id (FK → branches.id) ← KEY FOR FILTERING
├─ payment_status (unpaid/pending_verification/paid)
├─ payment_method (nullable)
├─ total_amount (decimal)
└─ ... other fields

notifications
├─ id (PK)
├─ user_id (FK → users.id, nullable) ← For staff
├─ customer_id (FK → customers.id, nullable) ← For customers
├─ type (payment_proof_submitted, payment_approved, etc.)
├─ title
├─ body
├─ laundries_id (FK → laundries.id, nullable)
├─ data (JSON)
├─ is_read (boolean)
├─ created_at
└─ updated_at

users (staff)
├─ id (PK)
├─ branch_id (FK → branches.id) ← KEY FOR FILTERING
├─ role (staff/admin)
├─ is_active (boolean)
└─ ... other fields
```

---

## Key Improvements Summary

### Before Fixes
❌ No notifications when payment proofs submitted
❌ Statistics showed wrong counts (only current page)
❌ Manual page refresh required
❌ No visual feedback for new submissions

### After Fixes
✅ Instant notifications to branch staff
✅ Accurate statistics for specific branch
✅ Auto-refresh every 30 seconds
✅ Visual indicators for auto-refresh
✅ Proper branch isolation
✅ Better security and data integrity
