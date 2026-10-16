<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Clan\Application\UseCases\GetClanBossPage;
use App\Modules\Clan\Application\UseCases\ResolveClanContext;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanMember;
use App\Modules\Clan\Domain\Models\ClanRole;
use App\Modules\Clan\Domain\Services\ClanExperienceService;
use App\Modules\Clan\Domain\Services\ClanLevelService;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Dungeon\Application\Services\ClanBossRewardService;
use App\Modules\Dungeon\Application\Services\ClanBossService;
use App\Modules\Dungeon\Domain\Enums\DungeonType;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossContribution;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossPlayerAttackState;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRun;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\ClanBossRunStage;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonStage;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Modules\User\Presentation\Http\Middleware\ApplyGameLocale;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class ClanBossPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createSchema();
    }

    public function test_clan_boss_instance_is_visible_only_to_members_of_its_active_clan_run(): void
    {
        $leader = User::query()->create(['name' => 'Лидер']);
        $member = User::query()->create(['name' => 'Участник']);
        $outsider = User::query()->create(['name' => 'Посторонний']);
        $clan = Clan::query()->create(['name' => 'Клан', 'owner_id' => $leader->id]);
        $role = ClanRole::query()->create(['clan_id' => $clan->id, 'name' => 'Участник']);
        ClanMember::query()->forceCreate(['clan_id' => $clan->id, 'user_id' => $member->id, 'role_id' => $role->id]);

        $dungeon = Dungeon::query()->create([
            'name' => 'Клановый данж', 'type' => DungeonType::TOWER,
            'is_active' => true, 'is_clan_boss' => true,
        ]);
        $stage = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id, 'number' => 1, 'name' => 'Этап',
            'completion_type' => 'kill_all', 'spawn_type' => 'fixed', 'total_monsters' => 1,
        ]);
        $monster = Monster::query()->create(['name' => 'Каменный Страж', 'hp' => 1000]);
        $normalMonster = MonsterOnLocation::query()->create([
            'monster_id' => $monster->id, 'hp_now' => 1000, 'hp_max' => 1000,
        ]);
        $bossInstance = MonsterOnLocation::query()->create([
            'monster_id' => $monster->id, 'hp_now' => 1000, 'hp_max' => 1000,
        ]);
        $run = ClanBossRun::query()->create([
            'dungeon_id' => $dungeon->id, 'clan_id' => $clan->id,
            'opened_by_user_id' => $leader->id, 'status' => ClanBossRun::STATUS_ACTIVE,
            'started_at' => now(), 'expires_at' => now()->addHour(),
        ]);
        $state = ClanBossRunStage::query()->create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $stage->id,
            'monster_on_location_id' => $bossInstance->id, 'hp_max' => 1000,
            'status' => ClanBossRunStage::STATUS_ACTIVE,
            'started_at' => now(), 'expires_at' => now()->addHour(),
        ]);
        $bossInstance->update(['clan_boss_stage_state_id' => $state->id]);

        $outsiderIds = MonsterOnLocation::query()->visibleTo($outsider)->pluck('id')->all();
        $memberIds = MonsterOnLocation::query()->visibleTo($member)->pluck('id')->all();
        $locationIds = MonsterOnLocation::query()->shownOnLocation()->pluck('id')->all();

        $this->assertEqualsCanonicalizing([$normalMonster->id], $outsiderIds);
        $this->assertEqualsCanonicalizing([$normalMonster->id, $bossInstance->id], $memberIds);
        $this->assertEqualsCanonicalizing([$normalMonster->id], $locationIds);
    }

    public function test_page_shows_run_boss_damage_and_attack_batch_cooldown(): void
    {
        $dungeon = Dungeon::query()->create([
            'name' => 'Логово', 'type' => DungeonType::TOWER,
            'is_active' => true, 'is_clan_boss' => true,
            'time_limit_seconds' => 3600,
            'clan_boss_hit_batch_size' => 10,
            'clan_boss_hit_batch_cooldown_seconds' => 60,
        ]);
        $stage = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id, 'number' => 1, 'name' => 'Первый',
            'time_limit_seconds' => 1800, 'completion_type' => 'kill_all',
            'spawn_type' => 'fixed', 'total_monsters' => 1,
        ]);
        $monster = Monster::query()->create(['name' => 'Босс', 'hp' => 1000, 'aggression' => 0, 'is_boss' => true]);
        $nextStage = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id, 'number' => 2, 'name' => 'Второй',
            'time_limit_seconds' => 1800, 'completion_type' => 'kill_all',
            'spawn_type' => 'fixed', 'total_monsters' => 1,
        ]);
        $nextMonster = Monster::query()->create(['name' => 'Следующий босс', 'hp' => 1500, 'aggression' => 0, 'is_boss' => true]);
        DB::table('dungeon_stage_monsters')->insert([
            'dungeon_stage_id' => $nextStage->id,
            'monster_id' => $nextMonster->id,
            'quantity' => 1,
            'weight' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $leader = User::query()->create(['name' => 'Лидер']);
        $fighter = User::query()->create(['name' => 'Боец']);
        $clan = Clan::query()->create(['name' => 'Клан', 'owner_id' => $leader->id]);
        $role = ClanRole::query()->create(['clan_id' => $clan->id, 'name' => 'Лидер', 'is_leader' => true]);
        ClanMember::query()->forceCreate(['clan_id' => $clan->id, 'user_id' => $leader->id, 'role_id' => $role->id]);
        ClanMember::query()->forceCreate(['clan_id' => $clan->id, 'user_id' => $fighter->id, 'role_id' => $role->id]);

        $run = ClanBossRun::query()->create([
            'dungeon_id' => $dungeon->id, 'clan_id' => $clan->id,
            'opened_by_user_id' => $leader->id, 'current_stage_id' => $stage->id,
            'status' => ClanBossRun::STATUS_ACTIVE,
            'started_at' => now(), 'expires_at' => now()->addHour(),
        ]);
        $instance = MonsterOnLocation::query()->create([
            'monster_id' => $monster->id, 'hp_now' => 700, 'hp_max' => 1000, 'active' => 1,
        ]);
        $state = ClanBossRunStage::query()->create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $stage->id,
            'monster_on_location_id' => $instance->id, 'hp_max' => 1000,
            'status' => ClanBossRunStage::STATUS_ACTIVE,
            'started_at' => now(), 'expires_at' => now()->addMinutes(30),
        ]);
        $instance->update(['clan_boss_stage_state_id' => $state->id]);

        ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $stage->id,
            'user_id' => $fighter->id, 'damage' => 300, 'hits' => 3,
            'last_hit_at' => now(),
        ]);
        ClanBossPlayerAttackState::query()->create([
            'clan_boss_run_id' => $run->id,
            'user_id' => $fighter->id,
            'hits_in_batch' => 3,
            'cooldown_until' => now()->addSeconds(60),
        ]);
        ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $stage->id,
            'user_id' => $leader->id, 'damage' => 100, 'hits' => 2,
            'last_hit_at' => now(),
        ]);

        $pastStage = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id, 'number' => 0, 'name' => 'Предыдущий этап',
            'completion_type' => 'kill_all', 'spawn_type' => 'fixed', 'total_monsters' => 1,
        ]);
        $pastMonster = Monster::query()->create(['name' => 'Побеждённый босс', 'hp' => 500]);
        $pastInstance = MonsterOnLocation::query()->create([
            'monster_id' => $pastMonster->id, 'hp_now' => 0, 'hp_max' => 500, 'active' => false,
        ]);
        $pastState = ClanBossRunStage::query()->create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $pastStage->id,
            'monster_on_location_id' => $pastInstance->id, 'hp_max' => 500,
            'status' => ClanBossRunStage::STATUS_KILLED,
            'started_at' => now()->subHours(2), 'expires_at' => now()->subHour(), 'killed_at' => now()->subHour(),
        ]);
        $pastInstance->update(['clan_boss_stage_state_id' => $pastState->id]);

        $page = $this->useCase()->execute($fighter);

        $this->assertSame($run->id, $page->run->id);
        $this->assertSame($state->id, $page->currentState->id);
        $this->assertSame('Босс', $page->currentState->bossInstance->monster->name);
        $this->assertSame('Следующий босс', $page->nextBoss->monster->name);
        $this->assertCount(1, $page->previousBosses);
        $this->assertSame('Побеждённый босс', $page->previousBosses->first()->bossInstance->monster->name);

        $this->assertCount(2, $page->damageRows);
        $this->assertSame('Боец', $page->damageRows[0]['user']->name);
        $this->assertSame(300, $page->damageRows[0]['damage']);
        $this->assertSame(100, $page->damageRows[1]['damage']);

        $this->assertSame(0, $page->hitsLeft);
        $this->assertNotNull($page->personalAvailableAt);
        $this->assertTrue($page->personalAvailableAt->gt(now()));
    }

    public function test_page_without_run_returns_empty_sections(): void
    {
        $user = User::query()->create(['name' => 'Одиночка']);
        $clan = Clan::query()->create(['name' => 'Клан', 'owner_id' => $user->id]);
        $role = ClanRole::query()->create(['clan_id' => $clan->id, 'name' => 'Лидер', 'is_leader' => true]);
        ClanMember::query()->forceCreate(['clan_id' => $clan->id, 'user_id' => $user->id, 'role_id' => $role->id]);

        $page = $this->useCase()->execute($user);

        $this->assertNull($page->run);
        $this->assertNull($page->currentState);
        $this->assertTrue($page->damageRows->isEmpty());
        $this->assertNull($page->hitsLeft);
        $this->assertNull($page->personalAvailableAt);
        $this->assertTrue($page->archiveRuns->isEmpty());
        $this->assertNull($page->archivedRun);

        $this->actingAs($user)
            ->withoutMiddleware(ApplyGameLocale::class)
            ->get(route('clan.boss'))
            ->assertOk();
    }

    public function test_page_lists_and_displays_results_for_a_previous_clan_boss_run(): void
    {
        $leader = User::query()->create(['name' => 'Лидер']);
        $fighter = User::query()->create(['name' => 'Боец']);
        $clan = Clan::query()->create(['name' => 'Клан', 'owner_id' => $leader->id]);
        $role = ClanRole::query()->create(['clan_id' => $clan->id, 'name' => 'Участник']);
        ClanMember::query()->forceCreate(['clan_id' => $clan->id, 'user_id' => $leader->id, 'role_id' => $role->id]);
        ClanMember::query()->forceCreate(['clan_id' => $clan->id, 'user_id' => $fighter->id, 'role_id' => $role->id]);

        $dungeon = Dungeon::query()->create([
            'name' => 'Башня Восхождения', 'type' => DungeonType::TOWER,
            'is_active' => true, 'is_clan_boss' => true,
        ]);
        $stage = DungeonStage::query()->create([
            'dungeon_id' => $dungeon->id, 'number' => 1, 'name' => 'Каменный Страж',
            'completion_type' => 'kill_all', 'spawn_type' => 'fixed', 'total_monsters' => 1,
        ]);
        $monster = Monster::query()->create(['name' => 'Каменный Страж', 'hp' => 1000]);
        $instance = MonsterOnLocation::query()->create([
            'monster_id' => $monster->id, 'hp_now' => 0, 'hp_max' => 1000, 'active' => false,
        ]);
        $run = ClanBossRun::query()->create([
            'dungeon_id' => $dungeon->id, 'clan_id' => $clan->id,
            'opened_by_user_id' => $leader->id, 'current_stage_id' => $stage->id,
            'status' => ClanBossRun::STATUS_COMPLETED,
            'started_at' => now()->subDay(), 'expires_at' => now(), 'finished_at' => now()->subHours(2),
        ]);
        $state = ClanBossRunStage::query()->create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $stage->id,
            'monster_on_location_id' => $instance->id, 'hp_max' => 1000,
            'status' => ClanBossRunStage::STATUS_KILLED,
            'started_at' => now()->subDay(), 'expires_at' => now()->subHours(20), 'killed_at' => now()->subHours(20),
        ]);
        $instance->update(['clan_boss_stage_state_id' => $state->id]);
        ClanBossContribution::query()->create([
            'clan_boss_run_id' => $run->id, 'dungeon_stage_id' => $stage->id,
            'user_id' => $fighter->id, 'damage' => 1000, 'hits' => 10,
            'last_hit_at' => now()->subHours(20), 'rewarded' => true,
        ]);

        $page = $this->useCase()->execute($fighter);

        $this->assertSame(1, $page->archiveRuns->total());
        $this->assertSame($run->id, $page->archivedRun->id);
        $this->assertSame('Каменный Страж', $page->archivedStages->first()->stage->name);
        $this->assertSame(1000, (int) $page->archivedStageDamage->get($stage->id));
        $this->assertSame('Боец', $page->archivedDamageRows->first()['user']->name);
        $this->assertTrue($page->archivedDamageRows->first()['rewarded']);

        $selectedPage = $this->useCase()->execute($fighter, $run->id);
        $this->assertSame($run->id, $selectedPage->archivedRun->id);
    }

    private function useCase(): GetClanBossPage
    {
        return new GetClanBossPage(
            new ResolveClanContext,
            new ClanBossService(
                Mockery::mock(BackpackService::class),
                new ClanBossRewardService(
                    Mockery::mock(BackpackService::class),
                    Mockery::mock(ExperienceService::class),
                    new ClanExperienceService(new ClanLevelService),
                    new ClanLogService,
                ),
            ),
        );
    }

    private function createSchema(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('player_id')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->boolean('is_admin')->default(false);
            $table->integer('warehouse_count')->default(50);
            $table->integer('bag_count')->default(25);
            $table->integer('slot_count')->default(3);
            $table->timestamps();
        });
        Schema::create('clans', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('lvl')->default(1);
            $table->decimal('experience', 18, 2)->default(0);
            $table->unsignedBigInteger('treasury')->default(0);
            $table->unsignedInteger('warehouse_capacity')->default(50);
            $table->unsignedBigInteger('owner_id');
            $table->timestamps();
        });
        Schema::create('clan_roles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_id');
            $table->string('name');
            $table->unsignedBigInteger('permissions')->default(0);
            $table->boolean('is_leader')->default(false);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
        Schema::create('clan_members', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->unsignedInteger('points')->default(0);
            $table->decimal('experience_contributed', 18, 2)->default(0);
            $table->timestamps();
        });
        Schema::create('dungeons', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->boolean('is_clan_boss')->default(false);
            $table->unsignedTinyInteger('min_clan_days')->default(3);
            $table->unsignedBigInteger('min_damage')->default(0);
            $table->unsignedBigInteger('entry_share_item_id')->nullable();
            $table->unsignedInteger('time_limit_seconds')->nullable();
            $table->unsignedSmallInteger('clan_boss_hit_batch_size')->default(10);
            $table->unsignedInteger('clan_boss_hit_batch_cooldown_seconds')->default(21600);
            $table->timestamps();
        });
        Schema::create('dungeon_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->integer('number');
            $table->string('name');
            $table->integer('time_limit_seconds')->nullable();
            $table->string('completion_type');
            $table->string('spawn_type');
            $table->integer('total_monsters');
            $table->unsignedBigInteger('next_stage_id')->nullable();
            $table->unsignedInteger('max_hits_per_user')->nullable();
            $table->unsignedInteger('personal_cooldown_seconds')->nullable();
            $table->unsignedInteger('clan_cooldown_seconds')->nullable();
            $table->timestamps();
        });
        Schema::create('dungeon_stage_monsters', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->unsignedSmallInteger('weight')->default(1);
            $table->timestamps();
        });
        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->integer('hp');
            $table->integer('aggression')->default(0);
            $table->integer('exp')->default(0);
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->unsignedBigInteger('clan_boss_stage_state_id')->nullable();
            $table->unsignedBigInteger('monster_id');
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->boolean('active')->default(true);
            $table->integer('aggression')->nullable();
            $table->boolean('is_drop_money')->default(0);
            $table->integer('current_phase')->default(1);
            $table->timestamps();
        });
        Schema::create('clan_boss_runs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('dungeon_id');
            $table->unsignedBigInteger('clan_id');
            $table->unsignedBigInteger('opened_by_user_id');
            $table->unsignedBigInteger('current_stage_id')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
        Schema::create('clan_boss_player_attack_states', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_boss_run_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedSmallInteger('hits_in_batch')->default(0);
            $table->timestamp('cooldown_until')->nullable();
            $table->timestamps();
            $table->unique(['clan_boss_run_id', 'user_id']);
        });
        Schema::create('clan_boss_run_stages', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_boss_run_id');
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('monster_on_location_id')->nullable();
            $table->unsignedBigInteger('hp_max');
            $table->string('status', 20)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('killed_at')->nullable();
            $table->timestamps();
        });
        Schema::create('clan_boss_contributions', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('clan_boss_run_id');
            $table->unsignedBigInteger('dungeon_stage_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('damage')->default(0);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamp('last_hit_at')->nullable();
            $table->boolean('rewarded')->default(false);
            $table->timestamps();
        });
    }
}
