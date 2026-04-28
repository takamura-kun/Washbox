// database/migrations/2026_01_02_000011_create_laundry_service_addon_table.php
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('laundry_service_addon', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laundries_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->decimal('price_at_purchase', 10, 2);
            $table->integer('quantity')->default(1);
            $table->timestamps();

            $table->unique(['laundries_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('laundry_service_addon');
    }
};
