<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('central_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->decimal('current_stock', 10, 2)->default(0); // Total units in warehouse
            $table->decimal('cost_price', 10, 2)->default(0); // Current cost per unit
            $table->decimal('reorder_point', 10, 2)->default(0);
            $table->decimal('max_stock_level', 10, 2)->nullable();
            $table->timestamp('last_purchased_at')->nullable();
            $table->timestamps();
            
            $table->unique('inventory_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('central_stocks');
    }
};
