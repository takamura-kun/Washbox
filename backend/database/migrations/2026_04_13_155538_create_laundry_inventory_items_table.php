<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundries_id')->constrained('laundries')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->decimal('price_at_purchase', 10, 2);
            $table->decimal('quantity', 10, 2)->default(1);
            $table->timestamps();
            
            $table->unique(['laundries_id', 'inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_inventory_items');
    }
};
