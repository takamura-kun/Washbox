# Branch Payment Verification - Routes & Authentication Guide

## Route Information

### Branch Payment Verification Routes

```
GET     /branch/payments/verification                    → branch.payments.verification.index
GET     /branch/payments/verification/{paymentProof}     → branch.payments.verification.show
POST    /branch/payments/verification/{paymentProof}/approve → branch.payments.verification.approve
POST    /branch/payments/verification/{paymentProof}/reject  → branch.payments.verification.reject
POST    /branch/payments/verification/bulk-approve       → branch.payments.verification.bulk-approve
```

### Middleware Applied

```php
Route::middleware(['auth:branch', 'branch'])
    ->prefix('branch')
    ->name('branch.')
    ->group(function () {
        // Payment verification routes here
    });
```

**Middleware Breakdown:**
1. **`auth:branch`** - Requires authentication via the `branch` guard
2. **`branch`** - Custom middleware (BranchMiddleware) that:
   - Checks if user is authenticated with branch guard, OR
   - Allows regular authenticated users IF they have a `branch_id`
   - Checks if branch is active

---

## Authentication Guards

### Branch Guard Configuration

```php
// config/auth.php

'guards' => [
    'branch' => [
        'driver' => 'session',
        'provider' => 'branches',  // ⚠️ Uses Branch model
    ],
],

'providers' => [
    'branches' => [
        'driver' => 'eloquent',
        'model' => App\Models\Branch::class,  // ⚠️ Branch entity, not User
    ],
],
```

**Important:** The `branch` guard authenticates as a `Branch` entity, not a `User` entity.

---

## Why You're Getting 403 Error

### Current Situation

You're logged in as:
- **User**: System Administrator
- **Role**: Admin
- **Guard**: `web` or `admin` (not `branch`)
- **Branch ID**: NULL ❌

### What the Middleware Expects

The `BranchMiddleware` allows access if:
1. Authenticated via `branch` guard (as a Branch entity), OR
2. Authenticated via any guard AND has `branch_id` set

### Your Issue

You're authenticated as an admin user, but your `branch_id` is NULL, so the middleware denies access.

---

## Solutions

### Solution 1: Assign Your Account to a Branch ⭐ RECOMMENDED

#### Via Laravel Tinker
```bash
cd backend
php artisan tinker
```

Then run:
```php
// Find your user
$user = \App\Models\User::where('email', 'your-email@example.com')->first();

// Check current branch_id
echo "Current branch_id: " . ($user->branch_id ?? 'NULL');

// Show available branches
\App\Models\Branch::all(['id', 'name', 'is_active']);

// Assign to a branch (replace 1 with actual branch ID)
$user->branch_id = 1;
$user->save();

echo "\n✅ Success! Branch ID: " . $user->branch_id;
echo "\n⚠️  Log out and log back in for changes to take effect.";
```

#### Via Database
```sql
-- Check available branches
SELECT id, name, is_active FROM branches WHERE is_active = 1;

-- Check your user
SELECT id, name, email, role, branch_id FROM users WHERE email = 'your-email@example.com';

-- Assign to branch (replace IDs)
UPDATE users SET branch_id = 1 WHERE id = YOUR_USER_ID;
```

#### Via Helper Script
```bash
cd backend
php artisan tinker
```

Then paste the contents of `assign_branch_to_user.php`

---

### Solution 2: Use Admin Payment Verification

Access the admin version instead:
```
/admin/payments/verification
```

This shows payment proofs from ALL branches and doesn't require a branch_id.

---

### Solution 3: Create/Use a Branch Staff Account

1. Create a new user with role `staff`
2. Assign them to a branch
3. Login with that account
4. Access `/branch/payments/verification`

---

## Step-by-Step Fix

### 1. Check Your Current Status

```bash
cd backend
php artisan tinker
```

```php
$user = auth()->user();
echo "User ID: " . $user->id . "\n";
echo "Name: " . $user->name . "\n";
echo "Email: " . $user->email . "\n";
echo "Role: " . $user->role . "\n";
echo "Branch ID: " . ($user->branch_id ?? 'NULL') . "\n";
```

### 2. List Available Branches

```php
$branches = \App\Models\Branch::where('is_active', true)->get(['id', 'name']);
foreach ($branches as $branch) {
    echo "ID: {$branch->id} - {$branch->name}\n";
}
```

### 3. Assign to a Branch

```php
$user = auth()->user();
$user->branch_id = 1;  // Replace with actual branch ID
$user->save();
echo "✅ Assigned to branch ID: " . $user->branch_id;
```

### 4. Log Out and Log Back In

```
1. Click your profile in top right
2. Click "Logout"
3. Log back in with same credentials
4. Navigate to /branch/payments/verification
5. Should work now! ✅
```

---

## Verification

After assigning branch_id, verify it worked:

```bash
php artisan tinker
```

```php
$user = \App\Models\User::find(YOUR_USER_ID);
echo "Branch ID: " . $user->branch_id . "\n";

if ($user->branch_id) {
    echo "✅ Branch assigned!\n";
    $branch = \App\Models\Branch::find($user->branch_id);
    echo "Branch Name: " . $branch->name . "\n";
    echo "Branch Active: " . ($branch->is_active ? 'Yes' : 'No') . "\n";
} else {
    echo "❌ No branch assigned\n";
}
```

---

## Testing After Fix

### 1. Access Payment Verification Page
```
http://your-domain.com/branch/payments/verification
```

**Expected:** Page loads without 403 error

### 2. Check Statistics
**Expected:** Shows counts for YOUR assigned branch only

### 3. View Payment Proof
Click "View Details" on any payment proof

**Expected:** Opens without 403 error

### 4. Approve/Reject Payment
Try approving or rejecting a payment

**Expected:** Works without errors

---

## Common Issues

### Issue: "Your account is not assigned to any branch"
**Cause:** `branch_id` is NULL
**Solution:** Assign branch_id using one of the methods above

### Issue: Still getting 403 after assigning branch
**Cause:** Session not updated
**Solution:** Log out and log back in

### Issue: "Branch account has been deactivated"
**Cause:** The branch you're assigned to has `is_active = false`
**Solution:** Assign to an active branch or activate the branch

### Issue: Can't see any payment proofs
**Cause:** No payment proofs exist for your branch
**Solution:** This is normal if no customers have submitted proofs

---

## Route Testing Commands

### Check if routes exist
```bash
php artisan route:list | grep "branch.payments.verification"
```

### Check route details
```bash
php artisan route:list --path=branch/payments
```

### Test route access
```bash
# In browser or Postman
GET http://your-domain.com/branch/payments/verification
```

---

## Database Schema Reference

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    email VARCHAR(255),
    role ENUM('admin', 'staff', ...),
    branch_id BIGINT NULL,  -- ⚠️ This must be set for branch access
    is_active BOOLEAN,
    ...
);
```

### Branches Table
```sql
CREATE TABLE branches (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    is_active BOOLEAN,
    ...
);
```

### Payment Proofs Table
```sql
CREATE TABLE payment_proofs (
    id BIGINT PRIMARY KEY,
    laundry_id BIGINT,
    status ENUM('pending', 'approved', 'rejected'),
    amount DECIMAL(10,2),
    ...
);
```

### Laundries Table
```sql
CREATE TABLE laundries (
    id BIGINT PRIMARY KEY,
    tracking_number VARCHAR(255),
    branch_id BIGINT,  -- Links to branch
    customer_id BIGINT,
    ...
);
```

---

## Summary

**Problem:** Your admin account has no `branch_id`, so the middleware denies access.

**Solution:** Assign your account to a branch using:
```php
$user = \App\Models\User::find(YOUR_ID);
$user->branch_id = BRANCH_ID;
$user->save();
```

**Then:** Log out and log back in.

**Result:** You can now access `/branch/payments/verification` ✅

---

## Quick Fix Script

Run this one-liner in tinker:

```php
auth()->user()->update(['branch_id' => \App\Models\Branch::where('is_active', true)->first()->id]);
```

Then log out and log back in!
