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
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Customer proof photo (uploaded when requesting pickup)
            $table->string('customer_proof_photo')->nullable()->after('notes');
            $table->timestamp('customer_proof_uploaded_at')->nullable()->after('customer_proof_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn(['customer_proof_photo', 'customer_proof_uploaded_at']);
        });
    }
};
