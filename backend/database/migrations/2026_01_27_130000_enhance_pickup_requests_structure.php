<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Standardize phone number field
            if (!Schema::hasColumn('pickup_requests', 'phone_number')) {
                $table->string('phone_number')->after('preferred_time');
            }
            
            // Add missing fields for better tracking (check if they don't exist)
            if (!Schema::hasColumn('pickup_requests', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('pickup_requests', 'special_instructions')) {
                $table->text('special_instructions')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('pickup_requests', 'estimated_duration_minutes')) {
                $table->decimal('estimated_duration_minutes', 5, 2)->nullable()->after('special_instructions');
            }
            if (!Schema::hasColumn('pickup_requests', 'actual_duration_minutes')) {
                $table->decimal('actual_duration_minutes', 5, 2)->nullable()->after('estimated_duration_minutes');
            }
            
            // Staff location tracking
            if (!Schema::hasColumn('pickup_requests', 'staff_latitude')) {
                $table->decimal('staff_latitude', 10, 8)->nullable()->after('longitude');
            }
            if (!Schema::hasColumn('pickup_requests', 'staff_longitude')) {
                $table->decimal('staff_longitude', 11, 8)->nullable()->after('staff_latitude');
            }
            if (!Schema::hasColumn('pickup_requests', 'location_updated_at')) {
                $table->timestamp('location_updated_at')->nullable()->after('staff_longitude');
            }
            
            // Enhanced status tracking
            if (!Schema::hasColumn('pickup_requests', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('accepted_at');
            }
            if (!Schema::hasColumn('pickup_requests', 'arrived_at')) {
                $table->timestamp('arrived_at')->nullable()->after('en_route_at');
            }
            if (!Schema::hasColumn('pickup_requests', 'status_history')) {
                $table->json('status_history')->nullable()->after('picked_up_at');
            }
            
            // Customer rating and feedback
            if (!Schema::hasColumn('pickup_requests', 'customer_rating')) {
                $table->tinyInteger('customer_rating')->nullable()->after('cancellation_reason');
            }
            if (!Schema::hasColumn('pickup_requests', 'customer_feedback')) {
                $table->text('customer_feedback')->nullable()->after('customer_rating');
            }
            if (!Schema::hasColumn('pickup_requests', 'feedback_at')) {
                $table->timestamp('feedback_at')->nullable()->after('customer_feedback');
            }
        });
        
        // Migrate existing contact_phone to phone_number if exists
        if (Schema::hasColumn('pickup_requests', 'contact_phone')) {
            DB::statement("UPDATE pickup_requests SET phone_number = contact_phone WHERE phone_number IS NULL AND contact_phone IS NOT NULL");
            Schema::table('pickup_requests', function (Blueprint $table) {
                $table->dropColumn('contact_phone');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropColumn([
                'contact_person', 'special_instructions', 'estimated_duration_minutes',
                'actual_duration_minutes', 'staff_latitude', 'staff_longitude',
                'location_updated_at', 'dispatched_at', 'arrived_at', 'status_history',
                'customer_rating', 'customer_feedback', 'feedback_at'
            ]);
        });
    }
};