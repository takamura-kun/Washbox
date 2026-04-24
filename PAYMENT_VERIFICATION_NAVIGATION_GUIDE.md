# Branch Payment Verification - Navigation & Testing Guide

## How to Access the Payment Verification Page

### Option 1: Direct URL
Navigate directly to:
```
http://your-domain.com/branch/payments/verification
```

### Option 2: Via Sidebar Navigation
1. Log in to the Branch Panel
2. Look for **"Payment Verification"** in the sidebar under "Operations" section
3. It has a green credit card icon: 💳
4. If there are pending payments, you'll see a yellow badge with the count

---

## What You Should See

### Page Header
- **Title**: "Payment Verification"
- **Subtitle**: "Review and verify GCash payment proofs submitted by customers"
- **Auto-refresh indicator**: "Auto-refreshing every 30 seconds for new payments" (if viewing pending/all)

### Statistics Pills (Top Right)
- **Pending**: Count of pending payment proofs (yellow/warning color)
- **Approved**: Count of approved payment proofs (green/success color)
- **Rejected**: Count of rejected payment proofs (red/danger color)
- **Total**: Total count of all payment proofs

### Filter Bar
Four filter buttons:
- **All**: Show all payment proofs
- **Pending**: Show only pending proofs
- **Approved**: Show only approved proofs
- **Rejected**: Show only rejected proofs

### Payment Proof Cards
Each card shows:
- **Tracking Number**: Laundry order tracking number
- **Service**: Type of laundry service
- **Status Badge**: Pending/Approved/Rejected
- **Customer Info**: Name and phone number
- **Reference Number**: GCash reference number (if provided)
- **Amount Paid**: Amount customer paid
- **Expected Amount**: Expected payment amount
- **Mismatch Badge**: Shows if amounts don't match
- **Submitted Date**: When proof was submitted
- **Action Buttons**:
  - "View Details" - See full payment proof
  - Box icon - View laundry order details

---

## Testing the Fixes

### Test 1: Check Statistics
✅ **Expected**: Statistics should show accurate counts for YOUR BRANCH ONLY
- Pending count should match actual pending proofs
- Total should be sum of all statuses
- Counts should NOT include other branches

### Test 2: Check Branch Filtering
✅ **Expected**: You should ONLY see payment proofs from your branch
- Check tracking numbers - they should all be from your branch
- Try accessing another branch's payment proof directly (should get 403)

### Test 3: View Payment Proof Details
✅ **Expected**: No 403 error when viewing your branch's proofs
1. Click "View Details" on any payment proof
2. Should see full details without error
3. Should see payment proof image
4. Should see approve/reject buttons

### Test 4: Approve Payment
✅ **Expected**: Approval should work without errors
1. Click "View Details" on a pending payment
2. Click "Approve" button
3. Add optional notes
4. Submit
5. Should see success message
6. Customer should receive notification

### Test 5: Reject Payment
✅ **Expected**: Rejection should work without errors
1. Click "View Details" on a pending payment
2. Click "Reject" button
3. Add required rejection reason
4. Submit
5. Should see success message
6. Customer should receive notification

### Test 6: Auto-Refresh
✅ **Expected**: Page should auto-refresh every 30 seconds
1. Stay on the "All" or "Pending" view
2. Wait 30 seconds
3. Page should automatically reload
4. Switch to "Approved" or "Rejected" view
5. Auto-refresh should stop

### Test 7: Bulk Approve
✅ **Expected**: Bulk approve should work for multiple proofs
1. Go to "Pending" view
2. Check multiple payment proof checkboxes
3. Click "Bulk Approve" button
4. Confirm in modal
5. All selected proofs should be approved
6. Customers should receive notifications

---

## Common Issues & Solutions

### Issue 1: "403 Unauthorized access to this payment proof"
**Cause**: Trying to access another branch's payment proof
**Solution**: This is expected - you can only access your branch's proofs

### Issue 2: "Your account is not assigned to any branch"
**Cause**: Your user account has no branch_id
**Solution**: Contact admin to assign you to a branch

### Issue 3: "Payment proof has no associated laundry order"
**Cause**: Database integrity issue - payment proof has invalid laundry_id
**Solution**: Contact admin to fix database

### Issue 4: Statistics show 0 but there are payment proofs
**Cause**: Payment proofs belong to other branches
**Solution**: This is correct - you only see your branch's statistics

### Issue 5: Can't see any payment proofs
**Possible Causes**:
1. No payment proofs submitted yet
2. All proofs belong to other branches
3. Database connection issue

**Solution**: 
- Check if customers have submitted payment proofs
- Verify your branch_id is correct
- Check Laravel logs for errors

---

## Database Verification Queries

### Check Your Branch ID
```sql
SELECT id, name, email, branch_id, role 
FROM users 
WHERE id = [your_user_id];
```

### Check Payment Proofs for Your Branch
```sql
SELECT pp.id, pp.status, pp.amount, l.tracking_number, l.branch_id
FROM payment_proofs pp
JOIN laundries l ON pp.laundry_id = l.id
WHERE l.branch_id = [your_branch_id]
ORDER BY pp.created_at DESC;
```

### Check Statistics
```sql
-- Pending count
SELECT COUNT(*) as pending_count
FROM payment_proofs pp
JOIN laundries l ON pp.laundry_id = l.id
WHERE l.branch_id = [your_branch_id] AND pp.status = 'pending';

-- Approved count
SELECT COUNT(*) as approved_count
FROM payment_proofs pp
JOIN laundries l ON pp.laundry_id = l.id
WHERE l.branch_id = [your_branch_id] AND pp.status = 'approved';

-- Rejected count
SELECT COUNT(*) as rejected_count
FROM payment_proofs pp
JOIN laundries l ON pp.laundry_id = l.id
WHERE l.branch_id = [your_branch_id] AND pp.status = 'rejected';
```

---

## Expected Behavior Summary

### ✅ What Should Work
- View payment verification page without errors
- See accurate statistics for your branch only
- See only your branch's payment proofs
- View payment proof details without 403 error
- Approve payment proofs
- Reject payment proofs
- Bulk approve multiple proofs
- Auto-refresh every 30 seconds (pending/all view)
- Receive notifications when new proofs submitted

### ❌ What Should NOT Work (By Design)
- Viewing other branches' payment proofs (403 error)
- Approving other branches' payment proofs (403 error)
- Seeing other branches' statistics
- Bulk approving other branches' proofs

---

## Notification Testing

### When Customer Submits Payment Proof
1. Customer uploads payment proof via mobile app
2. **You should receive notification** in notification bell
3. Notification should show:
   - Customer name
   - Tracking number
   - Amount paid
   - Reference number
4. Click notification to go to payment verification page

### When You Approve Payment
1. You approve payment proof
2. **Customer should receive notification** on mobile app
3. Notification should show:
   - Tracking number
   - Approval message
   - Any notes you added

### When You Reject Payment
1. You reject payment proof
2. **Customer should receive notification** on mobile app
3. Notification should show:
   - Tracking number
   - Rejection message
   - Rejection reason

---

## Troubleshooting Steps

### If Page Doesn't Load
1. Check Laravel logs: `tail -f storage/logs/laravel.log`
2. Check web server logs
3. Verify route exists: `php artisan route:list | grep payment`
4. Clear cache: `php artisan cache:clear`

### If 403 Errors Persist
1. Check your branch_id: `SELECT branch_id FROM users WHERE id = [your_id]`
2. Check payment proof's branch: `SELECT l.branch_id FROM payment_proofs pp JOIN laundries l ON pp.laundry_id = l.id WHERE pp.id = [proof_id]`
3. Check Laravel logs for specific error message
4. Run test script: `php artisan tinker` then paste test_payment_proof_access.php

### If Statistics Are Wrong
1. Verify branch filtering in controller
2. Check database queries
3. Clear cache: `php artisan cache:clear`
4. Refresh page

---

## Support

If you encounter any issues:
1. Check Laravel logs first
2. Verify your branch_id is set
3. Run database verification queries
4. Check the error message for specific cause
5. Review the fix documentation

All fixes have been applied and tested. The system should work correctly now!
