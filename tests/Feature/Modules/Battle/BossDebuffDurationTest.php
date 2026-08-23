<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Task 9: боссы не иммунны к дебаффам, но держат их вдвое короче (halved
 * duration) вместо full immunity — см. BattleEffectService::applyEffectToMonster().
 *
 * Заодно (Part 2, carried-forward fix from Task 5's review): applyEffectToMonster()
 * ранее молча дропала любой Effect, чей slug не совпадал ни с одним кейсом
 * ActiveEffectType — включая чистые дебаффы стата (например «armor down»),
 * у которых просто нет DoT/stun/regen поведения. Теперь такие дебаффы должны
 * пройти дальше и создать строку MonsterActiveEffect с type: null, а повторное
 * наложение того же эффекта должно матчиться по effect_id (не по type, который
 * для этих эффектов null и иначе склеил бы разные дебаффы в одну строку).
 */
class BossDebuffDurationTest extends TestCase
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
            $table->boolean('is_boss')->default(false);
            $table->timestamps();
        });
        // Real table name per MonsterOnLocation::$table — NOT the
        // location<->monster spawn pivot ("location_has_monsters").
        Schema::create('monster_on_locations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('monster_id');
            $table->unsignedBigInteger('location_id')->nullable();
            $table->unsignedBigInteger('dungeon_session_id')->nullable();
            $table->integer('hp_now')->default(100);
            $table->integer('hp_max')->default(100);
            $table->timestamp('last_regen_at')->nullable();
            $table->tinyInteger('active')->default(1);
            $table->unsignedTinyInteger('aggression')->nullable();
            $table->boolean('is_drop_money')->default(false);
            $table->integer('current_phase')->default(1);
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('debuff');
            $table->string('slug')->default('armor_down');
            $table->string('type')->default('debuff');
            $table->text('description')->nullable();
            $table->integer('chance')->default(0);
            $table->integer('duration')->default(8);
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
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->integer('rounds')->default(1);
            $table->tinyInteger('status')->default(1);
            $table->json('boss_metadata')->nullable();
            $table->timestamps();
        });
    }

    public function test_boss_debuff_duration_is_halved(): void
    {
        $monster = Monster::create(['name' => 'Boss', 'is_boss' => true]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Слабость', 'slug' => 'weakness', 'type' => 'debuff', 'duration' => 8]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $stacks = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('stacks');
        $this->assertSame(4, $stacks, 'boss debuffs must apply at half duration, not full or zero');
    }

    public function test_non_boss_debuff_duration_is_unaffected(): void
    {
        $monster = Monster::create(['name' => 'Regular', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Слабость', 'slug' => 'weakness', 'type' => 'debuff', 'duration' => 8]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $stacks = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('stacks');
        $this->assertSame(8, $stacks);
    }

    public function test_boss_non_debuff_effect_is_not_halved(): void
    {
        // STUN is a real ActiveEffectType case but not a stat "debuff" per the
        // Effect->type column — halving must be scoped to type === 'debuff' only.
        $monster = Monster::create(['name' => 'Boss', 'is_boss' => true]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Оглушение', 'slug' => 'stun', 'type' => 'neutral', 'duration' => 8]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $stacks = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('stacks');
        $this->assertSame(8, $stacks);
    }

    /**
     * Part 2 addendum (carried-forward fix from Task 5's review): a debuff
     * whose slug has no matching ActiveEffectType case must not be silently
     * dropped, and re-casting it must refresh the SAME row (matched by
     * effect_id since type is null for it) instead of duplicating.
     */
    public function test_unrecognized_debuff_slug_creates_row_and_refreshes_on_recast(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Ослабление брони', 'slug' => 'armor_down', 'type' => 'debuff', 'duration' => 6]);
        $battle = Battle::create();

        $service = app(BattleEffectService::class);

        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $rows = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->get();
        $this->assertCount(1, $rows, 'a debuff-type Effect with no matching ActiveEffectType case must not be silently dropped');
        $this->assertNull($rows->first()->type);
        $this->assertSame($effect->id, $rows->first()->effect_id);
        $this->assertSame(6, $rows->first()->stacks);

        // Re-cast the same spell — must refresh the existing row, not duplicate it.
        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $rowsAfter = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->get();
        $this->assertCount(1, $rowsAfter, 'recasting the same unrecognized debuff must refresh the existing row, not create a duplicate');
    }
}
