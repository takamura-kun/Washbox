<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('pickup_requests', 'promotion_id')) {
                $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete()->after('service_id');
            }
            if (!Schema::hasColumn('pickup_requests', 'promo_code')) {
                $table->string('promo_code', 50)->nullable()->after('promotion_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropForeign(['promotion_id']);
            $table->dropColumn(['promotion_id', 'promo_code']);
        });
    }
};
