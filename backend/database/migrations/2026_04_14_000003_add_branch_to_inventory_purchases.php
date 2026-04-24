<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_purchases', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->onDelete('set null');
            }
            
            if (!Schema::hasColumn('inventory_purchases', 'total_cost')) {
                $table->decimal('total_cost', 12, 2)->default(0)->after('grand_total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_purchases', function (Blueprint $table) {
            if (Schema::hasColumn('inventory_purchases', 'branch_id')) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            }
            
            if (Schema::hasColumn('inventory_purchases', 'total_cost')) {
                $table->dropColumn('total_cost');
            }
        });
    }
};
