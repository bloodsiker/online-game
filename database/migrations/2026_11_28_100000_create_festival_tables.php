<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('festival_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('npc_id')->nullable()->constrained('npcs')->nullOnDelete();
            $table->foreignId('coin_share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            // Бартерный магазин победителей (обычная структура типа barter_shop).
            $table->foreignId('shop_structure_id')->nullable()->constrained('structures')->nullOnDelete();
            $table->unsignedSmallInteger('collection_days')->default(7);
            $table->unsignedSmallInteger('rewards_days')->default(7);
            $table->boolean('is_active')->default(true);
            // Отложенный старт: пока время не наступило, планировщик не создаёт фестиваль.
            $table->timestamp('launch_at')->nullable();
            $table->timestamps();
        });

        // Камни печатей: сколько очков даёт камень и сколько монет за него на обмене победителей.
        Schema::create('festival_stone_rates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedInteger('points');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique('share_item_id');
        });

        Schema::create('festivals', function (Blueprint $table): void {
            $table->id();
            $table->string('status', 16)->index();
            $table->timestamp('starts_at');
            $table->timestamp('collection_ends_at');
            $table->timestamp('ends_at');
            $table->string('winner_side', 8)->nullable();
            $table->string('outcome', 8)->nullable();
            $table->unsignedBigInteger('light_points')->nullable();
            $table->unsignedBigInteger('dark_points')->nullable();
            $table->timestamp('collection_announced_at')->nullable();
            $table->timestamp('rewards_announced_at')->nullable();
            $table->timestamp('finished_announced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('festival_participants', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('festival_id')->constrained('festivals')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('side', 8);
            $table->unsignedBigInteger('points')->default(0);
            $table->timestamp('reward_claimed_at')->nullable();
            $table->timestamps();
            $table->unique(['festival_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('festival_participants');
        Schema::dropIfExists('festivals');
        Schema::dropIfExists('festival_stone_rates');
        Schema::dropIfExists('festival_settings');
    }
};
