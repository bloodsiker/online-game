<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dungeon_rewards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->enum('type', ['gold', 'diamond', 'item', 'experience']);
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->unsignedInteger('amount_min')->default(1);
            $table->unsignedInteger('amount_max')->default(1);
            $table->decimal('drop_chance', 6, 3)->default(100.000); // 0.001 - 100.000
            $table->timestamps();

            $table->foreign('dungeon_id')->references('id')->on('dungeons')->onDelete('cascade');
            $table->foreign('share_item_id')->references('id')->on('share_items')->onDelete('set null');
            $table->index('dungeon_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dungeon_rewards');
    }
};
