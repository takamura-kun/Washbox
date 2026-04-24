<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix data inconsistencies and standardize pickup_requests structure
     */
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            // 1. Fix phone number field inconsistency
            if (!Schema::hasColumn('pickup_requests', 'phone_number')) {
                $table->string('phone_number')->after('preferred_time');
            }
            
            // 2. Add customer_address_id relationship (already added in previous migration)
            if (!Schema::hasColumn('pickup_requests', 'customer_address_id')) {
                $table->foreignId('customer_address_id')
                      ->nullable()
                      ->after('customer_id')
                      ->constrained('customer_addresses')
                      ->nullOnDelete()
                      ->comment('Links to saved address if one was used');
            }
            
            // 3. Add delivery address fields for consistency
            if (!Schema::hasColumn('pickup_requests', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('pickup_address');
                $table->decimal('delivery_latitude', 10, 8)->nullable()->after('longitude');
                $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
            }
            
            // 4. Add contact person field
            if (!Schema::hasColumn('pickup_requests', 'contact_person')) {
                $table->string('contact_person')->nullable()->after('phone_number');
            }
            
            // 5. Rename 'notes' to 'special_instructions' for clarity
            if (Schema::hasColumn('pickup_requests', 'notes') && !Schema::hasColumn('pickup_requests', 'special_instructions')) {
                $table->text('special_instructions')->nullable()->after('contact_person');
            }
            
            // 6. Add estimated weight field
            if (!Schema::hasColumn('pickup_requests', 'estimated_weight')) {
                $table->decimal('estimated_weight', 5, 2)->nullable()->after('special_instructions')->comment('Estimated weight in kg');
            }
            
            // 7. Fix laundry relationship - rename laundries_id to laundry_id
            if (Schema::hasColumn('pickup_requests', 'laundries_id') && !Schema::hasColumn('pickup_requests', 'laundry_id')) {
                $table->unsignedBigInteger('laundry_id')->nullable()->after('cancelled_by');
                $table->index('laundry_id');
            }
            
            // 8. Add proof photo fields (if not exists from previous migration)
            if (!Schema::hasColumn('pickup_requests', 'pickup_proof_photo')) {
                $table->string('pickup_proof_photo')->nullable()->after('laundry_id');
                $table->timestamp('proof_uploaded_at')->nullable()->after('pickup_proof_photo');
            }
            
            // 9. Add enhanced status tracking
            if (!Schema::hasColumn('pickup_requests', 'dispatched_at')) {
                $table->timestamp('dispatched_at')->nullable()->after('accepted_at');
                $table->timestamp('arrived_at')->nullable()->after('en_route_at');
            }
            
            // 10. Add customer feedback fields
            if (!Schema::hasColumn('pickup_requests', 'customer_rating')) {
                $table->tinyInteger('customer_rating')->nullable()->after('cancellation_reason')->comment('Rating 1-5');
                $table->text('customer_feedback')->nullable()->after('customer_rating');
                $table->timestamp('feedback_at')->nullable()->after('customer_feedback');
            }
            
            // 11. Add staff location tracking
            if (!Schema::hasColumn('pickup_requests', 'staff_latitude')) {
                $table->decimal('staff_latitude', 10, 8)->nullable()->after('delivery_longitude');
                $table->decimal('staff_longitude', 11, 8)->nullable()->after('staff_latitude');
                $table->timestamp('location_updated_at')->nullable()->after('staff_longitude');
            }
            
            // 12. Add duration tracking
            if (!Schema::hasColumn('pickup_requests', 'estimated_duration_minutes')) {
                $table->decimal('estimated_duration_minutes', 5, 2)->nullable()->after('estimated_weight');
                $table->decimal('actual_duration_minutes', 5, 2)->nullable()->after('estimated_duration_minutes');
            }
            
            // 13. Add status history for audit trail
            if (!Schema::hasColumn('pickup_requests', 'status_history')) {
                $table->json('status_history')->nullable()->after('picked_up_at')->comment('Audit trail of status changes');
            }
            
            // 14. Add priority field
            if (!Schema::hasColumn('pickup_requests', 'priority')) {
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal')->after('service_type');
            }
            
            // 15. Add completion tracking
            if (!Schema::hasColumn('pickup_requests', 'completed_at')) {
                $table->timestamp('completed_at')->nullable()->after('picked_up_at');
            }
        });
        
        // Data migration: Copy data from old fields to new standardized fields
        $this->migrateExistingData();
        
        // Add new indexes for performance (check if they don't exist)
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Check if indexes don't exist before adding them
            $indexes = collect(DB::select('SHOW INDEX FROM pickup_requests'))->pluck('Key_name')->toArray();
            
            if (!in_array('pickup_requests_customer_address_id_index', $indexes)) {
                $table->index('customer_address_id');
            }
            if (!in_array('pickup_requests_priority_index', $indexes)) {
                $table->index('priority');
            }
            if (!in_array('pickup_requests_completed_at_index', $indexes)) {
                $table->index('completed_at');
            }
            if (!in_array('pickup_requests_status_priority_index', $indexes)) {
                $table->index(['status', 'priority']);
            }
            if (!in_array('pickup_requests_branch_id_status_priority_index', $indexes)) {
                $table->index(['branch_id', 'status', 'priority']);
            }
            if (!in_array('pickup_requests_assigned_to_status_index', $indexes)) {
                $table->index(['assigned_to', 'status']);
            }
        });
    }
    
    /**
     * Migrate existing data to new standardized structure
     */
    private function migrateExistingData(): void
    {
        // 1. Migrate contact_phone to phone_number if exists
        if (Schema::hasColumn('pickup_requests', 'contact_phone')) {
            DB::statement("
                UPDATE pickup_requests 
                SET phone_number = contact_phone 
                WHERE phone_number IS NULL AND contact_phone IS NOT NULL
            ");
        }
        
        // 2. Copy laundries_id to laundry_id
        if (Schema::hasColumn('pickup_requests', 'laundries_id')) {
            DB::statement("
                UPDATE pickup_requests 
                SET laundry_id = laundries_id 
                WHERE laundry_id IS NULL AND laundries_id IS NOT NULL
            ");
        }
        
        // 3. Copy notes to special_instructions
        if (Schema::hasColumn('pickup_requests', 'notes')) {
            DB::statement("
                UPDATE pickup_requests 
                SET special_instructions = notes 
                WHERE special_instructions IS NULL AND notes IS NOT NULL
            ");
        }
        
        // 4. Set delivery address same as pickup address for existing records
        DB::statement("
            UPDATE pickup_requests 
            SET delivery_address = pickup_address,
                delivery_latitude = latitude,
                delivery_longitude = longitude
            WHERE delivery_address IS NULL
        ");
        
        // 5. Initialize status_history for existing records
        DB::statement("
            UPDATE pickup_requests 
            SET status_history = JSON_ARRAY(
                JSON_OBJECT(
                    'status', 'pending',
                    'timestamp', created_at,
                    'changed_by', NULL,
                    'notes', 'Initial status'
                )
            )
            WHERE status_history IS NULL
        ");
        
        // 6. Set completed_at for picked_up records
        DB::statement("
            UPDATE pickup_requests 
            SET completed_at = picked_up_at 
            WHERE status = 'picked_up' AND completed_at IS NULL AND picked_up_at IS NOT NULL
        ");
    }
    
    /**
     * Clean up old inconsistent fields
     */
    private function cleanupOldFields(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Remove old inconsistent fields after data migration
            if (Schema::hasColumn('pickup_requests', 'contact_phone')) {
                $table->dropColumn('contact_phone');
            }
            
            if (Schema::hasColumn('pickup_requests', 'laundries_id')) {
                $table->dropIndex(['laundries_id']);
                $table->dropColumn('laundries_id');
            }
            
            // Keep 'notes' for backward compatibility, but 'special_instructions' is the new standard
        });
    }

    /**
     * Reverse the migrations
     */
    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn([
                'customer_address_id', 'delivery_address', 'delivery_latitude', 'delivery_longitude',
                'contact_person', 'special_instructions', 'estimated_weight', 'laundry_id',
                'pickup_proof_photo', 'proof_uploaded_at', 'dispatched_at', 'arrived_at',
                'customer_rating', 'customer_feedback', 'feedback_at', 'staff_latitude',
                'staff_longitude', 'location_updated_at', 'estimated_duration_minutes',
                'actual_duration_minutes', 'status_history', 'priority', 'completed_at'
            ]);
            
            // Restore old fields
            $table->unsignedBigInteger('laundries_id')->nullable();
            $table->index('laundries_id');
        });
    }
};