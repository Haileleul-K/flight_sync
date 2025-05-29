<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilot_apache_seat_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aircraft_id')->constrained('aircraft_models')->onDelete('cascade');
            $table->foreignId('fac_level_id')->constrained('fac_levels')->onDelete('cascade');
            $table->decimal('aircraft_back_seat_hours', 5, 1)->default(0.0); // Back seat flight hours
            $table->decimal('aircraft_front_seat_hours', 5, 1)->default(0.0); // Front seat flight hours
            $table->decimal('simulator_back_seat_hours', 5, 1)->default(0.0); // Back seat simulator hours
            $table->decimal('simulator_front_seat_hours', 5, 1)->default(0.0); // Front seat simulator hours
            $table->decimal('nvs_hours', 5, 1)->default(0.0); // Night Vision System hours (AH-64 specific)
            $table->decimal('nvd_hours', 5, 1)->default(0.0); // Night Vision Device hours
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilot_apache_seat_hours');
    }
};
