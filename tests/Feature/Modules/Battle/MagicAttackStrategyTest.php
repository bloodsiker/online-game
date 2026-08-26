<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\Services\Combat\MagicHitCalculator;
use App\Modules\Battle\Application\Services\Combat\Strategies\MagicAttackStrategy;
use App\Modules\MagicSkill\Application\Services\MagicCastGuard;
use App\Modules\MagicSkill\Infrastructure\Persistence\Models\MagicSkill;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\StubCombatant;
use Tests\TestCase;

class MagicAttackStrategyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->integer('mp_now')->default(10);
            // Player::$attributes defaults experience_multiplier to 1.0 on every insert.
            $table->float('experience_multiplier')->default(1.0);
            $table->timestamps();
        });
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('test');
            $table->string('type')->default('attack');
            $table->string('target_type')->default('enemy');
            $table->boolean('is_passive')->default(false);
            $table->integer('mana_cost')->default(0);
            $table->integer('min_damage')->default(0);
            $table->integer('max_damage')->default(0);
            $table->float('power_coefficient')->default(0);
            $table->integer('cooldown')->default(0);
            // MagicSkill::$attributes defaults level/base_healing/is_passive on every insert.
            $table->unsignedInteger('level')->default(1);
            $table->integer('base_healing')->default(0);
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
    }

    public function test_insufficient_mana_returns_cant_cast_without_deducting(): void
    {
        // Player has no $fillable/$guarded override, so it's totally guarded by default —
        // forceCreate() bypasses mass-assignment protection for this test-only construction.
        $player = Player::forceCreate(['mp_now' => 2]);
        $skill = MagicSkill::create([
            'type' => 'attack', 'is_passive' => false, 'target_type' => 'enemy',
            'mana_cost' => 8, 'min_damage' => 4, 'max_damage' => 7, 'power_coefficient' => 0.3,
            'cooldown' => 0,
        ]);
        $skill->setRelation('skillEffects', collect());
        DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $skill->id]);

        $strategy = new MagicAttackStrategy(
            magicHitCalc: new MagicHitCalculator,
            castGuard: app(MagicCastGuard::class),
            player: new StubCombatant,
            playerModel: $player,
            monster: new StubCombatant,
            magicSkill: $skill,
        );

        $hits = $strategy->getHits();

        $this->assertTrue($hits[0]->isCantCast());
        $this->assertSame(2, $player->mp_now, 'mana must not be touched when the cast fails');
    }

    public function test_successful_cast_deducts_mana_and_deals_damage(): void
    {
        $player = Player::forceCreate(['mp_now' => 10]);
        $skill = MagicSkill::create([
            'type' => 'attack', 'is_passive' => false, 'target_type' => 'enemy',
            'mana_cost' => 8, 'min_damage' => 5, 'max_damage' => 5, 'power_coefficient' => 0.0,
            'cooldown' => 0,
        ]);
        $skill->setRelation('skillEffects', collect());
        DB::table('player_magic_skills')->insert(['player_id' => $player->id, 'magic_skill_id' => $skill->id]);

        $strategy = new MagicAttackStrategy(
            magicHitCalc: new MagicHitCalculator,
            castGuard: app(MagicCastGuard::class),
            player: new StubCombatant,
            playerModel: $player,
            monster: new StubCombatant,
            magicSkill: $skill,
        );

        $hits = $strategy->getHits();

        $this->assertSame(5, $hits[0]->getDamage());
        $this->assertSame(2, $player->mp_now);
        $this->assertFalse($hits[0]->isDodge());
        $this->assertFalse($hits[0]->isCritical());
    }
}
