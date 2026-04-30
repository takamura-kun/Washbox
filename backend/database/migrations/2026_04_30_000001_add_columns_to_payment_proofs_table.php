<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            // Add customer tracking
            if (!Schema::hasColumn('payment_proofs', 'customer_id')) {
                $table->foreignId('customer_id')->nullable()->after('laundry_id')->constrained()->onDelete('cascade');
            }

            // Add transaction tracking
            if (!Schema::hasColumn('payment_proofs', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_method');
            }

            // Add screenshot path (new field name)
            if (!Schema::hasColumn('payment_proofs', 'screenshot_path')) {
                $table->text('screenshot_path')->nullable()->after('proof_image');
            }

            // Add submission notes
            if (!Schema::hasColumn('payment_proofs', 'notes')) {
                $table->text('notes')->nullable()->after('screenshot_path');
            }

            // Add submission timestamp
            if (!Schema::hasColumn('payment_proofs', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('status');
            }

            // Add approval timestamp
            if (!Schema::hasColumn('payment_proofs', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }

            // Add rejection timestamp
            if (!Schema::hasColumn('payment_proofs', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }

            // Add indexes for performance
            if (!Schema::hasIndex('payment_proofs', 'payment_proofs_customer_id_index')) {
                $table->index('customer_id');
            }
            if (!Schema::hasIndex('payment_proofs', 'payment_proofs_status_index')) {
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_proofs', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Customer::class);
            $table->dropColumn([
                'customer_id',
                'transaction_id',
                'screenshot_path',
                'notes',
                'submitted_at',
                'approved_at',
                'rejected_at',
            ]);
        });
    }
};
