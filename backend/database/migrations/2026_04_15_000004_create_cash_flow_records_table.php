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
        Schema::create('cash_flow_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->date('record_date')->index();
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('cash_inflow', 15, 2)->default(0);
            $table->decimal('cash_outflow', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->json('inflow_breakdown')->nullable(); // {laundry: 1000, retail: 500}
            $table->json('outflow_breakdown')->nullable(); // {expenses: 300, payroll: 200}
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Unique constraint: one record per branch per day
            $table->unique(['branch_id', 'record_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_flow_records');
    }
};
