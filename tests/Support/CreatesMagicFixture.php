<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesMagicFixture
{
    use CreatesBattleFixture;

    private function createMagicTables(): void
    {
        $this->createBattleTables();
        Schema::table('users', fn (Blueprint $table) => $table->timestamp('last_online_at')->nullable());
        Schema::table('players', function (Blueprint $table): void {
            foreach (['hp_max' => 100, 'mp_now' => 100, 'mp_max' => 100, 'lvl' => 1, 'endurance' => 1, 'reputation_rating' => 0] as $field => $default) {
                $table->integer($field)->default($default);
            }
            $table->timestamp('last_regen_at')->nullable();
            $table->integer('regen_hp_start')->nullable();
            $table->integer('regen_mp_start')->nullable();
            $table->float('experience_multiplier')->default(1);
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type')->default('heal');
            $table->string('target_type')->default('ally');
            $table->integer('level')->default(1);
            $table->integer('mana_cost')->default(10);
            $table->integer('cooldown')->default(30);
            $table->integer('base_healing')->default(20);
            $table->float('power_coefficient')->default(0);
            $table->boolean('is_passive')->default(false);
            $table->timestamps();
        });
        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->boolean('is_equipped')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamp('cooldown_end_at')->nullable();
            $table->timestamps();
            $table->unique(['player_id', 'magic_skill_id']);
        });
        Schema::create('skills', fn (Blueprint $table) => $table->id());
        Schema::create('player_skills', function (Blueprint $table): void {
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            $table->integer('lvl')->default(0);
        });
        Schema::table('effects', function (Blueprint $table): void {
            $table->json('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('type')->default('buff');
            $table->string('active_type')->nullable();
            $table->string('damage_scaling_type')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->integer('tick_interval')->default(10);
            $table->integer('value_per_tick')->default(5);
            $table->timestamps();
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance')->default(100);
            $table->integer('duration_seconds')->default(60);
        });
        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            foreach (['applied_at', 'last_tick_at', 'next_tick_at', 'expires_at'] as $column) {
                $table->timestamp($column)->nullable();
            }
            $table->integer('stacks')->default(0);
            $table->float('current_value')->default(0);
            $table->float('tick_remainder')->default(0);
            $table->timestamps();
        });
    }

    private function seedMagicFixture(): void
    {
        $this->seedBattleFixture();
        DB::table('users')->whereIn('id', [1, 2])->update(['last_online_at' => now()]);
        DB::table('players')->update(['hp_now' => 50, 'hp_max' => 100, 'mp_now' => 100, 'mp_max' => 100,
            'last_regen_at' => now(), 'regen_hp_start' => 50, 'regen_mp_start' => 100]);
        DB::table('magic_skills')->insert([
            ['id' => 1, 'name' => json_encode(['ru' => 'Лечение']), 'base_healing' => 20],
            ['id' => 2, 'name' => json_encode(['ru' => 'Благословение']), 'base_healing' => 0],
        ]);
        foreach ([1, 2] as $playerId) {
            foreach ([1, 2] as $skillId) {
                DB::table('player_magic_skills')->insert(['player_id' => $playerId, 'magic_skill_id' => $skillId]);
            }
        }
        DB::table('effects')->insert(['id' => 1, 'name' => json_encode(['ru' => 'Благословение']), 'slug' => 'blessing']);
        DB::table('magic_skill_effects')->insert(['magic_skill_id' => 2, 'effect_id' => 1]);
    }
}
