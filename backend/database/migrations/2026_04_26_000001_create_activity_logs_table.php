<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event');                          // created, updated, deleted, login, logout, status_changed, etc.
            $table->string('description');                    // Human-readable description
            $table->string('module');                         // laundry, pickup, staff, finance, inventory, attendance, etc.
            $table->string('causer_type')->nullable();        // App\Models\User or App\Models\Branch
            $table->unsignedBigInteger('causer_id')->nullable();
            $table->string('causer_name')->nullable();        // Snapshot of name at time of action
            $table->string('subject_type')->nullable();       // The model being acted on
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->string('subject_label')->nullable();      // Snapshot label (e.g. tracking number)
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->json('properties')->nullable();           // Extra context (old/new values, amounts, etc.)
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();

            $table->index(['causer_type', 'causer_id']);
            $table->index(['subject_type', 'subject_id']);
            $table->index(['module', 'created_at']);
            $table->index(['branch_id', 'created_at']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
