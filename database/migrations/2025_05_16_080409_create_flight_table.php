<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        
        // Schema::create('duty_positions', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code')->unique(); // e.g., 'pi', 'pc', 'mp'
        //     $table->string('label');
        //     $table->timestamps();
        // });
    
    
        // Schema::create('missions', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('code')->unique(); 
        //     $table->string('label');
        //     $table->timestamps();
        // });

        // Flights Table

        Schema::create('flights', function (Blueprint $table) {
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

            // Lookup foreign keys
            $table->foreignId('duty_position_id')->nullable()->constrained('duty_positions')->nullOnDelete();
            $table->foreignId('mission_id')->nullable()->constrained('missions')->nullOnDelete();
            $table->foreignId('aircraft_models_id')->nullable()->constrained('aircraft_models')->nullOnDelete();
            $table->string('seat')->nullable();
            $table->string('tail_number')->nullable();
            $table->string('departure_airport')->nullable();
            $table->string('arrival_airport')->nullable();

            $table->tinyText("tags")->nullable();
            $table->tinyText("notes")->nullable();
            $table->timestamps();

            // Foreign Key to users
            $table->foreign("user_id")->references("id")->on("users")->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('flights');
        // Schema::dropIfExists('missions');
        // Schema::dropIfExists('duty_positions');
    }
};
