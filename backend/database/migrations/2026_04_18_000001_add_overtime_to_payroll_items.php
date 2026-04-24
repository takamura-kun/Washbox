<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->decimal('overtime_hours', 8, 2)->default(0)->after('hours_worked');
            $table->decimal('overtime_pay', 10, 2)->default(0)->after('gross_pay');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_items', function (Blueprint $table) {
            $table->dropColumn(['overtime_hours', 'overtime_pay']);
        });
    }
};
