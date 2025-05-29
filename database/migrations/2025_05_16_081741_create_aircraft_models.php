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
        Schema::create('aircraft_models', function (Blueprint $table) {
            $table->id();
            $table->string('model', 50)->unique(); // e.g., AH-64D/E, UH-60M/V
            $table->json('seats')->nullable(); // Add this line for seat names
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aircraft_models');
    }
};
