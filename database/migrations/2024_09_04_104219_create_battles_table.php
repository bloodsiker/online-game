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
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('locations')->cascadeOnDelete();
            $table->integer('rounds')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::create('battle_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('battle_id')->references('id')->on('battles')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('location_monster_id')->references('id')->on('monster_on_locations')->nullOnDelete();
        });

        Schema::create('battle_rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->integer('round_number')->default(1);
            $table->text('action');
            $table->timestamps();

            $table->foreign('battle_id')->references('id')->on('battles')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('location_monster_id')->references('id')->on('monster_on_locations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_rounds');
        Schema::dropIfExists('battle_details');
        Schema::dropIfExists('battles');
    }
};
