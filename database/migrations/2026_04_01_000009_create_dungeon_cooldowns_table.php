<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeon_cooldowns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            // user_id = 0 — sentinel для global cooldown (MySQL 5.7 не поддерживает partial unique index)
            $table->unsignedBigInteger('user_id')->default(0);
            $table->timestamp('available_at');
            $table->timestamps();

            $table->unique(['dungeon_id', 'user_id']);

            $table->foreign('dungeon_id')->references('id')->on('dungeons')->onDelete('cascade');
            $table->index(['dungeon_id', 'available_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dungeon_cooldowns');
    }
};