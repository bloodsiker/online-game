<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\DTOs\AttackResultDTO;
use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Effect\Infrastructure\Persistence\Models\Effect;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Регенерация (HoT) раньше умирала после ОДНОГО тика: applyEffectToPlayer()
 * инициализировала stacks=0, а processPlayerEffects() делает stacks-- за
 * каждый боевой раунд — 0-- сразу уходит в «<=0» и эффект удаляется до
 * истечения durationSeconds. Фикс: stacks — это бюджет тиков на весь
 * durationSeconds (intdiv по tick_interval), как у applyEffectToMonster.
 */
class HotTickBudgetTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');

        Schema::create('effects', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->default('regen');
            $table->string('slug')->default('regen');
            $table->string('type')->default('buff');
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
            $table->integer('hp_max')->default(100);
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

    private function regenEffect(): Effect
    {
        return Effect::create([
            'name' => 'Регенерация', 'slug' => 'regen', 'type' => 'buff',
            'active_type' => 'regen', 'tick_interval' => 1, 'value_per_tick' => 15,
        ]);
    }

    public function test_hot_stacks_budget_matches_duration_over_tick_interval(): void
    {
        $player = new Player;
        $player->hp_now = 50;
        $player->hp_max = 100;
        $player->save();

        app(BattleEffectService::class)->applyEffectToPlayer(
            $this->regenEffect(), $player, null, new AttackResultDTO, durationSeconds: 4,
        );

        $stacks = DB::table('player_active_effects')->where('player_id', $player->id)->value('stacks');
        $this->assertSame(4, (int) $stacks, 'duration_seconds=4 / tick_interval=1 must give a 4-tick budget, not 0');
    }

    public function test_non_hot_effect_keeps_zero_stacks(): void
    {
        $player = new Player;
        $player->hp_now = 50;
        $player->hp_max = 100;
        $player->save();
        $debuff = Effect::create(['name' => 'Слабость', 'slug' => 'weakness', 'type' => 'debuff']);

        app(BattleEffectService::class)->applyEffectToPlayer(
            $debuff, $player, null, new AttackResultDTO, durationSeconds: 10,
        );

        $stacks = DB::table('player_active_effects')->where('player_id', $player->id)->value('stacks');
        $this->assertSame(0, (int) $stacks, 'stacks is only meaningful for HoT — must not change for other effect types');
    }

    public function test_regen_heals_across_multiple_battle_rounds_not_just_one(): void
    {
        $player = new Player;
        $player->hp_now = 10;
        $player->hp_max = 1000;
        $player->save();
        $battle = Battle::create();
        $service = app(BattleEffectService::class);

        $service->applyEffectToPlayer($this->regenEffect(), $player, null, new AttackResultDTO, durationSeconds: 4);

        // processPlayerEffects() только мутирует hp_now в памяти — сохранение
        // (как в реальном вызове из AttackService) на вызывающей стороне.
        for ($round = 0; $round < 3; $round++) {
            $freshPlayer = $player->fresh();
            $service->processPlayerEffects($freshPlayer, $battle, new AttackResultDTO);
            $freshPlayer->save();
        }
        $player->refresh();

        $this->assertSame(55, $player->hp_now, '10 + 3 × 15 HP — регенерация должна была тикнуть все 3 раза');
        $this->assertDatabaseHas('player_active_effects', ['player_id' => $player->id, 'stacks' => 1]);
    }
}
