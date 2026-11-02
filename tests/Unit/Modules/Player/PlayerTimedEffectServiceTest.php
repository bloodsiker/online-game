<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Player\Domain\DTO\StatSheet;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Player\Domain\Services\PlayerTimedEffectService;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerActiveEffect;
use Carbon\CarbonImmutable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PlayerTimedEffectServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        config()->set('game.player_heartbeat_seconds', 10);
        DB::purge('sqlite');

        Schema::connection('sqlite')->create('players', function (Blueprint $table): void {
            $table->id();
            $table->float('experience_multiplier')->default(1.0);
            $table->unsignedInteger('reputation_rating')->default(0);
            $table->integer('hp_now');
            $table->integer('hp_max');
            $table->integer('mp_now')->default(0);
            $table->integer('mp_max')->default(0);
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('battles', function (Blueprint $table): void {
            $table->id();
            $table->integer('status')->default(1);
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->integer('tick_interval')->default(1);
            $table->string('type')->nullable();
            $table->string('active_type')->nullable();
            $table->string('damage_scaling_type')->nullable();
            $table->float('value_per_tick')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::connection('sqlite')->create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->string('type')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamp('last_tick_at')->nullable();
            $table->timestamp('next_tick_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
            $table->float('tick_remainder')->default(0);
            $table->timestamps();
        });
    }

    public function test_poison_tick_is_applied_only_once_per_ten_seconds(): void
    {
        $startedAt = CarbonImmutable::parse('2026-08-21 10:00:00');
        $player = $this->player(100);
        $battle = Battle::query()->create(['status' => 1]);
        $effect = PlayerActiveEffect::query()->create([
            'player_id' => $player->id,
            'battle_id' => $battle->id,
            'type' => ActiveEffectType::POISON,
            'applied_at' => $startedAt,
            'last_tick_at' => $startedAt,
            'stacks' => 2,
            'current_value' => 7,
        ]);

        $service = app(PlayerTimedEffectService::class);

        $this->assertSame(0, $service->process($player, $startedAt->addSeconds(9))->totalDamage);
        $this->assertSame(100, $player->fresh()->hp_now);

        $firstTick = $service->process($player->refresh(), $startedAt->addSeconds(10));
        $this->assertSame(7, $firstTick->totalDamage);
        $this->assertSame(93, $player->fresh()->hp_now);
        $this->assertSame(1, $effect->fresh()->stacks);

        $this->assertSame(0, $service->process($player->refresh(), $startedAt->addSeconds(10))->totalDamage);
        $this->assertSame(93, $player->fresh()->hp_now);

        $secondTick = $service->process($player->refresh(), $startedAt->addSeconds(20));
        $this->assertSame(7, $secondTick->totalDamage);
        $this->assertSame(86, $player->fresh()->hp_now);
        $this->assertDatabaseMissing('player_active_effects', ['id' => $effect->id]);
    }

    public function test_timed_effect_applies_missed_ticks_only_until_expiration(): void
    {
        $startedAt = CarbonImmutable::parse('2026-08-21 11:00:00');
        $player = $this->player(100);
        $effect = PlayerActiveEffect::query()->create([
            'player_id' => $player->id,
            'type' => ActiveEffectType::BURN,
            'applied_at' => $startedAt,
            'last_tick_at' => $startedAt,
            'expires_at' => $startedAt->addSeconds(25),
            'current_value' => 3,
        ]);

        $result = app(PlayerTimedEffectService::class)->process($player, $startedAt->addSeconds(40));

        $this->assertSame(6, $result->totalDamage);
        $this->assertSame(94, $player->fresh()->hp_now);
        $this->assertDatabaseMissing('player_active_effects', ['id' => $effect->id]);
    }

    public function test_effect_specific_tick_interval_is_respected(): void
    {
        $startedAt = CarbonImmutable::parse('2026-08-21 11:30:00');
        $player = $this->player(100);
        $battle = Battle::query()->create(['status' => 1]);
        $definitionId = DB::table('effects')->insertGetId([
            'name' => 'Ожог',
            'slug' => 'burn',
            'tick_interval' => 2,
            'created_at' => $startedAt,
            'updated_at' => $startedAt,
        ]);
        PlayerActiveEffect::query()->create([
            'player_id' => $player->id,
            'effect_id' => $definitionId,
            'battle_id' => $battle->id,
            'type' => ActiveEffectType::BURN,
            'applied_at' => $startedAt,
            'last_tick_at' => $startedAt,
            'stacks' => 5,
            'current_value' => 2,
        ]);

        $result = app(PlayerTimedEffectService::class)->process($player, $startedAt->addSeconds(10));

        $this->assertSame(10, $result->totalDamage);
        $this->assertSame(90, $player->fresh()->hp_now);
        $this->assertDatabaseCount('player_active_effects', 0);
    }

    public function test_heartbeat_minimum_hp_does_not_revive_dead_player(): void
    {
        $startedAt = CarbonImmutable::parse('2026-08-21 12:00:00');
        $livingPlayer = $this->player(5);
        $deadPlayer = $this->player(0);

        foreach ([$livingPlayer, $deadPlayer] as $player) {
            PlayerActiveEffect::query()->create([
                'player_id' => $player->id,
                'type' => ActiveEffectType::POISON,
                'applied_at' => $startedAt,
                'last_tick_at' => $startedAt,
                'expires_at' => $startedAt->addMinute(),
                'current_value' => 10,
            ]);
        }

        $service = app(PlayerTimedEffectService::class);
        $service->process($livingPlayer, $startedAt->addSeconds(10), minimumHp: 1);
        $service->process($deadPlayer, $startedAt->addSeconds(10), minimumHp: 1);

        $this->assertSame(1, $livingPlayer->fresh()->hp_now);
        $this->assertSame(0, $deadPlayer->fresh()->hp_now);
    }

    public function test_fractional_tick_damage_is_accumulated_without_losing_total_damage(): void
    {
        $startedAt = CarbonImmutable::parse('2026-08-21 13:00:00');
        $player = $this->player(100);
        $definitionId = DB::table('effects')->insertGetId([
            'name' => 'Кровотечение',
            'slug' => 'monster_bleed',
            'tick_interval' => 1,
            'created_at' => $startedAt,
            'updated_at' => $startedAt,
        ]);
        $activeEffect = PlayerActiveEffect::query()->create([
            'player_id' => $player->id,
            'effect_id' => $definitionId,
            'type' => ActiveEffectType::BLEED,
            'applied_at' => $startedAt,
            'last_tick_at' => $startedAt,
            'expires_at' => $startedAt->addSeconds(20),
            'current_value' => 0.35,
            'tick_remainder' => 0,
        ]);

        $service = app(PlayerTimedEffectService::class);
        $firstHalf = $service->process($player, $startedAt->addSeconds(10));

        $this->assertSame(3, $firstHalf->totalDamage);
        $this->assertEqualsWithDelta(0.5, $activeEffect->fresh()->tick_remainder, 0.000001);

        $secondHalf = $service->process($player->refresh(), $startedAt->addSeconds(20));

        $this->assertSame(4, $secondHalf->totalDamage);
        $this->assertSame(93, $player->fresh()->hp_now);
        $this->assertDatabaseMissing('player_active_effects', ['id' => $activeEffect->id]);
    }

    public function test_regeneration_heals_thirty_percent_over_real_time_in_and_out_of_battle(): void
    {
        $this->freezeTime();
        $startedAt = now()->toImmutable();
        $player = $this->player(20);
        // Effective HP can differ from the base HP stored on the player.
        $sheet = new StatSheet;
        $sheet->hpMax = 200;
        $stats = \Mockery::mock(PlayerStatService::class);
        $stats->shouldReceive('resolve')->andReturn($sheet);
        $stats->shouldReceive('invalidate');
        $timer = new PlayerTimedEffectService;
        $combat = new BattleEffectService($stats, $timer);
        $battle = Battle::create(['status' => 1]);
        $effect = $this->regenerationDefinition($startedAt);
        $combat->applyEffectToPlayer($effect, $player, $battle, new AttackResultDTO, 120);

        $active = PlayerActiveEffect::firstOrFail();
        $this->assertNull($active->battle_id);
        $this->assertEqualsWithDelta(0.5, $active->current_value, 0.000001);

        // Ten instant attacks must not accelerate healing.
        for ($i = 0; $i < 10; $i++) {
            $combat->processPlayerEffects($player, $battle, new AttackResultDTO);
        }
        $this->assertSame(20, $player->fresh()->hp_now);
        $this->travelTo($startedAt->addSeconds(60));
        $combat->processPlayerEffects($player, $battle, new AttackResultDTO);
        $this->assertSame(50, $player->fresh()->hp_now);

        // The battle ends; the same effect continues through heartbeat processing.
        $battle->delete();
        $this->travelTo($startedAt->addSeconds(120));
        $result = $timer->process($player->refresh(), now(), hpMax: 200);
        $this->assertSame(30, $result->totalHealing);
        $this->assertSame(80, $player->fresh()->hp_now);
        $this->assertSame(0, $result->totalDamage);
        $this->assertDatabaseMissing('player_active_effects', ['id' => $active->id]);
    }

    public function test_regeneration_catches_up_after_expiration_without_exceeding_its_budget(): void
    {
        $startedAt = CarbonImmutable::parse('2026-10-29 10:00:00');
        $player = $this->player(10);
        $effect = $this->regenerationDefinition($startedAt);
        PlayerActiveEffect::create([
            'player_id' => $player->id,
            'effect_id' => $effect->id,
            'type' => ActiveEffectType::REGEN,
            'applied_at' => $startedAt,
            'last_tick_at' => $startedAt,
            'expires_at' => $startedAt->addSeconds(120),
            'current_value' => 0.25,
        ]);
        $timer = new PlayerTimedEffectService;
        $this->assertSame(0, $timer->process($player, $startedAt->addSeconds(3))->totalHealing);
        $this->assertSame(15, $timer->process($player->refresh(), $startedAt->addSeconds(60))->totalHealing);
        $this->assertSame(15, $timer->process($player->refresh(), $startedAt->addSeconds(300))->totalHealing);
        $this->assertSame(40, $player->fresh()->hp_now);
        $this->assertSame(0, $timer->process($player->refresh(), $startedAt->addSeconds(301))->totalHealing);
    }

    public function test_regeneration_neither_exceeds_maximum_hp_nor_revives_dead_players(): void
    {
        $startedAt = CarbonImmutable::parse('2026-10-29 11:00:00');
        $effect = $this->regenerationDefinition($startedAt);
        foreach ([95 => 100, 0 => 0] as $initial => $expected) {
            $player = $this->player($initial);
            PlayerActiveEffect::create([
                'player_id' => $player->id,
                'effect_id' => $effect->id,
                'type' => ActiveEffectType::REGEN,
                'applied_at' => $startedAt,
                'last_tick_at' => $startedAt,
                'expires_at' => $startedAt->addSeconds(120),
                'current_value' => 0.25,
            ]);
            app(PlayerTimedEffectService::class)->process($player, $startedAt->addSeconds(120));
            $this->assertSame($expected, $player->fresh()->hp_now);
        }
    }

    public function test_migration_configures_only_the_requested_regeneration_skill(): void
    {
        Schema::create('magic_skills', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->integer('cooldown');
            $table->text('description')->nullable();
            $table->timestamps();
        });
        Schema::create('magic_skill_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('magic_skill_id');
            $table->unsignedBigInteger('effect_id');
            $table->integer('duration_seconds');
            $table->integer('chance');
        });
        $effect = $this->regenerationDefinition(now()->toImmutable());
        DB::table('magic_skills')->insert([
            ['id' => 29, 'slug' => 'regen_skill', 'cooldown' => 25],
            ['id' => 30, 'slug' => 'other_skill', 'cooldown' => 99],
        ]);
        DB::table('magic_skill_effects')->insert([
            'magic_skill_id' => 29, 'effect_id' => $effect->id, 'duration_seconds' => 4, 'chance' => 50,
        ]);
        $migration = require database_path('migrations/2026_10_29_000001_make_regeneration_time_based_percent_healing.php');
        $migration->up();
        $migration->up();
        $this->assertSame(150, DB::table('magic_skills')->where('id', 29)->value('cooldown'));
        $this->assertSame(99, DB::table('magic_skills')->where('id', 30)->value('cooldown'));
        $this->assertDatabaseHas('magic_skill_effects', ['magic_skill_id' => 29, 'duration_seconds' => 120, 'chance' => 100]);
        $this->assertDatabaseHas('effects', ['id' => $effect->id, 'value_per_tick' => 30, 'damage_scaling_type' => 'target_max_hp']);
        $description = json_decode(DB::table('magic_skills')->where('id', 29)->value('description'), true);
        $this->assertStringContainsString('30%', $description['ru']);
        $this->assertStringContainsString('30%', $description['uk']);
    }

    private function regenerationDefinition(CarbonImmutable $startedAt): Effect
    {
        $id = DB::table('effects')->insertGetId([
            'name' => 'Регенерация',
            'slug' => 'regen',
            'type' => 'buff',
            'active_type' => 'regen',
            'damage_scaling_type' => 'target_max_hp',
            'tick_interval' => 1,
            'value_per_tick' => 30,
            'created_at' => $startedAt,
            'updated_at' => $startedAt,
        ]);

        return Effect::findOrFail($id);
    }

    private function player(int $hp): Player
    {
        $player = (new Player)->forceFill([
            'hp_now' => $hp,
            'hp_max' => 100,
            'mp_now' => 10,
            'mp_max' => 10,
        ]);
        $player->save();

        return $player;
    }
}
