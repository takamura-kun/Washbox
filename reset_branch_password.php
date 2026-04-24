<?php

// Run this: php backend/reset_branch_password.php

require __DIR__.'/backend/vendor/autoload.php';

$app = require_once __DIR__.'/backend/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$branch = \App\Models\Branch::first();

if (!$branch) {
    echo "\n❌ No branches found in database!\n\n";
    exit(1);
}

$newPassword = 'WashBox2024!';
$branch->password = bcrypt($newPassword);
$branch->save();

echo "\n=== BRANCH PASSWORD RESET ===\n\n";
echo "Branch: {$branch->name}\n";
echo "Username: {$branch->username}\n";
echo "New Password: {$newPassword}\n\n";
echo "✅ Password updated successfully!\n\n";
echo "You can now login at: " . url('/branch/login') . "\n\n";
