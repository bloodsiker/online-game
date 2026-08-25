<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Task 9: большой отрицательный модификатор от дебаффа не должен уводить
 * итоговый производный стат (броню и т.п.) в минус — applyModifiers()
 * должен клэмпить каждый посчитанный стат на 0.
 */
class PlayerStatServiceDebuffClampTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

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

        // PlayerStatService::resolve() touches equipment/skills/buffs/effects
        // tables even with no rows — minimal empty schemas, same pattern as
        // MagicResistanceDerivationTest (Task 2).
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
        Schema::create('share_items', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
        });
        Schema::create('share_item_stats', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('share_item_id');
            $table->string('stat_type');
            $table->float('value');
            $table->string('value_type');
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_passive')->default(false);
        });
        Schema::create('player_magic_skills', function (Blueprint $table): void {
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('magic_skill_id');
            $table->timestamp('cooldown_end_at')->nullable();
            $table->boolean('is_equipped')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
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

        // fromActiveEffects() reads player_active_effects joined with effects
        // (Effect->stat_modifiers) — this is the real "inject an extra debuff
        // modifier" mechanism, see PlayerStatService::fromActiveEffects().
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->string('slug')->default('test');
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
    }

    public function test_negative_flat_modifier_cannot_push_armor_below_zero(): void
    {
        $player = $this->player(['strength' => 5.0, 'lvl' => 12]); // armor base = (5-1)*1 = 4

        $effect = Effect::create([
            'name' => 'Разрушение брони',
            'slug' => 'armor_shatter',
            'type' => 'debuff',
            'stat_modifiers' => [
                ['stat' => 'armor', 'value' => -9999, 'is_percent' => false],
            ],
        ]);

        PlayerActiveEffect::create([
            'player_id' => $player->id,
            'effect_id' => $effect->id,
            'applied_at' => now(),
            'expires_at' => null, // battle-scoped/permanent-until-cleared, still active
            'stacks' => 8,
        ]);

        $sheet = app(PlayerStatService::class)->resolve($player);

        $this->assertSame(0, $sheet->getArmor(), 'a large negative modifier must clamp at 0, not go negative');
    }

    public function test_without_debuff_armor_is_unaffected(): void
    {
        $player = $this->player(['strength' => 5.0, 'lvl' => 12]);

        $sheet = app(PlayerStatService::class)->resolve($player);

        $this->assertSame(4, $sheet->getArmor());
    }

    /**
     * Player has no $fillable/$guarded override (fully guarded by default,
     * see Player model) — forceFill()+save() instead of create(), same
     * pattern as MagicResistanceDerivationTest::player() (Task 2).
     */
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
