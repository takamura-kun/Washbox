<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->string('area_name'); // e.g., "Dumaguete City", "Sibulan"
            $table->string('area_type')->default('city'); // city, municipality, barangay
            $table->boolean('is_free')->default(true); // true = free, false = with fee
            $table->decimal('delivery_fee', 10, 2)->default(0.00); // fee if not free
            $table->text('coverage_notes')->nullable(); // e.g., "All barangays included"
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['branch_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_areas');
    }
};
