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
        // Check if the unique constraint exists before dropping it
        $indexExists = DB::select("SHOW INDEX FROM customer_ratings WHERE Key_name = 'customer_ratings_branch_customer_unique'");
        
        if (!empty($indexExists)) {
            Schema::table('customer_ratings', function (Blueprint $table) {
                $table->dropUnique('customer_ratings_branch_customer_unique');
            });
        }

        // MariaDB doesn't support partial indexes, so we'll skip this
        // The application logic will handle preventing duplicate branch ratings
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the partial unique index
        DB::statement('DROP INDEX IF EXISTS customer_ratings_branch_only_unique');
        
        // Restore the original constraint
        Schema::table('customer_ratings', function (Blueprint $table) {
            $table->unique(['branch_id', 'customer_id'], 'customer_ratings_branch_customer_unique');
        });
    }
};
