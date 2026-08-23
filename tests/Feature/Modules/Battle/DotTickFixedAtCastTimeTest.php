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
 * Task 10: DoT tick value is computed ONCE at cast time (via MagicHitCalculator
 * inside MagicAttackStrategy) and stored as an override on MonsterActiveEffect,
 * instead of being re-derived from Effect::value_per_tick on every tick.
 */
class DotTickFixedAtCastTimeTest extends TestCase
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

    public function test_tick_value_override_is_stored_verbatim_not_effects_static_value(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create([
            'name' => 'Ожог', 'slug' => 'burn', 'type' => 'debuff', 'duration' => 6,
            'value_per_tick' => 999, // deliberately wrong — must NOT be what gets stored
        ]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster(
            $effect, $locMonster, $battle, new AttackResultDTO, tickValueOverride: 23,
        );

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(23.0, (float) $stored);
    }

    public function test_recast_refreshes_the_tick_value_to_the_new_computation(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Ожог', 'slug' => 'burn', 'type' => 'debuff', 'duration' => 6]);
        $battle = Battle::create();
        $service = app(BattleEffectService::class);

        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, tickValueOverride: 10);
        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, tickValueOverride: 40);

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(40.0, (float) $stored, 'recasting must refresh the tick value, not keep the first cast\'s number');
        $this->assertSame(1, DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->count(), 'must refresh the existing row, not create a second one');
    }

    public function test_no_override_falls_back_to_effect_static_value_unchanged(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Регенерация', 'slug' => 'regen', 'type' => 'buff', 'duration' => 4, 'value_per_tick' => 15]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO);

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(15.0, (float) $stored, 'existing non-magic callers (no override passed) must keep working exactly as before');
    }
}
