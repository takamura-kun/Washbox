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
        Schema::create('payment_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('event_type'); // proof_submitted, proof_approved, proof_rejected, refund_issued
            $table->decimal('amount', 10, 2);
            $table->string('status'); // pending, approved, rejected, refunded
            $table->json('data')->nullable(); // Additional event data
            $table->timestamps();

            // Indexes for faster queries
            $table->index('laundry_id');
            $table->index('customer_id');
            $table->index('event_type');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_events');
    }
};
