<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->decimal('inventory_cost', 10, 2)->nullable()->after('storage_fee');
        });
    }

    public function down(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->dropColumn('inventory_cost');
        });
    }
};
