<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update role enum to remove 'staff' from login roles
        // Staff records remain but are for HR/payroll purposes only
        // Note: This doesn't delete staff records, just changes their purpose
        
        // Add a comment to clarify staff role purpose
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'customer') DEFAULT 'customer' COMMENT 'admin=system admin, staff=HR record only (no login), customer=customer account'");
        
        // Optionally add a flag to indicate this is HR-only record
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_hr_record')->default(false)->after('role')->comment('True if this is staff HR record (no login access)');
        });
        
        // Mark all existing staff as HR records
        DB::table('users')->where('role', 'staff')->update(['is_hr_record' => true]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_hr_record');
        });
        
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'staff', 'customer') DEFAULT 'customer'");
    }
};
