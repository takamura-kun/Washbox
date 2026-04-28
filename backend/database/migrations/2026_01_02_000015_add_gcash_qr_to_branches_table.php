<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('gcash_qr_image')->nullable()->after('photo_url');
            $table->string('gcash_account_name')->nullable()->after('gcash_qr_image');
            $table->string('gcash_account_number')->nullable()->after('gcash_account_name');
        });
    }

    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['gcash_qr_image', 'gcash_account_name', 'gcash_account_number']);
        });
    }
};