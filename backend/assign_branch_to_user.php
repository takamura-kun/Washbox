<?php

/**
 * Script to assign a branch to a user account
 * 
 * Run this in Laravel Tinker:
 * php artisan tinker
 * 
 * Then paste this code
 */

echo "=== Assign Branch to User ===\n\n";

// Step 1: Show all branches
echo "Available Branches:\n";
$branches = \App\Models\Branch::all(['id', 'name', 'is_active']);
foreach ($branches as $branch) {
    $status = $branch->is_active ? '✅ Active' : '❌ Inactive';
    echo "  ID: {$branch->id} - {$branch->name} ({$status})\n";
}
echo "\n";

// Step 2: Show current user
$currentUser = auth()->user();
if ($currentUser) {
    echo "Current Logged In User:\n";
    echo "  ID: {$currentUser->id}\n";
    echo "  Name: {$currentUser->name}\n";
    echo "  Email: {$currentUser->email}\n";
    echo "  Role: {$currentUser->role}\n";
    echo "  Current Branch ID: " . ($currentUser->branch_id ?? 'NULL') . "\n";
    echo "\n";
    
    // Step 3: Assign to first active branch
    $firstBranch = \App\Models\Branch::where('is_active', true)->first();
    if ($firstBranch) {
        echo "Assigning user to branch: {$firstBranch->name} (ID: {$firstBranch->id})\n";
        $currentUser->branch_id = $firstBranch->id;
        $currentUser->save();
        echo "✅ Success! User assigned to branch.\n";
        echo "New Branch ID: {$currentUser->branch_id}\n";
        echo "\n";
        echo "⚠️  IMPORTANT: Please log out and log back in for changes to take effect.\n";
    } else {
        echo "❌ No active branches found!\n";
    }
} else {
    echo "❌ No user is currently logged in.\n";
    echo "\nTo assign a specific user, run:\n";
    echo "\$user = \\App\\Models\\User::find(USER_ID);\n";
    echo "\$user->branch_id = BRANCH_ID;\n";
    echo "\$user->save();\n";
}

echo "\n=== Done ===\n";
