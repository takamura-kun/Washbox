<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extra_service_settings', function (Blueprint $table) {
            $table->id();
            $table->string('service_key')->unique();
            $table->string('service_name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('icon')->nullable();
            $table->string('color')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Insert default extra services
        DB::table('extra_service_settings')->insert([
            [
                'service_key' => 'extra_wash',
                'service_name' => 'Extra Wash',
                'description' => 'Additional wash cycle for heavy items',
                'price' => 100.00,
                'icon' => 'bi-droplet-fill',
                'color' => 'primary',
                'is_active' => true,
                'display_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_key' => 'extra_dry',
                'service_name' => 'Extra Dry',
                'description' => 'Extended drying for thick fabrics',
                'price' => 80.00,
                'icon' => 'bi-sun-fill',
                'color' => 'warning',
                'is_active' => true,
                'display_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_key' => 'extra_rinse',
                'service_name' => 'Extra Rinse',
                'description' => 'Additional rinse cycle',
                'price' => 50.00,
                'icon' => 'bi-water',
                'color' => 'info',
                'is_active' => true,
                'display_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'service_key' => 'extra_spin',
                'service_name' => 'Extra Spin',
                'description' => 'Extra spin cycle to remove water',
                'price' => 60.00,
                'icon' => 'bi-arrow-repeat',
                'color' => 'success',
                'is_active' => true,
                'display_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('extra_service_settings');
    }
};
