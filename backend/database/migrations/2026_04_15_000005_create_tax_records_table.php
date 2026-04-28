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
        Schema::create('tax_records', function (Blueprint $table) {
            $table->id();
            $table->string('tax_type'); // VAT, Withholding Tax, Income Tax
            $table->string('period'); // 2026-Q1, 2026-03
            $table->date('period_start');
            $table->date('period_end');
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('gross_sales', 15, 2)->default(0);
            $table->decimal('vat_exempt_sales', 15, 2)->default(0);
            $table->decimal('vat_zero_rated_sales', 15, 2)->default(0);
            $table->decimal('vatable_sales', 15, 2)->default(0);
            $table->decimal('output_vat', 15, 2)->default(0);
            $table->decimal('input_vat', 15, 2)->default(0);
            $table->decimal('net_vat_payable', 15, 2)->default(0);
            $table->decimal('withholding_tax', 15, 2)->default(0);
            $table->enum('status', ['draft', 'filed', 'paid'])->default('draft');
            $table->date('filed_date')->nullable();
            $table->date('payment_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('prepared_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Indexes
            $table->index(['period', 'tax_type']);
            $table->index(['branch_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_records');
    }
};
