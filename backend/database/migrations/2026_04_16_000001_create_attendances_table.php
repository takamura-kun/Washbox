<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->constrained()->onDelete('cascade');
            $table->date('attendance_date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->time('break_start')->nullable();
            $table->time('break_end')->nullable();
            $table->decimal('hours_worked', 5, 2)->default(0);
            $table->enum('status', ['present', 'absent', 'half_day', 'late', 'on_leave'])->default('absent');
            $table->enum('shift_type', ['full_day', 'half_day', 'overtime'])->default('full_day');
            $table->string('time_in_photo')->nullable(); // Photo verification
            $table->string('time_out_photo')->nullable();
            $table->string('time_in_ip')->nullable();
            $table->string('time_out_ip')->nullable();
            $table->json('time_in_location')->nullable(); // GPS coordinates
            $table->json('time_out_location')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            // Prevent duplicate entries
            $table->unique(['user_id', 'attendance_date']);
            
            // Indexes for performance
            $table->index(['branch_id', 'attendance_date']);
            $table->index(['user_id', 'attendance_date']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
