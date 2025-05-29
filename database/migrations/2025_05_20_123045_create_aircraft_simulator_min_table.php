<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aircraft_simulator_min', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aircraft_id')->constrained('aircraft_models')->onDelete('cascade');
            $table->foreignId('fac_level_id')->constrained('fac_levels')->onDelete('cascade');

            // Aircraft hours
            $table->double('aircraft_total_hours', 8, 1)->default(0.0);

            // Weather conditions
            $table->integer('hood_weather')->default(0); // Instrument/simulated weather conditions
            $table->integer('night')->default(0);        // Night hours
            $table->integer('nvg')->default(0);          // Night Vision Goggle hours

            // Simulator hours
            $table->double('simulator_total_hours', 8, 1)->default(0.0);

            $table->timestamps();

            // Optional: Add index for frequently queried columns
            $table->index(['user_id', 'aircraft_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft_simulator_min');
    }
};
