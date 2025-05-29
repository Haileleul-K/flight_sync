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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 100);
            $table->string('email', 100)->unique('user_email_unique');
            $table->string('password', 255);
            $table->date('birth_month')->nullable();
            // Foreign keys (required, non-nullable)
            $table->foreignId('rank_id')->nullable()->constrained('ranks')->nullOnDelete();
            $table->foreignId('fac_level_id')->nullable()->constrained('fac_levels')->nullOnDelete();
            $table->foreignId('rl_level_id')->nullable()->constrained('rl_levels')->nullOnDelete();
            $table->foreignId('aircraft_model_id')->nullable()->constrained('aircraft_models')->nullOnDelete();
            $table->string('token', 100)->nullable()->unique('user_token_unique');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};