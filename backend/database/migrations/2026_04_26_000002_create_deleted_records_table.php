<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deleted_records', function (Blueprint $table) {
            $table->id();
            $table->string('model_type');                     // e.g. App\Models\Customer
            $table->string('model_label');                    // human-readable name/number
            $table->string('module');                         // customer, staff, laundry, etc.
            $table->unsignedBigInteger('original_id');        // the original model's id
            $table->json('data');                             // full snapshot of the record
            $table->string('deleted_by_name')->nullable();    // who deleted it
            $table->string('deleted_by_type')->nullable();    // App\Models\User or Branch
            $table->unsignedBigInteger('deleted_by_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('deleted_at');

            $table->index(['model_type', 'original_id']);
            $table->index(['module', 'deleted_at']);
            $table->index('deleted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deleted_records');
    }
};
