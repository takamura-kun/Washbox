<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // ROI tracking fields
            $table->decimal('marketing_cost', 10, 2)->default(0)->after('max_usage');
            $table->decimal('total_revenue', 10, 2)->default(0)->after('marketing_cost');
            $table->decimal('total_discounts', 10, 2)->default(0)->after('total_revenue');
            $table->decimal('roi_percentage', 8, 2)->nullable()->after('total_discounts');
            $table->timestamp('roi_last_calculated')->nullable()->after('roi_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn([
                'marketing_cost',
                'total_revenue', 
                'total_discounts',
                'roi_percentage',
                'roi_last_calculated'
            ]);
        });
    }
};