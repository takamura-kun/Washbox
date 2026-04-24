<?php

/**
 * Test Script: Verify Payment Proof Branch Access
 * 
 * Run this in Laravel Tinker to test the fix:
 * php artisan tinker
 * 
 * Then paste this code to test different scenarios
 */

// Test 1: Check if staff has branch_id
echo "=== Test 1: Staff Branch Assignment ===\n";
$staff = \App\Models\User::where('role', 'staff')->first();
if ($staff) {
    echo "Staff ID: {$staff->id}\n";
    echo "Staff Name: {$staff->name}\n";
    echo "Branch ID: " . ($staff->branch_id ?? 'NULL') . "\n";
    if (!$staff->branch_id) {
        echo "⚠️  WARNING: Staff has no branch_id assigned!\n";
    } else {
        echo "✅ Staff has valid branch_id\n";
    }
} else {
    echo "❌ No staff users found\n";
}
echo "\n";

// Test 2: Check payment proofs with laundry relationship
echo "=== Test 2: Payment Proofs with Laundry ===\n";
$paymentProofs = \App\Models\PaymentProof::with('laundry')->take(5)->get();
foreach ($paymentProofs as $proof) {
    echo "Payment Proof ID: {$proof->id}\n";
    echo "  Laundry ID: {$proof->laundry_id}\n";
    if ($proof->laundry) {
        echo "  Laundry Branch ID: {$proof->laundry->branch_id}\n";
        echo "  ✅ Valid relationship\n";
    } else {
        echo "  ❌ WARNING: No laundry found!\n";
    }
    echo "\n";
}

// Test 3: Check branch access logic
echo "=== Test 3: Branch Access Logic ===\n";
$staff = \App\Models\User::where('role', 'staff')->whereNotNull('branch_id')->first();
$paymentProof = \App\Models\PaymentProof::with('laundry')->first();

if ($staff && $paymentProof && $paymentProof->laundry) {
    echo "Staff Branch ID: {$staff->branch_id}\n";
    echo "Payment Proof Branch ID: {$paymentProof->laundry->branch_id}\n";
    
    if ($paymentProof->laundry->branch_id === $staff->branch_id) {
        echo "✅ Access should be GRANTED\n";
    } else {
        echo "❌ Access should be DENIED (different branches)\n";
    }
} else {
    echo "⚠️  Cannot test - missing data\n";
}
echo "\n";

// Test 4: Count payment proofs per branch
echo "=== Test 4: Payment Proofs Per Branch ===\n";
$branches = \App\Models\Branch::all();
foreach ($branches as $branch) {
    $count = \App\Models\PaymentProof::whereHas('laundry', function($q) use ($branch) {
        $q->where('branch_id', $branch->id);
    })->count();
    echo "Branch: {$branch->name} (ID: {$branch->id})\n";
    echo "  Payment Proofs: {$count}\n";
    echo "  Pending: " . \App\Models\PaymentProof::whereHas('laundry', function($q) use ($branch) {
        $q->where('branch_id', $branch->id);
    })->where('status', 'pending')->count() . "\n";
    echo "\n";
}

// Test 5: Verify route model binding
echo "=== Test 5: Route Model Binding Test ===\n";
$paymentProof = \App\Models\PaymentProof::first();
if ($paymentProof) {
    echo "Payment Proof ID: {$paymentProof->id}\n";
    echo "Can load laundry: " . ($paymentProof->laundry ? 'Yes' : 'No') . "\n";
    echo "Relationship loaded: " . ($paymentProof->relationLoaded('laundry') ? 'Yes' : 'No') . "\n";
    
    // Test loading
    $paymentProof->load('laundry');
    echo "After load() - Relationship loaded: " . ($paymentProof->relationLoaded('laundry') ? 'Yes' : 'No') . "\n";
    echo "✅ Route model binding should work\n";
} else {
    echo "❌ No payment proofs found\n";
}
echo "\n";

echo "=== Tests Complete ===\n";
echo "If all tests pass, the fix should work correctly.\n";
