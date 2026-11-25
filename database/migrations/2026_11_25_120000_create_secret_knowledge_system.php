<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('secret_knowledge_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('npc_id')->nullable()->constrained('npcs')->nullOnDelete();
            $table->foreignId('bundle_share_item_id')->nullable()->constrained('share_items')->nullOnDelete();
            $table->unsignedTinyInteger('analysis_success_chance')->default(25);
            $table->unsignedInteger('knowledge_gain_min')->default(1);
            $table->unsignedInteger('knowledge_gain_max')->default(1);
            $table->boolean('is_active')->default(false);
            $table->timestamps();
        });

        DB::table('secret_knowledge_settings')->insert([
            'analysis_success_chance' => 25,
            'knowledge_gain_min' => 1,
            'knowledge_gain_max' => 1,
            'is_active' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('player_secret_knowledge', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->unique()->constrained('players')->cascadeOnDelete();
            $table->unsignedInteger('points')->default(0);
            $table->timestamps();
        });

        Schema::create('boss_knowledge_manuals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('manual_share_item_id')->unique()->constrained('share_items')->cascadeOnDelete();
            $table->foreignId('monster_id')->unique()->constrained('monsters')->cascadeOnDelete();
            $table->unsignedInteger('required_knowledge')->default(0);
            $table->foreignId('boss_part_share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('player_studied_boss_manuals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('player_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('boss_knowledge_manual_id')->constrained('boss_knowledge_manuals')->cascadeOnDelete();
            $table->timestamp('studied_at')->useCurrent();
            $table->unique(['player_id', 'boss_knowledge_manual_id'], 'player_boss_manual_unique');
        });

        Schema::create('boss_knowledge_reward_claims', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('battle_id')->constrained('battles')->cascadeOnDelete();
            $table->foreignId('monster_on_location_id')->constrained('monster_on_locations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('share_item_id')->constrained('share_items')->cascadeOnDelete();
            $table->timestamp('created_at')->useCurrent();
            $table->unique(['battle_id', 'monster_on_location_id', 'user_id'], 'boss_knowledge_reward_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boss_knowledge_reward_claims');
        Schema::dropIfExists('player_studied_boss_manuals');
        Schema::dropIfExists('boss_knowledge_manuals');
        Schema::dropIfExists('player_secret_knowledge');
        Schema::dropIfExists('secret_knowledge_settings');
    }
};
