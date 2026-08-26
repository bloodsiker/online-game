<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Quest;

use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Quest\Domain\Services\QuestDefinitionsCache;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Quest\Infrastructure\Persistence\Models\Quest;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanObjective;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestClanProgress;
use App\Modules\Quest\Infrastructure\Persistence\Models\QuestObjective;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class QuestKillProgressTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();
        QuestDefinitionsCache::flush();
    }

    public function test_clan_kill_objective_increments_and_returns_message(): void
    {
        $user = User::query()->create(['name' => 'leader']);
        DB::table('monsters')->insert(['id' => 7, 'name' => 'Мышь']);
        DB::table('clans')->insert(['id' => 1, 'name' => 'Клан', 'owner_id' => $user->id]);
        DB::table('clan_members')->insert(['id' => 1, 'user_id' => $user->id, 'clan_id' => 1]);

        $quest = Quest::query()->create(['title' => 'Мышиная напасть', 'type' => 'clan']);
        $objective = QuestObjective::query()->create([
            'quest_id' => $quest->id,
            'type' => 'kill',
            'target_id' => 7,
            'required_amount' => 30,
        ]);

        $progress = QuestClanProgress::query()->create([
            'quest_id' => $quest->id,
            'clan_id' => 1,
            'user_id' => $user->id,
            'status' => 'in_progress',
        ]);
        $clanObjective = QuestClanObjective::query()->create([
            'quest_clan_progress_id' => $progress->id,
            'quest_objective_id' => $objective->id,
            'amount' => 12,
        ]);

        $spawn = new MonsterOnLocation;
        $spawn->monster_id = 7;
        $spawn->location_id = 1;
        $spawn->hp_max = 100;
        $spawn->hp_now = 100;
        $spawn->active = 1;

        $messages = app(QuestProgressService::class)
            ->progressKillAndCollect($this->playerOf($user), $spawn);

        $this->assertCount(1, $messages);
        $this->assertStringContainsString('[Клан]', $messages[0]);
        $this->assertSame(13, $clanObjective->fresh()->amount);
    }

    public function test_kill_objective_for_other_monster_is_ignored(): void
    {
        $user = User::query()->create(['name' => 'leader2']);
        $quest = Quest::query()->create(['title' => 'Крысы', 'type' => 'main']);
        $objective = QuestObjective::query()->create([
            'quest_id' => $quest->id,
            'type' => 'kill',
            'target_id' => 99,
            'required_amount' => 5,
        ]);

        DB::table('players')->insert(['id' => $user->id, 'user_id' => $user->id, 'lvl' => 1]);
        DB::table('quest_players')->insert([
            'id' => 1,
            'player_id' => $user->id,
            'quest_id' => $quest->id,
            'status' => 'in_progress',
        ]);
        $playerObjectiveId = DB::table('quest_player_objectives')->insertGetId([
            'quest_player_id' => 1,
            'quest_objective_id' => $objective->id,
            'amount' => 0,
        ]);

        // Убили не ту цель — прогресс не должен измениться и сообщение не придёт
        $spawn = new MonsterOnLocation;
        $spawn->monster_id = 7;
        $spawn->location_id = 1;
        $spawn->hp_max = 10;
        $spawn->hp_now = 10;
        $spawn->active = 1;

        $player = Player::query()->findOrFail($user->id);
        $user->setRelation('player', $player);

        $messages = app(QuestProgressService::class)
            ->progressKillAndCollect($player, $spawn);

        $this->assertSame([], $messages);
        $this->assertSame(0, (int) DB::table('quest_player_objectives')->where('id', $playerObjectiveId)->value('amount'));
    }

    private function playerOf(User $user): Player
    {
        $player = new Player;
        $player->forceFill(['id' => $user->id, 'user_id' => $user->id]);
        $player->exists = true;
        $user->setRelation('player', $player);

        return $player;
    }

    private function createTables(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('clans');
        Schema::dropIfExists('clan_memberships');
        Schema::dropIfExists('quests');
        Schema::dropIfExists('quest_objectives');
        Schema::dropIfExists('quest_players');
        Schema::dropIfExists('quest_player_objectives');
        Schema::dropIfExists('quest_clan_progress');
        Schema::dropIfExists('quest_clan_objectives');
        Schema::dropIfExists('monster_on_locations');

        Schema::create('locations', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('name')->nullable();
        });
        Schema::create('monsters', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('name')->nullable();
        });
        Schema::create('players', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('lvl')->default(1);
            $t->unsignedInteger('min_dmg')->default(1);
            $t->unsignedInteger('max_dmg')->default(2);
            $t->timestamps();
        });

        Schema::create('users', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('name');
            $t->boolean('is_admin')->default(0);
            $t->unsignedInteger('warehouse_count')->default(50);
            $t->unsignedInteger('bag_count')->default(25);
            $t->unsignedInteger('slot_count')->default(3);
            $t->unsignedInteger('location_id')->nullable();
            $t->timestamps();
        });
        Schema::create('clan_members', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->unsignedInteger('clan_id')->nullable();
        });
        Schema::create('clans', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('name');
            $t->unsignedInteger('owner_id');
        });
        Schema::create('clan_memberships', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('clan_id');
        });
        Schema::create('quests', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('title');
            $t->text('description')->nullable();
            $t->string('type');
            $t->boolean('is_active')->default(1);
            $t->boolean('is_finish')->default(0);
            $t->timestamps();
        });
        Schema::create('quest_objectives', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('quest_id');
            $t->string('type');
            $t->unsignedInteger('target_id')->nullable();
            $t->unsignedInteger('stage_id')->nullable();
            $t->unsignedInteger('required_amount');
            $t->unsignedInteger('share_item_id')->nullable();
            $t->unsignedInteger('map_id')->nullable();
            $t->unsignedTinyInteger('drop_chance')->nullable();
            $t->text('description')->nullable();
            $t->timestamps();
        });
        Schema::create('quest_players', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('player_id');
            $t->unsignedInteger('quest_id');
            $t->string('status');
            $t->unsignedInteger('current_stage_id')->nullable();
            $t->timestamps();
        });
        Schema::create('quest_player_objectives', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('quest_player_id');
            $t->unsignedInteger('quest_objective_id');
            $t->unsignedInteger('amount')->default(0);
            $t->timestamps();
        });
        Schema::create('quest_clan_progress', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('quest_id');
            $t->unsignedInteger('clan_id');
            $t->unsignedInteger('user_id');
            $t->string('status');
            $t->unsignedInteger('current_stage_id')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('reset_at')->nullable();
            $t->timestamps();
        });
        Schema::create('quest_clan_objectives', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('quest_clan_progress_id');
            $t->unsignedInteger('quest_objective_id');
            $t->unsignedInteger('amount')->default(0);
            $t->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('monster_id');
            $t->unsignedInteger('location_id');
            $t->unsignedInteger('hp_max')->default(1);
            $t->unsignedInteger('hp_now')->default(1);
            $t->boolean('active')->default(1);
            $t->timestamps();
        });
    }
}
