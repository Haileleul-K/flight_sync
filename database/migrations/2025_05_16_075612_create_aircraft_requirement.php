
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
        Schema::create('aircraft_requirements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aircraft_id')->constrained('aircraft_models')->onDelete('cascade');
            $table->foreignId('fac_level_id')->constrained('fac_levels')->onDelete('cascade');
            $table->unique(['aircraft_id', 'fac_level_id']); // Ensure unique aircraft, FAC level
            $table->decimal('aircraft_front_seat_hours', 5, 1)->default(0.0);
            $table->decimal('aircraft_back_seat_hours', 5, 1)->default(0.0);
            $table->decimal('simulator_front_seat_hours', 5, 1)->default(0.0);
            $table->decimal('simulator_back_seat_hours', 5, 1)->default(0.0);
            $table->enum('role', [
                'pilot',
                'instructor',
                'nrcm',
                'ncm',
                'test_pilot',
                'flight_surgeon',
                'physician_assistant',
                'da_civilian'
            ])->nullable();
            $table->enum('category', ['primary', 'alternate'])->nullable();
            $table->decimal('total_hours', 5, 1);
            $table->decimal('nvd_hours', 5, 1)->default(0.0);
            $table->decimal('nvs_hours', 5, 1)->default(0.0);
            $table->decimal('nvg_hours', 5, 1)->default(0.0);
            $table->decimal('night_hours', 5, 1)->default(0.0);
            $table->enum('type', ['flight', 'simulator'])->nullable(false);
            $table->enum('period', ['semi_annual', 'annual'])->nullable(false)->default('semi_annual');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aircraft_requirements');
    }
};