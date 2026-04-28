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
            $table->string('pickup_proof_photo')->nullable()->after('picked_up_at');
            $table->timestamp('proof_uploaded_at')->nullable()->after('pickup_proof_photo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn(['pickup_proof_photo', 'proof_uploaded_at']);
        });
    }
};
