<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('expense_categories')) {
            Schema::create('expense_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_system')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                $table->index('is_active');
                $table->index('is_system');
            });
        }

        if (!Schema::hasTable('expenses')) {
            Schema::create('expenses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
                $table->foreignId('expense_category_id')->constrained('expense_categories')->onDelete('cascade');
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('amount', 12, 2);
                $table->date('expense_date');
                $table->string('reference_no')->nullable();
                $table->string('attachment')->nullable();
                $table->text('notes')->nullable();
                $table->boolean('is_recurring')->default(false);
                $table->enum('source', ['manual', 'auto'])->default('manual');
                $table->foreignId('inventory_purchase_id')->nullable()->constrained('inventory_purchases')->onDelete('set null');
                $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
                $table->timestamps();
                
                $table->index('branch_id');
                $table->index('expense_category_id');
                $table->index('expense_date');
                $table->index('source');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
    }
};
