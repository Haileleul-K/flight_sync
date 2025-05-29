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
        Schema::create('simulators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("user_id");
            $table->integer("day")->default(0);
            $table->integer("night")->default(0);
            $table->integer("nvs")->default(0);
            $table->integer("hood")->default(0);
            $table->integer("weather")->default(0);
            $table->integer("nvg")->default(0);
            $table->date("date")->nullable();
            $table->string("image")->nullable();
            $table->foreignId('duty_position_id')->nullable()->constrained('duty_positions')->nullOnDelete();
            $table->foreignId('aircraft_models_id')->nullable()->constrained('aircraft_models')->nullOnDelete();
            $table->tinyText("tags")->nullable();
            $table->tinyText("notes")->nullable();
            $table->string("seat")->nullable();
            $table->timestamps();
            $table->foreign("user_id")->references("id")->on("users")->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('simulators');
    }
};
