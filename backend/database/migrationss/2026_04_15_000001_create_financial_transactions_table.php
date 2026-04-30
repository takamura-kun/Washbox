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
        Schema::create('financial_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->enum('type', ['income', 'expense', 'transfer'])->index();
            $table->enum('category', [
                'laundry_sale',
                'retail_sale',
                'pickup_fee',
                'delivery_fee',
                'expense',
                'payroll',
                'inventory_purchase',
                'refund',
                'adjustment',
                'other'
            ])->index();
            $table->decimal('amount', 15, 2);
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->string('reference_type')->nullable(); // Laundry, RetailSale, Expense, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->date('transaction_date')->index();
            $table->enum('status', ['pending', 'completed', 'cancelled', 'reversed'])->default('completed')->index();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['reference_type', 'reference_id']);
            $table->index(['transaction_date', 'type']);
            $table->index(['branch_id', 'transaction_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('financial_transactions');
    }
};
