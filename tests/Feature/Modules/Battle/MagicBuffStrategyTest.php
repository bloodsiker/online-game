<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Application\Services\Combat\Strategies\MagicBuffStrategy;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Services\MagicCastGuard;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Final review, IMPORTANT 6 и IMPORTANT 5 — боевой путь баффов/хилов:
 *
 * 6. MagicBuffStrategy проверял ману самодельным `if` с in-memory `-=` и вообще
 *    не писал кулдаун: боевой бафф спамился каждый раунд, а ману могли списать
 *    дважды параллельные запросы. Теперь каст идёт через MagicCastGuard.
 * 5. Хил в бою лечил плоский base_healing без всякого масштабирования, тогда как
 *    внебоевой путь (UseMagicSkill) уже считал через MagicHitCalculator::heal().
 *    Одно и то же заклинание давало разное лечение в зависимости от контекста.
 *
 * Структура схемы/сетапа зеркалит MagicCastGuardTest.
 */
class MagicBuffStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->integer('mp_now')->default(100);
            $table->integer('hp_now')->default(10);
            $table->integer('hp_max')->default(100);
            $table->float('experience_multiplier')->default(1.0);
            $table->timestamps();
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->string('type')->default('buff');
            $table->integer('mana_cost')->default(0);
            $table->integer('cooldown')->default(0);
            $table->float('power_coefficient')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->integer('base_healing')->default(0);
            $table->boolean('is_passive')->default(false);
            $table->timestamps();
        });
        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedSmallInteger('sort_order')->default(0);
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance')->default(100);
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('effect');
            $table->string('slug')->default('effect');
            $table->json('stat_modifiers')->nullable();
        });
    }

    /** @param  array<string, int|string>  $skillOverrides */
    private function makeStrategy(Player $player, array $skillOverrides, ?StatSheet $sheet = null): MagicBuffStrategy
    {
        $skill = MagicSkill::create(['name' => 'Прилив магии', 'type' => 'buff', ...$skillOverrides]);
        DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $skill->id]);

        return new MagicBuffStrategy(
            castGuard: app(MagicCastGuard::class),
            magicHitCalc: app(MagicHitCalculator::class),
            casterSheet: $sheet ?? $this->sheet(),
            playerModel: $player,
            magicSkill: $skill,
        );
    }

    private function sheet(int $intelligence = 0, int $hpMax = 100): StatSheet
    {
        $sheet = new StatSheet;
        $sheet->intelligence = $intelligence;
        $sheet->hpMax = $hpMax;
        $sheet->level = 12;

        return $sheet;
    }

    public function test_second_cast_inside_the_cooldown_window_is_rejected(): void
    {
        $player = Player::forceCreate(['mp_now' => 100, 'hp_now' => 100, 'hp_max' => 100]);
        $strategy = $this->makeStrategy($player, ['mana_cost' => 10, 'cooldown' => 30]);

        $first = $strategy->getHits()[0];
        $second = $strategy->getHits()[0];

        $this->assertFalse($first->isCantCast(), 'первый каст обязан пройти');
        $this->assertTrue($second->isCantCast(), 'повторный каст в окне кулдауна обязан быть отклонён');
        $this->assertStringContainsString('Перезарядк', (string) $second->getMessage());
        $this->assertSame(90, $player->fresh()->mp_now, 'мана списывается ровно один раз');
    }

    public function test_mana_is_deducted_in_the_database_not_only_in_memory(): void
    {
        $player = Player::forceCreate(['mp_now' => 100, 'hp_now' => 100, 'hp_max' => 100]);
        $strategy = $this->makeStrategy($player, ['mana_cost' => 22, 'cooldown' => 0]);

        $strategy->getHits();

        $this->assertSame(78, $player->fresh()->mp_now, 'без записи в БД параллельный запрос списал бы ту же ману повторно');
        $this->assertSame(78, $player->mp_now, 'in-memory модель обязана остаться синхронной с БД');
    }

    public function test_repeated_casts_cannot_overdraw_mana(): void
    {
        $player = Player::forceCreate(['mp_now' => 25, 'hp_now' => 100, 'hp_max' => 100]);
        $strategy = $this->makeStrategy($player, ['mana_cost' => 10, 'cooldown' => 0]);

        $strategy->getHits();
        $strategy->getHits();
        $third = $strategy->getHits()[0];

        $this->assertTrue($third->isCantCast());
        $this->assertStringContainsString('Недостаточно маны', (string) $third->getMessage());
        $this->assertSame(5, $player->fresh()->mp_now);
    }

    public function test_in_battle_heal_scales_with_intelligence(): void
    {
        $dumbPlayer = Player::forceCreate(['mp_now' => 100, 'hp_now' => 10, 'hp_max' => 500]);
        $this->makeStrategy(
            $dumbPlayer,
            ['mana_cost' => 0, 'cooldown' => 0, 'base_healing' => 40, 'power_coefficient' => 0.35],
            $this->sheet(intelligence: 0, hpMax: 500),
        )->getHits();

        $smartPlayer = Player::forceCreate(['mp_now' => 100, 'hp_now' => 10, 'hp_max' => 500]);
        $this->makeStrategy(
            $smartPlayer,
            ['mana_cost' => 0, 'cooldown' => 0, 'base_healing' => 40, 'power_coefficient' => 0.35],
            $this->sheet(intelligence: 100, hpMax: 500),
        )->getHits();

        // 10 + 40 = 50 против 10 + (40 + round(100 × 0.35)) = 85 — та же формула,
        // что у внебоевого пути в UseMagicSkill.
        $this->assertSame(50, $dumbPlayer->hp_now);
        $this->assertSame(85, $smartPlayer->hp_now);
    }

    public function test_heal_is_clamped_by_the_resolved_sheet_hp_max_not_the_raw_model_field(): void
    {
        // hp_max в модели устарел (100), реальный потолок из StatSheet — 300.
        $player = Player::forceCreate(['mp_now' => 100, 'hp_now' => 290, 'hp_max' => 100]);

        $this->makeStrategy(
            $player,
            ['mana_cost' => 0, 'cooldown' => 0, 'base_healing' => 100, 'power_coefficient' => 0.0],
            $this->sheet(intelligence: 0, hpMax: 300),
        )->getHits();

        $this->assertSame(300, $player->hp_now, 'лечение обязано упираться в эффективный максимум HP из StatSheet');
    }
}
