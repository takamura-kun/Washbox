<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('inventory_purchases', function (Blueprint $table) {
            // Expiration date tracking
            if (!Schema::hasColumn('inventory_purchases', 'expiration_date')) {
                $table->date('expiration_date')->nullable()->after('purchase_date');
            }

            // Batch/lot number for traceability
            if (!Schema::hasColumn('inventory_purchases', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('expiration_date');
            }

            // Storage location/shelf info
            if (!Schema::hasColumn('inventory_purchases', 'storage_location')) {
                $table->string('storage_location')->nullable()->after('batch_number');
            }
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            // Image for product
            if (!Schema::hasColumn('inventory_items', 'image_path')) {
                $table->string('image_path')->nullable()->after('description');
            }

            // Track if item is perishable
            if (!Schema::hasColumn('inventory_items', 'is_perishable')) {
                $table->boolean('is_perishable')->default(false)->after('image_path');
            }

            // Shelf life in days
            if (!Schema::hasColumn('inventory_items', 'shelf_life_days')) {
                $table->integer('shelf_life_days')->nullable()->after('is_perishable');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inventory_purchases', function (Blueprint $table) {
            $table->dropColumn(['expiration_date', 'batch_number', 'storage_location']);
        });

        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'is_perishable', 'shelf_life_days']);
        });
    }
};
