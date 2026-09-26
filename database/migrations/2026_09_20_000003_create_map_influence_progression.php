<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_gates', function (Blueprint $table): void {
            $table->unsignedBigInteger('share_item_id')->nullable()->change();
        });

        Schema::create('influence_medals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedInteger('rating_points')->default(0);
            $table->timestamps();
        });

        Schema::create('map_influence_levels', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->unsignedSmallInteger('level');
            $table->string('name');
            $table->unsignedBigInteger('required_influence');
            $table->foreignId('influence_medal_id')->nullable()->constrained('influence_medals')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['map_id', 'level']);
            $table->unique(['map_id', 'required_influence']);
        });

        Schema::create('map_influence_level_bonuses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_influence_level_id')->constrained()->cascadeOnDelete();
            $table->string('bonus_type', 64);
            $table->decimal('value', 8, 3)->default(0);
            $table->timestamps();

            $table->unique(['map_influence_level_id', 'bonus_type'], 'influence_level_bonus_unique');
        });

        Schema::create('influence_medal_stats', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('influence_medal_id')->constrained('influence_medals')->cascadeOnDelete();
            $table->string('stat_type', 64);
            $table->decimal('value', 10, 3);
            $table->boolean('is_percent')->default(false);
            $table->timestamps();

            $table->unique(['influence_medal_id', 'stat_type', 'is_percent'], 'influence_medal_stat_unique');
        });

        Schema::create('map_influence_level_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('map_influence_level_id')->constrained()->cascadeOnDelete();
            $table->string('reward_type', 32);
            $table->foreignId('share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->unsignedBigInteger('amount')->default(1);
            $table->json('config')->nullable();
            $table->timestamps();
        });

        Schema::create('player_influence_medals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('influence_medal_id')->constrained('influence_medals')->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'influence_medal_id']);
        });

        Schema::create('player_map_influence_level_rewards', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedBigInteger('map_influence_level_reward_id');
            $table->foreign('map_influence_level_reward_id', 'player_influence_reward_reward_fk')
                ->references('id')->on('map_influence_level_rewards')->cascadeOnDelete();
            $table->timestamp('granted_at');
            $table->timestamps();

            $table->unique(['user_id', 'map_influence_level_reward_id'], 'player_influence_reward_unique');
        });

        Schema::create('map_influence_transactions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->bigInteger('amount');
            $table->string('source_type', 64);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'map_id', 'created_at'], 'influence_transactions_player_map_idx');
        });

        Schema::create('npc_influence_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('npc_id')->constrained('npcs')->cascadeOnDelete();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->unsignedBigInteger('required_influence');
            $table->timestamps();
            $table->unique(['npc_id', 'map_id']);
        });

        Schema::create('quest_influence_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('quest_id')->constrained('quests')->cascadeOnDelete();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->unsignedBigInteger('required_influence');
            $table->timestamps();
            $table->unique(['quest_id', 'map_id']);
        });

        Schema::create('location_gate_influence_requirements', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('location_gate_id')->constrained('location_gates')->cascadeOnDelete();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->unsignedBigInteger('required_influence');
            $table->timestamps();
            $table->unique(['location_gate_id', 'map_id'], 'location_gate_influence_unique');
        });

        Schema::create('influence_shop_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('structure_id')->constrained('structures')->cascadeOnDelete();
            $table->foreignId('map_id')->constrained('maps')->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->nullable();
            $table->unsignedBigInteger('required_influence')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['structure_id', 'map_id']);
        });

        Schema::create('influence_shop_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('influence_shop_section_id')->constrained()->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->unsignedBigInteger('required_influence')->default(0);
            $table->unsignedBigInteger('price')->default(0);
            $table->unsignedBigInteger('diamond')->default(0);
            $table->unsignedInteger('purchase_limit')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('influence_shop_purchases', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('influence_shop_item_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->timestamps();
            $table->unique(['user_id', 'influence_shop_item_id'], 'influence_shop_purchase_unique');
        });

        Schema::table('map_influences', function (Blueprint $table): void {
            $table->index(['map_id', 'influence', 'user_id'], 'map_influence_rating_idx');
            $table->index(['user_id', 'influence'], 'player_influence_idx');
        });
    }

    public function down(): void
    {
        Schema::table('map_influences', function (Blueprint $table): void {
            $table->dropIndex('map_influence_rating_idx');
            $table->dropIndex('player_influence_idx');
        });

        Schema::dropIfExists('influence_shop_purchases');
        Schema::dropIfExists('influence_shop_items');
        Schema::dropIfExists('influence_shop_sections');
        Schema::dropIfExists('location_gate_influence_requirements');
        Schema::dropIfExists('quest_influence_requirements');
        Schema::dropIfExists('npc_influence_requirements');
        Schema::dropIfExists('map_influence_transactions');
        Schema::dropIfExists('player_map_influence_level_rewards');
        Schema::dropIfExists('player_influence_medals');
        Schema::dropIfExists('map_influence_level_rewards');
        Schema::dropIfExists('influence_medal_stats');
        Schema::dropIfExists('map_influence_level_bonuses');
        Schema::dropIfExists('map_influence_levels');
        Schema::dropIfExists('influence_medals');

        Schema::table('location_gates', function (Blueprint $table): void {
            $table->unsignedBigInteger('share_item_id')->nullable(false)->change();
        });
    }
};
