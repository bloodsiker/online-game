<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Database\Seeders\MagicBookStarterSeeder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\StubCombatant;
use Tests\TestCase;

/**
 * Final review, IMPORTANT 4: бафф «Прилив магии» из стартового набора книг
 * давал «+25% к magic_attack», а magic_attack по дизайну считается только с
 * экипировки (база 0.0 в PlayerStatService) — 25% от нуля есть ноль, заклинание
 * не делало ничего. Теперь бафф поднимает интеллект, который реально кормит
 * MagicHitCalculator::magicPower().
 */
class SeededBuffSpellScalesMagicTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        // Схема — объединение того, что нужно сидеру книг и что трогает
        // PlayerStatService::resolve(). Полный набор миграций на sqlite не
        // поднимается (см. SeededDotSpellTicksTest), поэтому таблицы ручные.
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedInteger('lvl')->default(1);
            $table->float('strength')->default(1);
            $table->float('agility')->default(1);
            $table->float('intuition')->default(1);
            $table->float('wisdom')->default(1);
            $table->float('intelligence')->default(1);
            $table->float('endurance')->default(1);
            $table->integer('min_dmg')->default(0);
            $table->integer('max_dmg')->default(0);
            $table->integer('mp_max')->default(10);
            $table->integer('mp_now')->default(10);
            $table->integer('hp_now')->default(10);
            $table->unsignedInteger('free_stats')->default(0);
            $table->float('experience_multiplier')->default(1.0);
            $table->timestamps();
        });
        Schema::create('player_equipments', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->timestamps();
        });
        Schema::create('items', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
        });
        Schema::create('item_gems', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('socket_index')->default(0);
        });
        Schema::create('item_runes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedInteger('slot_index')->default(0);
            $table->text('stats')->nullable();
            $table->text('passive_skill')->nullable();
            $table->unsignedInteger('reroll_count')->default(0);
        });
        Schema::create('share_item_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('stat_type');
            $table->float('value');
            $table->string('value_type');
        });
        Schema::create('player_item_buffs', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->string('effect_type');
            $table->float('value');
            $table->string('value_type');
            $table->timestamp('expires_at');
            $table->timestamps();
        });
        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->unsignedBigInteger('source_player_id')->nullable();
            $table->unsignedBigInteger('source_magic_skill_id')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(1);
            $table->float('current_value')->nullable();
            $table->float('tick_remainder')->default(0);
            $table->timestamps();
        });
        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->string('type')->default('attack');
            $table->string('target_type')->default('enemy');
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('mana_cost')->default(0);
            $table->integer('min_damage')->default(0);
            $table->integer('max_damage')->default(0);
            $table->float('power_coefficient')->default(0);
            $table->integer('base_healing')->default(0);
            $table->integer('cooldown')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->boolean('is_passive')->default(false);
            $table->json('effects')->nullable();
            $table->timestamps();
        });
        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('chance')->default(100);
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->timestamps();
        });
        Schema::create('magic_skill_requirements', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->string('type');
            $table->string('stat_key')->nullable();
            $table->unsignedBigInteger('skill_id')->nullable();
            $table->integer('min_value')->default(0);
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('effect');
            $table->string('slug');
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
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('type')->default('resource');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('is_two_hand')->default(0);
            $table->integer('count_use')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_sell')->default(true);
            $table->boolean('is_auction_sellable')->default(false);
            $table->boolean('is_give')->default(true);
            $table->boolean('is_droppable')->default(true);
            $table->boolean('is_slot_usable')->default(false);
            $table->boolean('is_weight')->default(true);
            $table->integer('price')->default(0);
            $table->integer('break_crystal')->default(0);
            $table->timestamps();
        });
        Schema::create('magic_skill_books', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamps();
        });

        $spellSkillId = DB::table('skills')->insertGetId(['name' => 'Колдовство']);

        foreach (['fire_spark', 'flame_barrage', 'incinerating_vortex'] as $slug) {
            MagicSkill::create([
                'name' => $slug, 'slug' => $slug, 'type' => 'attack', 'target_type' => 'enemy',
                'skill_id' => $spellSkillId, 'level' => 1,
            ]);
        }

        $this->artisan('db:seed', ['--class' => MagicBookStarterSeeder::class])->run();
    }

    public function test_seeded_buff_targets_a_stat_that_actually_feeds_magic_power(): void
    {
        $effect = MagicSkill::where('slug', 'arcane_surge_skill')
            ->firstOrFail()
            ->skillEffects()
            ->where('effects.slug', 'arcane_surge')
            ->firstOrFail();

        $types = array_column($effect->stat_modifiers, 'type');

        $this->assertContains('intelligence', $types);
        $this->assertNotContains(
            'magic_attack',
            $types,
            'magic_attack — стат исключительно с экипировки (база 0.0), процент от него всегда даёт 0',
        );
    }

    public function test_seeded_buff_measurably_raises_magic_damage(): void
    {
        $effect = MagicSkill::where('slug', 'arcane_surge_skill')
            ->firstOrFail()
            ->skillEffects()
            ->where('effects.slug', 'arcane_surge')
            ->firstOrFail();
        $player = $this->player(['intelligence' => 40.0, 'lvl' => 12]);

        $statService = app(PlayerStatService::class);
        $calculator = app(MagicHitCalculator::class);
        $dummy = new StubCombatant(level: 12, magicResistance: 0);

        $sheetBefore = $statService->resolve($player);
        $this->assertSame(40, $sheetBefore->getIntelligence());

        // min == max — бросок детерминирован, разница в уроне идёт только от магсилы.
        $damageBefore = $calculator->hit($sheetBefore, $dummy, minDamage: 10, maxDamage: 10, powerCoefficient: 1.0)->getDamage();
        $this->assertSame(50, $damageBefore, '10 брошенных + 40 интеллекта × 1.0');

        // Так бафф ложится на игрока вне боя — см. UseMagicSkill → BattleEffectService::applyEffectToPlayer().
        PlayerActiveEffect::create([
            'player_id' => $player->id,
            'effect_id' => $effect->id,
            'battle_id' => null,
            'applied_at' => now(),
            'expires_at' => now()->addSeconds((int) $effect->pivot->duration_seconds),
            'stacks' => 0,
        ]);
        $statService->invalidate($player);

        $sheetAfter = $statService->resolve($player);
        $damageAfter = $calculator->hit($sheetAfter, $dummy, minDamage: 10, maxDamage: 10, powerCoefficient: 1.0)->getDamage();

        $this->assertSame(50, $sheetAfter->getIntelligence(), '+25% к 40 интеллекта');
        $this->assertSame(60, $damageAfter);
        $this->assertGreaterThan($damageBefore, $damageAfter, 'бафф обязан реально увеличивать урон заклинаний');
    }

    /** @param  array<string, float|int>  $overrides */
    private function player(array $overrides): Player
    {
        $player = new Player;
        $player->forceFill([
            'lvl' => 1,
            'free_stats' => 0,
            'strength' => 1.0,
            'intuition' => 1.0,
            'agility' => 1.0,
            'wisdom' => 1.0,
            'intelligence' => 1.0,
            'endurance' => 1.0,
            'mp_max' => 10,
            'min_dmg' => 0,
            'max_dmg' => 0,
            ...$overrides,
        ]);
        $player->save();

        return $player;
    }
}
