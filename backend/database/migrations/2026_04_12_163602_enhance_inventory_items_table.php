<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Supplier information
            $table->string('supplier_name')->nullable()->after('brand');
            $table->string('supplier_contact')->nullable()->after('supplier_name');
            
            // Product identification
            $table->string('barcode')->unique()->nullable()->after('sku');
            $table->string('image_path')->nullable()->after('barcode');
            
            // Storage and logistics
            $table->string('storage_location')->nullable()->after('image_path');
            $table->integer('lead_time_days')->default(0)->after('storage_location');
            
            // Expiration tracking
            $table->boolean('has_expiration')->default(false)->after('lead_time_days');
            $table->text('notes')->nullable()->after('has_expiration');
            
            // Audit fields
            $table->foreignId('created_by')->nullable()->after('notes')->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->after('created_by')->constrained('users')->nullOnDelete();
            $table->softDeletes()->after('updated_by');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropForeign(['created_by']);
            $table->dropForeign(['updated_by']);
            $table->dropColumn([
                'supplier_name',
                'supplier_contact',
                'barcode',
                'image_path',
                'storage_location',
                'lead_time_days',
                'has_expiration',
                'notes',
                'created_by',
                'updated_by',
            ]);
        });
    }
};
