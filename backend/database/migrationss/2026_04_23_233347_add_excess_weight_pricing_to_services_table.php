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
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('excess_weight_charge_per_kg', 10, 2)->nullable()->after('max_weight');
            $table->boolean('allow_excess_weight')->default(false)->after('excess_weight_charge_per_kg');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn(['excess_weight_charge_per_kg', 'allow_excess_weight']);
        });
    }
};
