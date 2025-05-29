<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pilot_semi_annual_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->date('start_date')->nullable(); // Allow null
            $table->date('end_date')->nullable();   // Allow null
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pilot_semi_annual_periods');
    }
};
