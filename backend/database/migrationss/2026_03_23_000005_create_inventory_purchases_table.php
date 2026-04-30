<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_purchases', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('purchase_date');
            $table->string('supplier')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('purchased_by')->constrained('users')->onDelete('cascade');
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->timestamps();
        });

        // Create inventory_purchase_items table for line items
        Schema::create('inventory_purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_purchase_id')->constrained('inventory_purchases')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->string('purchase_unit'); // case, box, sack
            $table->decimal('quantity', 10, 2); // quantity in purchase units
            $table->decimal('cost_per_bulk', 10, 2); // cost per purchase unit
            $table->decimal('cost_per_unit', 10, 2); // cost per distribution unit
            $table->decimal('units_received', 10, 2); // total distribution units received
            $table->decimal('total_cost', 10, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_purchase_items');
        Schema::dropIfExists('inventory_purchases');
    }
};
