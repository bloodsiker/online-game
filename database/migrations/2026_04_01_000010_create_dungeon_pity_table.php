<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeon_pity', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('fail_count')->default(0);
            $table->timestamp('last_fail_at')->nullable();
            $table->timestamp('last_pity_at')->nullable();
            $table->timestamps();

            $table->unique(['dungeon_id', 'user_id']);

            $table->foreign('dungeon_id')->references('id')->on('dungeons')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dungeon_pity');
    }
};