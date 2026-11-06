<?php

declare(strict_types=1);

namespace Tests\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesBattleFixture
{
    use CreatesInventoryFixture;

    private function createBattleTables(): void
    {
        $this->createInventoryTables();
        Schema::table('users', fn (Blueprint $table) => $table->unsignedBigInteger('prev_location_id')->nullable());
        Schema::table('players', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('hp_now')->default(100);
            $table->integer('victory')->default(0);
            $table->integer('exp')->default(0);
            $table->timestamps();
        });
        Schema::table('locations', function (Blueprint $table): void {
            $table->integer('count_monster')->default(1);
            $table->integer('time_not_attack')->default(1);
            $table->integer('percent_respawn_monster')->default(0);
            $table->timestamp('last_respawn_monster_at')->nullable();
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('dungeon_run_id')->nullable();
            $table->integer('rounds')->default(0);
            $table->integer('status')->default(1);
            $table->json('boss_metadata')->nullable();
            $table->timestamps();
        });
        Schema::create('battle_details', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::create('battle_rounds', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->integer('round_number');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('location_monster_id')->nullable();
            $table->text('action')->nullable();
            $table->timestamps();
            $table->unique(['battle_id', 'round_number']);
        });
        Schema::create('battle_round_hits', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_round_id');
            $table->string('participant_type');
            $table->unsignedBigInteger('participant_id');
            $table->integer('hp_after');
            $table->text('action');
            $table->timestamps();
        });
        Schema::create('battle_monster_damage', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('battle_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('location_monster_id');
            $table->integer('damage');
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('hp')->default(20);
            $table->integer('aggression')->default(100);
            $table->boolean('is_boss')->default(false);
            $table->boolean('does_not_regenerate_hp')->default(false);
            $table->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->unsignedBigInteger('clan_boss_stage_state_id')->nullable();
            $table->integer('hp_now')->default(20);
            $table->integer('hp_max')->default(20);
            $table->boolean('active')->default(true);
            $table->timestamp('last_regen_at')->nullable();
            $table->boolean('is_drop_money')->default(false);
            $table->integer('aggression')->nullable();
            $table->timestamps();
        });
        Schema::create('effects', fn (Blueprint $table) => $table->id());
        Schema::create('monster_effects', function (Blueprint $table): void {
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('effect_id');
            $table->boolean('trigger_on_hit')->default(true);
            $table->integer('chance')->default(0);
            $table->integer('duration_seconds')->default(1);
            $table->integer('power_percent')->default(0);
            $table->timestamps();
        });
        Schema::create('location_has_monsters', function (Blueprint $table): void {
            $table->unsignedBigInteger('location_id');
            $table->unsignedBigInteger('monster_id');
            $table->integer('aggression')->nullable();
        });
        Schema::create('gathering_attempts', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
        });
        Schema::create('battle_test_kills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('spawn_id');
        });
        Schema::create('dungeons', function (Blueprint $table): void {
            $table->id();
            $table->json('name');
            $table->string('type')->default('survival');
            $table->integer('first_location_id')->default(1);
            $table->integer('return_location_id')->default(2);
            $table->integer('wave_count')->default(1);
            $table->timestamps();
        });
        Schema::create('dungeon_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('leader_user_id')->default(1);
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->string('status')->default('active');
            foreach (['stage_expires_at', 'finished_at', 'expires_at'] as $field) {
                $table->timestamp($field)->nullable();
            }
            $table->string('failure_reason')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_stages', function (Blueprint $table): void {
            $table->id();
        });
        Schema::create('dungeon_sessions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('dungeon_run_id')->nullable();
            $table->unsignedBigInteger('primary_session_id')->nullable();
            $table->integer('current_wave')->default(1);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_run_participants', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_run_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('dungeon_session_id');
            $table->string('status')->default('active');
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->string('type');
            $table->integer('amount_min');
            $table->integer('amount_max');
            $table->float('drop_chance')->default(100);
            $table->timestamps();
        });
        (require database_path('migrations/2026_11_03_000002_create_battle_entry_locks_table.php'))->up();
    }

    private function seedBattleFixture(): void
    {
        $this->seedInventoryFixture();
        DB::table('clan_members')->delete();
        DB::table('players')->where('id', 1)->update(['user_id' => 1]);
        DB::table('players')->insert(['id' => 2, 'user_id' => 2]);
        DB::table('users')->where('id', 2)->update(['player_id' => 2]);
        DB::table('users')->update(['prev_location_id' => 2]);
        DB::table('monsters')->insert(['id' => 1, 'name' => 'Страж']);
        DB::table('monster_on_locations')->insert(['id' => 1, 'monster_id' => 1, 'location_id' => 1]);
    }

    private function seedDungeonReward(bool $shared = false): void
    {
        DB::table('dungeons')->insert(['id' => 1, 'name' => json_encode(['ru' => 'Испытание'])]);
        DB::table('locations')->where('id', 1)->update(['dungeon_id' => 1]);
        DB::table('monster_on_locations')->delete();
        if ($shared) {
            DB::table('dungeon_runs')->insert(['id' => 1, 'dungeon_id' => 1]);
        }
        DB::table('dungeon_sessions')->insert(['id' => 1, 'dungeon_id' => 1, 'user_id' => 1, 'dungeon_run_id' => $shared ? 1 : null]);
        if ($shared) {
            DB::table('dungeon_run_participants')->insert(['dungeon_run_id' => 1, 'user_id' => 1, 'dungeon_session_id' => 1]);
        }
        DB::table('dungeon_rewards')->insert([
            ['dungeon_id' => 1, 'type' => 'gold', 'amount_min' => 50, 'amount_max' => 50],
            ['dungeon_id' => 1, 'type' => 'diamond', 'amount_min' => 3, 'amount_max' => 3],
        ]);
    }
}
