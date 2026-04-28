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
                $table->foreignId('branch_id')->nullable()->after('id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_purchases', 'total_cost')) {
                $table->decimal('total_cost', 15, 2)->default(0)->after('grand_total');
            }
            if (!Schema::hasColumn('inventory_purchases', 'purchase_order_number')) {
                $table->string('purchase_order_number')->nullable()->after('reference_no');
            }
            if (!Schema::hasColumn('inventory_purchases', 'supplier_id')) {
                $table->unsignedBigInteger('supplier_id')->nullable()->after('supplier');
            }
        });
    }

    public function down(): void
    {
        Schema::table('inventory_purchases', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropColumn(['branch_id', 'total_cost', 'purchase_order_number', 'supplier_id']);
        });
    }
};
