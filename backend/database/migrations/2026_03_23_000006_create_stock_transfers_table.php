<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_distributions', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no')->unique();
            $table->date('distribution_date');
            $table->text('notes')->nullable();
            $table->foreignId('distributed_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // Create inventory_distribution_items table for line items
        Schema::create('inventory_distribution_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_distribution_id')->constrained('inventory_distributions')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('quantity', 10, 2); // in distribution units
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_distribution_items');
        Schema::dropIfExists('inventory_distributions');
    }
};
