<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotion_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')->constrained('promotions')->cascadeOnDelete();
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
            $table->decimal('quantity_per_use', 10, 2)->default(1.00)->comment('Quantity deducted per promotion use');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['promotion_id', 'inventory_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotion_items');
    }
};
