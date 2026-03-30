<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('player_item_buffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->onDelete('cascade');
            $table->enum('effect_type', ['buff_attack', 'buff_armor']);
            $table->integer('value');
            $table->enum('value_type', ['flat', 'percent']);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->index(['player_id', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_item_buffs');
    }
};
