<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\Battle\Application\Services\Combat\BattleEffectService;
use App\Modules\MagicSkill\Application\UseCases\UseMagicSkill;
use App\Modules\Player\Application\UseCases\RecalculatePlayerHp;
use Illuminate\Support\Facades\DB;
use Mockery;
use RuntimeException;
use Tests\Support\CreatesMagicFixture;
use Tests\Support\MagicStateTestDoubles;
use Tests\TestCase;

class MagicStateTransactionsTest extends TestCase
{
    use CreatesMagicFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createMagicTables();
        $this->seedMagicFixture();
        MagicStateTestDoubles::bind();
    }

    public function test_explicit_self_target_is_the_same_locked_player_and_healing_is_persisted(): void
    {
        $user = $this->economyUser();
        $user->load('player');
        DB::table('players')->where('id', 1)->update(['hp_now' => 30, 'mp_now' => 70]);
        $result = app(UseMagicSkill::class)->execute($user, skillId: 1, targetPlayerId: 1);
        $this->assertSame('success', $result->status);
        $this->assertSame(['current' => 50, 'max' => 100], $result->hp);
        $this->assertDatabaseHas('players', ['id' => 1, 'hp_now' => 50, 'mp_now' => 60]);
    }

    public function test_heal_uses_current_target_hp_without_changing_the_targets_mana(): void
    {
        $result = app(UseMagicSkill::class)->execute($this->economyUser(), skillId: 1, targetPlayerId: 2);
        $this->assertSame('success', $result->status);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 70, 'mp_now' => 100]);
        $this->assertDatabaseHas('players', ['id' => 1, 'hp_now' => 50, 'mp_now' => 90]);
        $this->assertNotNull(DB::table('player_magic_skills')->where('player_id', 1)->where('magic_skill_id', 1)->value('cooldown_end_at'));
    }

    public function test_location_and_online_rechecks_do_not_charge_a_stale_caster(): void
    {
        $user = $this->economyUser();
        $user->load('player.user');
        DB::table('users')->where('id', 2)->update(['location_id' => 2]);
        $this->assertSame(404, app(UseMagicSkill::class)->execute($user, skillId: 1, targetPlayerId: 2)->httpCode);
        DB::table('users')->where('id', 2)->update(['location_id' => 1, 'last_online_at' => now()->subDay()]);
        $this->assertSame(404, app(UseMagicSkill::class)->execute($user, skillId: 1, targetPlayerId: 2)->httpCode);
        DB::table('users')->where('id', 2)->update(['last_online_at' => now()]);
        DB::table('users')->where('id', 1)->update(['location_id' => 2]);
        $this->assertSame(404, app(UseMagicSkill::class)->execute($user, skillId: 1, targetPlayerId: 2)->httpCode);
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 100]);
        $this->assertNull(DB::table('player_magic_skills')->where('player_id', 1)->where('magic_skill_id', 1)->value('cooldown_end_at'));
    }

    public function test_repeated_cast_is_rejected_without_a_second_heal_or_mana_charge(): void
    {
        $user = $this->economyUser();
        $this->assertSame('success', app(UseMagicSkill::class)->execute($user, skillId: 1, targetPlayerId: 2)->status);
        $this->assertSame('error', app(UseMagicSkill::class)->execute($user, skillId: 1, targetPlayerId: 2)->status);
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 90]);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 70]);
    }

    public function test_buff_refreshes_one_effect_and_returns_its_notification(): void
    {
        DB::table('magic_skills')->where('id', 2)->update(['cooldown' => 0]);
        $user = $this->economyUser();
        $first = app(UseMagicSkill::class)->execute($user, skillId: 2, targetPlayerId: null);
        $second = app(UseMagicSkill::class)->execute($user, skillId: 2, targetPlayerId: null);
        $this->assertSame('success', $first->status);
        $this->assertSame('success', $second->status);
        $this->assertDatabaseCount('player_active_effects', 1);
        $this->assertNotEmpty($second->blessings);
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 80]);
    }

    public function test_effect_failure_rolls_back_healing_mana_cooldown_and_effect_rows(): void
    {
        DB::table('magic_skill_effects')->insert(['magic_skill_id' => 1, 'effect_id' => 1]);
        $effects = Mockery::mock(BattleEffectService::class);
        $effects->shouldReceive('applyEffectToPlayer')->andReturnUsing(function (): void {
            DB::table('player_active_effects')->insert(['player_id' => 2, 'effect_id' => 1]);
            throw new RuntimeException('Effect delivery failed');
        });
        app()->instance(BattleEffectService::class, $effects);
        try {
            app(UseMagicSkill::class)->execute($this->economyUser(), skillId: 1, targetPlayerId: 2);
            $this->fail('The failed cast must not commit.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Effect delivery failed', $exception->getMessage());
        }
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 100]);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 50]);
        $this->assertDatabaseCount('player_active_effects', 0);
        $this->assertNull(DB::table('player_magic_skills')->where('player_id', 1)->where('magic_skill_id', 1)->value('cooldown_end_at'));
    }

    public function test_recalculation_preserves_fractional_health_and_restarts_the_baseline(): void
    {
        DB::table('players')->where('id', 1)->update(['hp_now' => 40, 'hp_max' => 100, 'lvl' => 2, 'endurance' => 3]);
        $result = app(RecalculatePlayerHp::class)->execute(playerId: 1);
        $this->assertSame(28, $result->newHpMax);
        $this->assertSame(11, $result->newHpNow);
        $this->assertDatabaseHas('players', ['id' => 1, 'hp_now' => 11, 'hp_max' => 28, 'regen_hp_start' => 11, 'regen_mp_start' => 100]);
    }

    public function test_recalculation_does_not_revive_dead_or_round_a_living_player_to_zero(): void
    {
        DB::table('players')->where('id', 1)->update(['hp_now' => 0, 'hp_max' => 0]);
        DB::table('players')->where('id', 2)->update(['hp_now' => 1, 'hp_max' => 1000]);
        app(RecalculatePlayerHp::class)->execute(playerId: 1);
        app(RecalculatePlayerHp::class)->execute(playerId: 2);
        $this->assertDatabaseHas('players', ['id' => 1, 'hp_now' => 0]);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 1]);
    }

    public function test_recalculation_dry_run_and_missing_player_do_not_write_anything(): void
    {
        $before = DB::table('players')->orderBy('id')->get()->toJson();
        $this->artisan('player:recalc-hp', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame($before, DB::table('players')->orderBy('id')->get()->toJson());
        $this->assertNull(app(RecalculatePlayerHp::class)->execute(playerId: 999));
    }

    public function test_an_outer_rollback_restores_the_entire_successful_cast(): void
    {
        DB::beginTransaction();
        $result = app(UseMagicSkill::class)->execute($this->economyUser(), skillId: 2, targetPlayerId: 2);
        $this->assertSame('success', $result->status);
        DB::rollBack();
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 100]);
        $this->assertDatabaseCount('player_active_effects', 0);
        $this->assertNull(DB::table('player_magic_skills')->where('player_id', 1)->where('magic_skill_id', 2)->value('cooldown_end_at'));
    }
}
