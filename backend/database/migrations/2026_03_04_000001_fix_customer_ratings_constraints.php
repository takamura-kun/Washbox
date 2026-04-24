<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if the problematic constraint exists and drop it
        $constraintExists = DB::select("
            SELECT CONSTRAINT_NAME 
            FROM information_schema.TABLE_CONSTRAINTS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'customer_ratings' 
            AND CONSTRAINT_NAME = 'customer_ratings_branch_customer_unique'
        ");

        if (!empty($constraintExists)) {
            Schema::table('customer_ratings', function (Blueprint $table) {
                $table->dropUnique('customer_ratings_branch_customer_unique');
            });
        }

        // Since MariaDB doesn't support partial indexes, we'll handle the constraint
        // at the application level in the CustomerRatingController
        // The controller will check for existing branch ratings when laundry_id is NULL
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore the original constraint if needed
        try {
            Schema::table('customer_ratings', function (Blueprint $table) {
                $table->unique(['branch_id', 'customer_id'], 'customer_ratings_branch_customer_unique');
            });
        } catch (Exception $e) {
            // Ignore if constraint already exists
        }
    }
};