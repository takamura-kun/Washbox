<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('location_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pickup_request_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('user_type', ['staff', 'customer']);
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable();
            $table->decimal('speed', 8, 2)->nullable();
            $table->decimal('heading', 8, 2)->nullable();
            $table->timestamp('timestamp');
            $table->timestamps();

            // Indexes for performance
            $table->index(['pickup_request_id', 'user_type']);
            $table->index(['pickup_request_id', 'timestamp']);
            $table->index('timestamp');
        });
    }

    public function down()
    {
        Schema::dropIfExists('location_updates');
    }
};