<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('share_items', function (Blueprint $table): void {
            $table->string('effect_group', 32)->nullable();
        });

        Schema::create('player_item_group_cooldowns', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->string('effect_group', 32);
            $table->timestamp('available_at');
            $table->timestamps();

            $table->unique(['player_id', 'effect_group'], 'player_item_group_cooldown_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('player_item_group_cooldowns');

        Schema::table('share_items', function (Blueprint $table): void {
            $table->dropColumn('effect_group');
        });
    }
};
