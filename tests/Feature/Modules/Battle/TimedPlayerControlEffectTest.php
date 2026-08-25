<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TimedPlayerControlEffectTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('players', function (Blueprint $table): void {
            $table->id();
            $table->integer('hp_now')->default(100);
            $table->integer('hp_max')->default(100);
            $table->timestamps();
        });
        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('control');
            $table->string('slug')->default('stun');
            $table->string('type')->default('debuff');
            $table->timestamps();
        });
        Schema::create('battles', function (Blueprint $table): void {
            $table->id();
            $table->tinyInteger('status')->default(1);
            $table->integer('rounds')->default(1);
            $table->timestamps();
        });
        Schema::create('player_active_effects', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('player_id');
            $table->unsignedBigInteger('effect_id')->nullable();
            $table->unsignedBigInteger('battle_id')->nullable();
            $table->unsignedBigInteger('source_player_id')->nullable();
            $table->unsignedBigInteger('source_magic_skill_id')->nullable();
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

    public function test_stun_and_paralysis_on_player_expire_by_time_not_actions(): void
    {
        DB::table('players')->insert(['id' => 1, 'hp_now' => 100, 'hp_max' => 100]);
        $player = Player::findOrFail(1);
        $battle = Battle::create();
        $service = app(BattleEffectService::class);

        foreach ([ActiveEffectType::STUN, ActiveEffectType::PARALYSIS] as $controlType) {
            $applyResult = new AttackResultDTO;
            $service->applyCustomEffectToPlayer(
                $controlType,
                0,
                5,
                $player,
                $battle,
                $applyResult,
            );

            $row = DB::table('player_active_effects')
                ->where('player_id', $player->id)
                ->where('type', $controlType->value)
                ->first();

            $this->assertNotNull($row);
            $this->assertNull($row->battle_id, 'временной контроль не должен зависеть от жизненного цикла боя');
            $this->assertSame(0, (int) $row->stacks);

            $notification = $applyResult->getPlayerEffects()[0] ?? null;
            $this->assertNotNull($notification);
            $this->assertSame('effect_'.$row->id, $notification->id);
            $this->assertSame($controlType->label(), $notification->name);
            $this->assertSame(5, $notification->duration);
            $this->assertTrue($notification->isCurse);

            $this->assertTrue($service->processPlayerEffects($player, $battle, new AttackResultDTO));
            $this->assertTrue(
                $service->processPlayerEffects($player, $battle, new AttackResultDTO),
                'два действия в одну секунду не должны снимать эффект',
            );

            DB::table('player_active_effects')
                ->where('player_id', $player->id)
                ->where('type', $controlType->value)
                ->update(['expires_at' => now()->subSecond()]);

            $this->assertFalse($service->processPlayerEffects($player, $battle, new AttackResultDTO));
            $this->assertDatabaseMissing('player_active_effects', [
                'player_id' => $player->id,
                'type' => $controlType->value,
            ]);
        }
    }
}
