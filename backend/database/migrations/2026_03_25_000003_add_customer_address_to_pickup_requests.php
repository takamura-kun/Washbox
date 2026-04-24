<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            // Add foreign key to track which saved address was used
            $table->foreignId('customer_address_id')
                  ->nullable()
                  ->after('customer_id')
                  ->constrained('customer_addresses')
                  ->nullOnDelete()
                  ->comment('Links to saved address if one was used');
            
            // Add phone number field if not exists
            if (!Schema::hasColumn('pickup_requests', 'phone_number')) {
                $table->string('phone_number')->after('preferred_time');
            }
            
            // Add delivery address fields for future use
            $table->text('delivery_address')->nullable()->after('pickup_address');
            $table->decimal('delivery_latitude', 10, 8)->nullable()->after('longitude');
            $table->decimal('delivery_longitude', 11, 8)->nullable()->after('delivery_latitude');
            
            // Add index for performance
            $table->index('customer_address_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_requests', function (Blueprint $table) {
            $table->dropForeign(['customer_address_id']);
            $table->dropColumn([
                'customer_address_id',
                'delivery_address',
                'delivery_latitude', 
                'delivery_longitude'
            ]);
        });
    }
};