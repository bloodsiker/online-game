<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Domain\Services\MonsterCombatantFactory;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Final review, CRITICAL 2 — жизненный цикл чистого стат-дебаффа на монстре:
 *
 * 1. processMonsterEffects() умел списывать стаки только у стана и DoT, а строка
 *    с type = NULL (дебафф из одних stat_modifiers, например «Разъедающая
 *    ржавчина») не декрементилась и не удалялась никогда — только вместе со
 *    смертью моба (AttackService::handleMonsterDeath).
 * 2. MonsterCombatantFactory::build() выбирал дебаффы спавна без фильтра по
 *    battle_id, поэтому дебафф, наложенный в чужом бою, продолжал резать броню
 *    этого моба всем следующим игрокам.
 *
 * Заодно закрывает провал в покрытии read-path дебаффов (IMPORTANT 9): именно
 * здесь впервые становится наблюдаемым и деление длительности дебаффа пополам
 * на боссах (Task 9).
 */
class MonsterDebuffLifecycleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('monsters', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->integer('armor')->default(0);
            $table->integer('dodge')->default(0);
            $table->integer('critical')->default(0);
            $table->integer('lvl')->default(1);
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->integer('hp_now')->default(100);
            $table->integer('hp_max')->default(100);
            $table->boolean('is_drop_money')->default(false);
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('debuff');
            $table->string('slug')->default('corroding_rust');
            $table->string('type')->default('debuff');
            $table->text('description')->nullable();
            $table->integer('chance')->default(0);
            $table->boolean('is_stackable')->default(false);
            $table->integer('max_stacks')->default(1);
            $table->integer('tick_interval')->default(1);
            $table->integer('value_per_tick')->nullable();
            $table->json('stat_modifiers')->nullable();
            $table->boolean('is_dispellable')->default(true);
            $table->timestamps();
        });
        Schema::create('monster_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_monster_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->integer('rounds')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    private function armorDebuff(): Effect
    {
        return Effect::create([
            'name' => 'Разъедающая ржавчина',
            'slug' => 'corroding_rust', // нет такого кейса в ActiveEffectType → type = NULL
            'type' => 'debuff',
            'stat_modifiers' => [['type' => 'armor', 'value' => -15, 'is_percent' => false]],
        ]);
    }

    private function spawn(bool $isBoss = false, int $armor = 50): MonsterOnLocation
    {
        $monster = Monster::create(['name' => 'Ржавый голем', 'armor' => $armor, 'lvl' => 10, 'is_boss' => $isBoss]);

        return MonsterOnLocation::create(['monster_id' => $monster->id, 'hp_now' => 200, 'hp_max' => 200]);
    }

    public function test_null_type_debuff_lives_for_seconds_not_for_battle_rounds(): void
    {
        $locMonster = $this->spawn();
        $battle = Battle::create();
        $service = app(BattleEffectService::class);

        $service->applyEffectToMonster($this->armorDebuff(), $locMonster, $battle, new AttackResultDTO, durationSeconds: 3);

        $row = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->first();
        $this->assertNotNull($row);
        $this->assertNull($row->type, 'чистый стат-дебафф хранится без ActiveEffectType — это и есть проблемный случай');
        $this->assertSame(3, (int) $row->stacks);

        $this->assertSame(
            3,
            (int) DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('stacks'),
            'повторный ход не должен сокращать эффект без прошедшего времени',
        );

        DB::table('monster_active_effects')
            ->where('location_monster_id', $locMonster->id)
            ->update(['expires_at' => now()->subSecond()]);
        $service->processMonsterEffects($locMonster, $battle, new AttackResultDTO);

        $this->assertSame(
            0,
            DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->count(),
            'по истечении времени строка обязана удаляться, а не висеть до смерти моба',
        );
        $this->assertSame(200, $locMonster->hp_now, 'чистый стат-дебафф не наносит урона');
    }

    public function test_expired_debuff_stops_affecting_the_monster_stats(): void
    {
        $locMonster = $this->spawn(armor: 50);
        $battle = Battle::create();
        $service = app(BattleEffectService::class);
        $factory = new MonsterCombatantFactory;

        $service->applyEffectToMonster($this->armorDebuff(), $locMonster, $battle, new AttackResultDTO, durationSeconds: 2);
        $this->assertSame(35, $factory->build($locMonster, $battle->id)->getArmor());

        DB::table('monster_active_effects')
            ->where('location_monster_id', $locMonster->id)
            ->update(['expires_at' => now()->subSecond()]);
        $service->processMonsterEffects($locMonster, $battle, new AttackResultDTO);

        $this->assertSame(50, $factory->build($locMonster, $battle->id)->getArmor(), 'после истечения дебафф обязан перестать резать броню');
    }

    public function test_debuff_from_another_battle_persists_on_the_same_monster(): void
    {
        $locMonster = $this->spawn(armor: 50);
        $battleA = Battle::create();
        $service = app(BattleEffectService::class);
        $factory = new MonsterCombatantFactory;

        $service->applyEffectToMonster($this->armorDebuff(), $locMonster, $battleA, new AttackResultDTO, durationSeconds: 10);
        $this->assertSame(35, $factory->build($locMonster, $battleA->id)->getArmor(), 'в своём бою дебафф обязан действовать');

        // Бой A брошен, монстр не убит — строка дебаффа осталась висеть на спавне.
        $battleB = Battle::create();

        $this->assertSame(
            35,
            $factory->build($locMonster, $battleB->id)->getArmor(),
            'дебафф должен переживать завершение первого боя',
        );
        $this->assertSame(
            35,
            $factory->build($locMonster, $battleA->id)->getArmor(),
            'при этом в исходном бою он продолжает действовать',
        );
    }

    public function test_recasting_the_same_debuff_refreshes_one_persistent_row(): void
    {
        // Re-review finding: applyEffectToMonster()'s $existing lookup only scoped by
        // location_monster_id + type/effect_id, not battle_id — so a stale row left over
        // from an abandoned battle A got silently refreshed (and re-targeted) by a cast in
        // a brand-new battle B, while MonsterCombatantFactory::build() (scoped to B) never
        // saw it. The debuff appeared to succeed but had no effect for the caster.
        $locMonster = $this->spawn(armor: 50);
        $battleA = Battle::create();
        $service = app(BattleEffectService::class);
        $factory = new MonsterCombatantFactory;

        // Same Effect row cast twice — this is what makes the $existing lookup in
        // applyEffectToMonster() match across battles when it isn't scoped by battle_id.
        $effect = $this->armorDebuff();

        $service->applyEffectToMonster($effect, $locMonster, $battleA, new AttackResultDTO, durationSeconds: 10);
        $this->assertSame(35, $factory->build($locMonster, $battleA->id)->getArmor());

        // Battle A abandoned without the monster dying — its debuff row is now stale.
        $battleB = Battle::create();
        $service->applyEffectToMonster($effect, $locMonster, $battleB, new AttackResultDTO, durationSeconds: 10);

        $this->assertSame(
            35,
            $factory->build($locMonster, $battleB->id)->getArmor(),
            'дебафф, наложенный заново в новом бою, обязан продолжать действовать',
        );
        $this->assertSame(
            1,
            DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->count(),
            'повторный каст должен обновить один постоянный эффект, а не создать стак',
        );
    }

    public function test_boss_halved_debuff_duration_is_applied_in_seconds(): void
    {
        $locMonster = $this->spawn(isBoss: true, armor: 60);
        $battle = Battle::create();
        $service = app(BattleEffectService::class);
        $factory = new MonsterCombatantFactory;

        // duration 6 → на боссе 3 (BOSS_DEBUFF_DURATION_MULTIPLIER = 0.5)
        $service->applyEffectToMonster($this->armorDebuff(), $locMonster, $battle, new AttackResultDTO, durationSeconds: 6);
        $this->assertSame(
            3,
            (int) DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('stacks'),
        );

        $this->assertSame(45, $factory->build($locMonster, $battle->id)->getArmor(), 'дебафф активен до истечения времени');
        DB::table('monster_active_effects')
            ->where('location_monster_id', $locMonster->id)
            ->update(['expires_at' => now()->subSecond()]);
        $service->processMonsterEffects($locMonster, $battle, new AttackResultDTO);
        $this->assertSame(60, $factory->build($locMonster, $battle->id)->getArmor(), 'укороченная вдвое длительность истекает по времени');
    }

    public function test_stun_and_paralysis_block_by_elapsed_seconds_not_round_count(): void
    {
        foreach ([ActiveEffectType::STUN, ActiveEffectType::PARALYSIS] as $controlType) {
            $locMonster = $this->spawn();
            $battle = Battle::create();
            $effect = Effect::create([
                'name' => $controlType->label(),
                'slug' => $controlType->value,
                'type' => 'debuff',
            ]);
            $service = app(BattleEffectService::class);

            $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, durationSeconds: 5);

            $this->assertTrue($service->processMonsterEffects($locMonster, $battle, new AttackResultDTO));
            $stacksAfterFirstCheck = (int) DB::table('monster_active_effects')
                ->where('location_monster_id', $locMonster->id)
                ->value('stacks');

            $this->assertTrue(
                $service->processMonsterEffects($locMonster, $battle, new AttackResultDTO),
                'повторная проверка в ту же секунду не должна снимать контроль',
            );
            $this->assertSame(
                $stacksAfterFirstCheck,
                (int) DB::table('monster_active_effects')
                    ->where('location_monster_id', $locMonster->id)
                    ->value('stacks'),
                'число боевых действий не должно сокращать длительность контроля',
            );

            DB::table('monster_active_effects')
                ->where('location_monster_id', $locMonster->id)
                ->update(['expires_at' => now()->subSecond()]);

            $this->assertFalse($service->processMonsterEffects($locMonster, $battle, new AttackResultDTO));
            $this->assertDatabaseMissing('monster_active_effects', [
                'location_monster_id' => $locMonster->id,
                'type' => $controlType->value,
            ]);
        }
    }
}
