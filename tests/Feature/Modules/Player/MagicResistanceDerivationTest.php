<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Player;

use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MagicResistanceDerivationTest extends TestCase
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
        // PlayerStatServiceOptimizationTest.
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
        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->timestamp('expires_at')->nullable();
        });
    }

    public function test_magic_resistance_derives_from_wisdom_only(): void
    {
        $player = $this->player(['wisdom' => 21.0, 'lvl' => 12]);

        $sheet = app(PlayerStatService::class)->resolve($player);

        // (21 - 1) * MAGIC_RESIST_PER_WIS(1) = 20
        $this->assertSame(20, $sheet->getMagicResistance());
    }

    public function test_magic_attack_stays_zero_without_equipment(): void
    {
        $player = $this->player(['intelligence' => 999.0, 'wisdom' => 999.0, 'lvl' => 12]);

        $sheet = app(PlayerStatService::class)->resolve($player);

        $this->assertSame(0, $sheet->getMagicAttack(), 'magic_attack must stay gear-only — intelligence must not leak into it (see Task 1).');
    }

    /**
     * Player has no $fillable/$guarded override (fully guarded by default,
     * see Player model) — forceFill()+save() instead of create(), same
     * pattern as PlayerStatServiceOptimizationTest::player(). All primary
     * stats are pinned explicitly: an in-memory Eloquent model does not
     * pick up DB column defaults after save() without a refresh, so an
     * unset stat would come back null and blow up floor() in buildSheet().
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
