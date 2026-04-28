<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->foreignId('inventory_item_id')->constrained('inventory_items')->onDelete('cascade');
            $table->enum('type', ['damaged', 'expired', 'lost', 'found', 'correction', 'theft', 'spoilage'])->default('damaged');
            $table->integer('quantity'); // Negative for reductions, positive for additions
            $table->decimal('value_loss', 10, 2)->default(0); // Financial impact
            $table->string('reason');
            $table->text('notes')->nullable();
            $table->string('photo_proof')->nullable(); // Photo of damaged items
            $table->foreignId('adjusted_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            // Indexes
            $table->index(['branch_id', 'created_at']);
            $table->index(['inventory_item_id', 'type']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
