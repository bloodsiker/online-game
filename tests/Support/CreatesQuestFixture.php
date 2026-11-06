<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait CreatesQuestFixture
{
    use CreatesInventoryFixture;

    private function createQuestTables(): void
    {
        $this->createInventoryTables();
        Schema::table('players', function (Blueprint $table): void {
            $table->unsignedBigInteger('user_id')->nullable();
            $table->integer('exp')->default(0);
            $table->timestamps();
        });
        Schema::create('quests', function (Blueprint $table): void {
            $table->id();
            $table->json('title');
            $table->text('description')->nullable();
            $table->string('type')->default('one_time');
            foreach (['start_npc_id', 'complete_npc_id', 'after_quest_id', 'parent_quest_id', 'reset_period'] as $field) {
                $table->integer($field)->nullable();
            }
            $table->boolean('is_active')->default(true);
            $table->boolean('is_finish')->default(false);
            $table->timestamps();
        });
        Schema::create('quest_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->integer('order')->default(1);
            $table->integer('complete_npc_id')->nullable();
            $table->string('stage_type')->default('objectives');
            $table->integer('wait_duration_seconds')->default(0);
            foreach (['title', 'description', 'waiting_text', 'ready_text'] as $field) {
                $table->text($field)->nullable();
            }
            $table->unsignedBigInteger('grant_share_item_id')->nullable();
            $table->integer('grant_amount')->default(1);
            $table->timestamps();
        });
        Schema::create('quest_objectives', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->unsignedBigInteger('stage_id')->nullable();
            $table->string('type')->default('deliver');
            $table->string('target_type')->default('item');
            $table->unsignedBigInteger('target_id')->default(1);
            $table->unsignedBigInteger('share_item_id')->nullable();
            $table->integer('required_amount')->default(1);
            $table->timestamps();
        });
        foreach (['quest_players' => 'player_id', 'quest_clan_progress' => 'clan_id'] as $name => $owner) {
            Schema::create($name, function (Blueprint $table) use ($owner): void {
                $table->id();
                $table->unsignedBigInteger($owner);
                $table->unsignedBigInteger('quest_id');
                if ($owner === 'clan_id') {
                    $table->unsignedBigInteger('user_id');
                }
                $table->string('status')->default('in_progress');
                $table->unsignedBigInteger('current_stage_id')->nullable();
                foreach (['current_stage_started_at', 'current_stage_ready_at', 'completed_at', 'reset_at'] as $field) {
                    $table->timestamp($field)->nullable();
                }
                $table->timestamps();
            });
        }
        foreach (['quest_player_objectives' => 'quest_player_id', 'quest_clan_objectives' => 'quest_clan_progress_id'] as $name => $parent) {
            Schema::create($name, function (Blueprint $table) use ($parent): void {
                $table->id();
                $table->unsignedBigInteger($parent);
                $table->unsignedBigInteger('quest_objective_id');
                $table->integer('amount')->default(0);
                $table->timestamps();
            });
        }
        Schema::create('quest_rewards', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
            $table->string('type');
            $table->integer('amount');
            foreach (['share_item_id', 'location_id', 'reputation_id'] as $field) {
                $table->unsignedBigInteger($field)->nullable();
            }
            $table->timestamps();
        });
        Schema::create('quest_influence_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('quest_id');
        });
        Schema::create('reputation_tiers', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('feat_quest_id')->nullable();
        });
        Schema::create('quest_test_notifications', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
        });
        (require database_path('migrations/2026_11_03_000001_add_revision_to_quest_progress.php'))->up();
    }

    private function seedQuestFixture(bool $clan = false): Quest
    {
        $this->seedInventoryFixture();
        DB::table('players')->where('id', 1)->update(['user_id' => 1]);
        DB::table('players')->insert(['id' => 2, 'user_id' => 2]);
        DB::table('users')->where('id', 2)->update(['player_id' => 2]);
        DB::table('quests')->insert(['id' => 1, 'title' => json_encode(['ru' => 'Проверка']), 'type' => $clan ? 'clan' : 'one_time', 'reset_period' => 60]);
        DB::table('quest_objectives')->insert(['id' => 1, 'quest_id' => 1, 'type' => 'deliver', 'target_id' => 1, 'required_amount' => 4]);
        DB::table('quest_rewards')->insert([
            ['quest_id' => 1, 'type' => 'money', 'amount' => 50, 'share_item_id' => null],
            ['quest_id' => 1, 'type' => 'item', 'amount' => 2, 'share_item_id' => 1],
            ['quest_id' => 1, 'type' => 'exp', 'amount' => 30, 'share_item_id' => null],
        ]);
        DB::table($clan ? 'quest_clan_progress' : 'quest_players')->insert(
            ['id' => 1, 'quest_id' => 1] + ($clan ? ['clan_id' => 1, 'user_id' => 1] : ['player_id' => 1]),
        );
        DB::table($clan ? 'quest_clan_objectives' : 'quest_player_objectives')->insert([
            $clan ? 'quest_clan_progress_id' : 'quest_player_id' => 1, 'quest_objective_id' => 1,
        ]);

        return Quest::findOrFail(1);
    }
}
