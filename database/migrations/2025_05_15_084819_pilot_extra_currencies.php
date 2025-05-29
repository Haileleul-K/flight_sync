<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilot_extra_currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('cbrn_hours', 5, 1)->nullable(); // CBRN training hours
            $table->boolean('hoist')->nullable(); // Hoist qualification
            $table->boolean('extended_fuel_system')->nullable(); // Extended fuel system qualification
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilot_extra_currencies');
    }
};
