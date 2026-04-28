<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::table('laundries', function (Blueprint $table) {
        if (!$this->indexExists('laundries', 'laundries_status_index')) {
            $table->index('status');
        }
        if (!$this->indexExists('laundries', 'laundries_branch_id_index')) {
            $table->index('branch_id');
        }
        if (!$this->indexExists('laundries', 'laundries_customer_id_index')) {
            $table->index('customer_id');
        }
        if (!$this->indexExists('laundries', 'laundries_created_at_index')) {
            $table->index('created_at');
        }
    });
}

private function indexExists(string $table, string $index): bool
{
    return collect(DB::select("SHOW INDEX FROM `{$table}`"))
        ->pluck('Key_name')
        ->contains($index);
}

public function down(): void
{
    Schema::table('laundries', function (Blueprint $table) {
        $table->dropIndex(['status']);
        $table->dropIndex(['branch_id']);
        $table->dropIndex(['customer_id']);
        $table->dropIndex(['created_at']);
    });
}

};
