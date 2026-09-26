<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reputations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('npc_id')->nullable()->constrained('npcs')->nullOnDelete();
            $table->string('icon')->nullable();
            $table->timestamps();
        });

        Schema::create('reputation_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reputation_id')->constrained('reputations')->cascadeOnDelete();
            $table->unsignedInteger('min_points')->default(0);
            $table->unsignedInteger('max_points')->nullable();
            $table->string('medal_name')->nullable();
            $table->string('medal_icon')->nullable();
            $table->timestamps();

            $table->unique(['reputation_id', 'min_points']);
        });

        Schema::create('reputation_tier_quests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tier_id')->constrained('reputation_tiers')->cascadeOnDelete();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tier_id', 'quest_id']);
        });

        Schema::create('player_reputations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('reputation_id')->constrained('reputations')->cascadeOnDelete();
            $table->unsignedInteger('points')->default(0);
            $table->timestamp('last_completed_at')->nullable();
            $table->timestamps();

            $table->unique(['player_id', 'reputation_id']);
        });

        Schema::create('reputation_shop_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reputation_id')->constrained('reputations')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->integer('price')->default(0);
            $table->integer('diamond')->default(0);
            $table->unsignedInteger('min_points')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('reputation_shop_item_requirements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reputation_shop_item_id');
            $table->foreign('reputation_shop_item_id', 'rep_shop_item_req_item_fk')
                ->references('id')->on('reputation_shop_items')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::table('quest_rewards', function (Blueprint $table) {
            $table->foreign('reputation_id')->references('id')->on('reputations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('quest_rewards', function (Blueprint $table) {
            $table->dropForeign(['reputation_id']);
        });

        Schema::dropIfExists('reputation_shop_item_requirements');
        Schema::dropIfExists('reputation_shop_items');
        Schema::dropIfExists('player_reputations');
        Schema::dropIfExists('reputation_tier_quests');
        Schema::dropIfExists('reputation_tiers');
        Schema::dropIfExists('reputations');
    }
};