<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_relationships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('target_id');
            $table->enum('type', ['friend', 'enemy', 'ignore']);
            $table->enum('status', ['pending', 'accepted'])->nullable();
            $table->timestamps();

            $table->foreign('player_id')->references('id')->on('players')->onDelete('cascade');
            $table->foreign('target_id')->references('id')->on('players')->onDelete('cascade');
            $table->unique(['player_id', 'target_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_relationships');
    }
};