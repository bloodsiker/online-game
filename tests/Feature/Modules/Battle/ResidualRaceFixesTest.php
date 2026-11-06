<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\AttackService;
use App\Modules\Battle\Application\Services\MonsterLootService;
use App\Modules\Battle\Infrastructure\Persistence\BattleRepository;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Clan\Domain\Services\ClanExperienceService;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\ExperienceService;
use App\Modules\Player\Domain\Services\PlayerSkillService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Skill\Infrastructure\Persistence\Models\Skill;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Mockery;
use ReflectionClass;
use ReflectionMethod;
use Tests\Support\CreatesBattleFixture;
use Tests\TestCase;

class ResidualRaceFixesTest extends TestCase
{
    use CreatesBattleFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->createBattleTables();
        $this->seedBattleFixture();
        Event::fake();
    }

    public function test_removal_rechecks_a_stale_stack_and_returns_the_real_result(): void
    {
        $service = app(BackpackService::class);
        $stale = Backpack::findOrFail(1);
        DB::table('backpacks')->where('id', 1)->update(['count' => 1]);
        $remove = new ReflectionMethod(BackpackService::class, 'removeBackpackItem');
        $this->assertFalse($remove->invoke($service, $stale, 2));
        $this->assertFalse($remove->invoke($service, $stale, 0));
        $this->assertTrue($remove->invoke($service, $stale, 1));
        $this->assertFalse($remove->invoke($service, $stale, 1));
        $this->assertDatabaseCount('backpacks', 0);
    }

    public function test_repeated_join_does_not_insert_a_duplicate_participant(): void
    {
        DB::table('battles')->insert(['id' => 1, 'location_id' => 1]);
        Schema::table('battle_details', fn (Blueprint $table) => $table->unique(['battle_id', 'user_id']));
        $repo = app(BattleRepository::class);
        $first = $repo->createBattleDetails(Battle::findOrFail(1), $this->economyUser());
        $second = $repo->createBattleDetails(Battle::findOrFail(1), $this->economyUser());
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('battle_details', 1);
    }

    private function chain(int $hp = 4, bool $active = true): AttackService
    {
        Schema::table('monsters', function (Blueprint $table): void {
            $table->integer('exp')->default(100);
            $table->integer('lvl')->default(1);
        });
        Schema::table('players', fn (Blueprint $table) => $table->integer('lvl')->default(1));
        DB::table('battles')->insert(['id' => 1, 'location_id' => 1]);
        DB::table('monster_on_locations')->insert(['id' => 2, 'monster_id' => 1, 'location_id' => 1, 'hp_now' => $hp, 'active' => $active]);
        DB::table('battle_details')->insert([
            ['battle_id' => 1, 'location_monster_id' => 1],
            ['battle_id' => 1, 'location_monster_id' => 2],
        ]);
        $xp = Mockery::mock(ExperienceService::class);
        $xp->shouldReceive('calculateGain')->andReturn(5);
        $clan = Mockery::mock(ClanExperienceService::class);
        $clan->shouldReceive('awardForMonsterExperience');
        $reflection = new ReflectionClass(AttackService::class);
        $service = $reflection->newInstanceWithoutConstructor();
        foreach (['experienceService' => $xp, 'clanExperienceService' => $clan, 'monsterLootService' => new MonsterLootService] as $property => $value) {
            $reflection->getProperty($property)->setValue($service, $value);
        }

        return $service;
    }

    private function hitChain(AttackService $service): void
    {
        (new ReflectionMethod(AttackService::class, 'applyChainAttack'))->invoke($service,
            Player::findOrFail(1), MonsterOnLocation::findOrFail(1), Battle::findOrFail(1), 20, 1.0, new AttackResultDTO);
    }

    public function test_chain_uses_secondary_hp_and_records_only_actual_damage(): void
    {
        $service = $this->chain();
        DB::transaction(fn () => $this->hitChain($service));
        DB::transaction(fn () => $this->hitChain($service));
        $this->assertDatabaseHas('monster_on_locations', ['id' => 2, 'hp_now' => 0]);
        $this->assertDatabaseHas('monster_on_locations', ['id' => 1, 'hp_now' => 20]);
        $this->assertDatabaseCount('battle_monster_damage', 1);
        $this->assertDatabaseHas('battle_monster_damage', ['location_monster_id' => 2, 'damage' => 4]);
    }

    public function test_chain_skips_an_inactive_secondary_spawn(): void
    {
        $service = $this->chain(active: false);
        DB::transaction(fn () => $this->hitChain($service));
        $this->assertDatabaseHas('monster_on_locations', ['id' => 2, 'hp_now' => 4]);
        $this->assertDatabaseCount('battle_monster_damage', 0);
    }

    public function test_learning_a_weapon_skill_reuses_its_row_and_keeps_each_experience_gain(): void
    {
        Schema::create('player_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('skill_id');
            foreach (['lvl', 'exp', 'exp_up', 'exp_diff'] as $field) {
                $table->integer($field)->default(0);
            }
            $table->timestamps();
            $table->unique(['player_id', 'skill_id']);
        });
        Schema::create('skill_level_requirements', function (Blueprint $table): void {
            $table->id();
            foreach (['skill_id', 'lvl', 'exp_required', 'exp_diff'] as $field) {
                $table->integer($field);
            }
        });
        $skill = (new Skill)->forceFill(['id' => 1]);
        $service = app(PlayerSkillService::class);
        $player = Player::findOrFail(1);
        $service->gainExperienceSkill(player: $player, skill: $skill);
        $service->gainExperienceSkill(player: $player, skill: $skill);
        $service->gainExperienceSkill(player: $player, skill: $skill);
        $this->assertDatabaseCount('player_skills', 1);
        $this->assertDatabaseHas('player_skills', ['player_id' => 1, 'skill_id' => 1, 'exp' => 2, 'lvl' => 1]);
    }

    public function test_bank_constraint_rejects_invalid_writes_without_changing_valid_balance(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->bigInteger('bank_balance')->nullable()->default(0));
        $migration = require database_path('migrations/2026_11_04_000001_add_bank_balance_constraint.php');
        $migration->up();
        $migration->up();
        foreach ([-1, null, '9223372036854775808'] as $invalid) {
            try {
                DB::table('users')->where('id', 1)->update(['bank_balance' => $invalid]);
                $this->fail('Invalid bank balance must fail.');
            } catch (QueryException) {
                $this->assertDatabaseHas('users', ['id' => 1, 'bank_balance' => 0]);
            }
        }
        DB::table('users')->where('id', 1)->update(['bank_balance' => PHP_INT_MAX]);
        $this->assertSame(PHP_INT_MAX, DB::table('users')->where('id', 1)->value('bank_balance'));
        $migration->down();
        DB::table('users')->where('id', 1)->update(['bank_balance' => -1]);
        try {
            $migration->up();
            $this->fail('Preflight must preserve invalid data for an explicit correction.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('No rows were changed', $exception->getMessage());
            $this->assertDatabaseHas('users', ['id' => 1, 'bank_balance' => -1]);
        }
    }
}
