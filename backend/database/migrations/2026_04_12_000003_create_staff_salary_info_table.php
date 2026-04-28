<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('staff_salary_info', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('set null');
            $table->enum('salary_type', ['monthly', 'daily', 'hourly'])->default('monthly');
            $table->decimal('base_rate', 10, 2);
            $table->enum('pay_period', ['weekly', 'bi-weekly', 'monthly'])->default('monthly');
            $table->date('effectivity_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_salary_info');
    }
};
