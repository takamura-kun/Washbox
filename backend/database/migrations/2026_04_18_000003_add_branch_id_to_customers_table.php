<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add branch_id column after preferred_branch_id
            $table->foreignId('branch_id')->nullable()->after('longitude')->constrained('branches')->onDelete('set null');
            
            // Add index for better query performance
            $table->index('branch_id');
        });

        // Copy existing preferred_branch_id values to branch_id
        DB::statement('UPDATE customers SET branch_id = preferred_branch_id WHERE preferred_branch_id IS NOT NULL');
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
