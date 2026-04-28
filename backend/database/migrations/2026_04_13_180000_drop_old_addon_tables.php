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
        // Drop pivot tables first (foreign key constraints)
        Schema::dropIfExists('laundry_addon');
        Schema::dropIfExists('laundry_service_addon');
        
        // Drop main add_ons table
        Schema::dropIfExists('add_ons');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate add_ons table
        Schema::create('add_ons', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Recreate laundry_addon pivot table
        Schema::create('laundry_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundries_id')->constrained('laundries')->onDelete('cascade');
            $table->foreignId('add_on_id')->constrained('add_ons')->onDelete('cascade');
            $table->decimal('price_at_purchase', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });

        // Recreate laundry_service_addon pivot table
        Schema::create('laundry_service_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundries_id')->constrained('laundries')->onDelete('cascade');
            $table->foreignId('add_on_id')->constrained('add_ons')->onDelete('cascade');
            $table->decimal('price_at_purchase', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();
        });
    }
};
