<?php

// Run this from the backend directory: php check_branch_credentials.php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$branches = \App\Models\Branch::select('id', 'name', 'code', 'username')->get();

echo "\n=== BRANCH CREDENTIALS ===\n\n";

foreach ($branches as $branch) {
    echo "Branch: {$branch->name}\n";
    echo "Code: {$branch->code}\n";
    echo "Username: {$branch->username}\n";
    echo "Has Password: " . (!empty($branch->password) ? 'Yes' : 'No') . "\n";
    echo "---\n";
}

echo "\nDefault password for auto-generated branches: branch123\n\n";
