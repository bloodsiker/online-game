<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Monster\Infrastructure\Persistence\Models\Monster;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
            $table->string('active_type')->nullable();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
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
            $table->float('tick_remainder')->default(0);
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
        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->integer('hp_now')->default(100);
            $table->decimal('experience_multiplier', 6, 3)->default(1.0);
            $table->timestamps();
        });
        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->float('tick_remainder')->default(0);
            $table->timestamps();
        });
    }

    public function test_tick_value_override_is_stored_verbatim_not_effects_static_value(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create([
            'name' => 'Ожог', 'slug' => 'burn', 'type' => 'debuff',
            'value_per_tick' => 999, // deliberately wrong — must NOT be what gets stored
        ]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster(
            $effect, $locMonster, $battle, new AttackResultDTO, durationSeconds: 6, tickValueOverride: 23,
        );

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(23.0, (float) $stored);
    }

    public function test_recast_refreshes_the_tick_value_to_the_new_computation(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Ожог', 'slug' => 'burn', 'type' => 'debuff']);
        $battle = Battle::create();
        $service = app(BattleEffectService::class);

        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, durationSeconds: 6, tickValueOverride: 10);
        $service->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, durationSeconds: 6, tickValueOverride: 40);

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(40.0, (float) $stored, 'recasting must refresh the tick value, not keep the first cast\'s number');
        $this->assertSame(1, DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->count(), 'must refresh the existing row, not create a second one');
    }

    public function test_no_override_falls_back_to_effect_static_value_unchanged(): void
    {
        $monster = Monster::create(['name' => 'Target', 'is_boss' => false]);
        $locMonster = MonsterOnLocation::create(['monster_id' => $monster->id]);
        $effect = Effect::create(['name' => 'Регенерация', 'slug' => 'regen', 'type' => 'buff', 'value_per_tick' => 15]);
        $battle = Battle::create();

        app(BattleEffectService::class)->applyEffectToMonster($effect, $locMonster, $battle, new AttackResultDTO, durationSeconds: 4);

        $stored = DB::table('monster_active_effects')->where('location_monster_id', $locMonster->id)->value('current_value');
        $this->assertSame(15.0, (float) $stored, 'existing non-magic callers (no override passed) must keep working exactly as before');
    }

    public function test_monster_dot_override_is_stored_on_player_effect(): void
    {
        $player = new Player;
        $player->hp_now = 100;
        $player->save();
        $effect = Effect::create([
            'name' => 'Кровотечение от моба',
            'slug' => 'monster_bleed',
            'type' => 'debuff',
            'active_type' => 'bleed',
            'description' => 'Наносит периодический урон.',
            'image' => 'effects/monster-bleed.png',
            'value_per_tick' => 999,
        ]);

        $result = new AttackResultDTO;

        app(BattleEffectService::class)->applyEffectToPlayer(
            $effect,
            $player,
            null,
            $result,
            durationSeconds: 3,
            tickValueOverride: 7,
        );

        $stored = DB::table('player_active_effects')->where('player_id', $player->id)->first();

        $this->assertSame('bleed', $stored->type);
        $this->assertSame(7.0, (float) $stored->current_value);

        $notification = $result->getPlayerEffects()[0] ?? null;
        $this->assertNotNull($notification);
        $this->assertSame('monster_bleed_'.$stored->id, $notification->id);
        $this->assertSame('Кровотечение от моба', $notification->name);
        $this->assertSame(3, $notification->duration);
        $this->assertTrue($notification->isCurse);
        $this->assertSame(Storage::disk('public')->url('effects/monster-bleed.png'), $notification->image);
        $this->assertSame('Наносит периодический урон.', $notification->description);
    }

    public function test_same_runtime_dot_type_refreshes_instead_of_stacking(): void
    {
        $player = new Player;
        $player->hp_now = 100;
        $player->save();
        $first = Effect::create([
            'name' => 'Кровотечение заклинания', 'slug' => 'spell_bleed', 'type' => 'debuff',
            'active_type' => 'bleed',
        ]);
        $second = Effect::create([
            'name' => 'Кровотечение зверя', 'slug' => 'monster_bleed', 'type' => 'debuff',
            'active_type' => 'bleed',
        ]);
        $service = app(BattleEffectService::class);
        $firstResult = new AttackResultDTO;
        $secondResult = new AttackResultDTO;

        $service->applyEffectToPlayer($first, $player, null, $firstResult, durationSeconds: 5, tickValueOverride: 3);
        DB::table('player_active_effects')->where('player_id', $player->id)->update([
            'applied_at' => now()->subMinute(),
            'tick_remainder' => 0.75,
        ]);
        $service->applyEffectToPlayer($second, $player, null, $secondResult, durationSeconds: 3, tickValueOverride: 8);

        $effects = DB::table('player_active_effects')->where('player_id', $player->id)->get();

        $this->assertCount(1, $effects);
        $this->assertSame($second->id, (int) $effects->first()->effect_id);
        $this->assertSame(8.0, (float) $effects->first()->current_value);
        $this->assertSame(0.0, (float) $effects->first()->tick_remainder);
        $this->assertTrue(now()->subSecond()->lte($effects->first()->applied_at));
        $this->assertSame(
            'monster_bleed_'.$effects->first()->id,
            $secondResult->getPlayerEffects()[0]->id,
            'повторное наложение должно обновлять тот же элемент и таймер в character-frame',
        );
        $this->assertSame(3, $secondResult->getPlayerEffects()[0]->duration);
    }
}
