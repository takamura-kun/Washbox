<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->text('manual_address')->nullable()->after('pickup_address')
                ->comment('User manually typed address from geotag input');
            $table->boolean('address_manually_edited')->default(false)->after('manual_address')
                ->comment('Flag indicating if address was manually entered by user');
        });
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn(['manual_address', 'address_manually_edited']);
        });
    }
};
