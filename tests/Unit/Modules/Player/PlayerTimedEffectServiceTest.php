<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\Battle\Domain\Enums\ActiveEffectType;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
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
            $table->timestamp('expires_at')->nullable();
            $table->integer('stacks')->default(0);
            $table->float('current_value')->nullable();
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
