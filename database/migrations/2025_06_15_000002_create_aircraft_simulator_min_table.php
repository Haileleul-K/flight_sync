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
        Schema::create('aircraft_simulator_min', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('aircraft_id')->constrained('aircraft_models')->onDelete('cascade');
            $table->foreignId('fac_level_id')->constrained('fac_levels')->onDelete('cascade');
            $table->decimal('aircraft_total_hours', 5, 1)->default(0.0);
            $table->integer('hood_weather')->default(0);
            $table->integer('night')->default(0);
            $table->integer('nvg')->default(0);
            $table->decimal('simulator_total_hours', 5, 1)->default(0.0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aircraft_simulator_min');
    }
}; 