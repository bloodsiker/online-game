<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeon_gates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('from_location_id');
            $table->unsignedBigInteger('to_location_id');
            $table->enum('unlock_type', ['area_cleared', 'boss_item'])->default('area_cleared');
            $table->unsignedBigInteger('boss_share_item_id')->nullable();
            $table->timestamps();

            $table->foreign('dungeon_id')->references('id')->on('dungeons')->onDelete('cascade');
            $table->foreign('from_location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('to_location_id')->references('id')->on('locations')->onDelete('cascade');
            $table->foreign('boss_share_item_id')->references('id')->on('share_items')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dungeon_gates');
    }
};