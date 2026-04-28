<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->timestamp('out_for_delivery_at')->nullable()->after('ready_at');
            $table->timestamp('delivered_at')->nullable()->after('out_for_delivery_at');
        });

        Schema::table('laundry_status_histories', function (Blueprint $table) {
            // status column is already VARCHAR(20), no enum change needed
        });
    }

    public function down(): void
    {
        Schema::table('laundries', function (Blueprint $table) {
            $table->dropColumn(['out_for_delivery_at', 'delivered_at']);
        });
    }
};
