<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\Services\Battle\BattleService;
use App\Modules\Battle\Application\Services\Battle\PlayerAttackInitiator;
use App\Modules\Battle\Application\Services\Combat\FightOrchestrator;
use App\Modules\Battle\Application\UseCases\RunAway;
use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\Support\BattleTestDoubles;
use Tests\Support\CreatesBattleFixture;
use Tests\TestCase;

class BattleTransactionsTest extends TestCase
{
    use CreatesBattleFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createBattleTables();
        $this->seedBattleFixture();
        BattleTestDoubles::bind();
        Auth::setUser($this->economyUser());
    }

    private function enter(int $userId = 1): int
    {
        Auth::setUser($this->economyUser($userId));

        return app(BattleService::class)->attackMonster(Location::findOrFail(1), 1)->id;
    }

    public function test_both_entry_paths_reuse_battle_and_participant_rows(): void
    {
        $id = $this->enter();
        $this->assertSame($id, app(PlayerAttackInitiator::class)->attack(Location::findOrFail(1), 1)->id);
        $this->assertSame($id, $this->enter(2));
        $this->assertDatabaseCount('battles', 1);
        $this->assertSame(1, DB::table('battle_details')->where('user_id', 1)->count());
        $this->assertSame(1, DB::table('battle_details')->whereNotNull('location_monster_id')->count());
        $this->assertSame(3, DB::table('battle_rounds')->distinct()->count('round_number'));
    }

    public function test_entry_rechecks_current_location_and_dungeon_scope(): void
    {
        DB::table('users')->where('id', 1)->update(['location_id' => 2]);
        $this->assertNull(app(BattleService::class)->attackMonster(Location::findOrFail(1), 1));
        DB::table('users')->where('id', 1)->update(['location_id' => 1]);
        DB::table('monster_on_locations')->update(['dungeon_session_id' => 5]);
        $this->assertNull(app(BattleService::class)->attackMonster(Location::findOrFail(1), 1));
        $this->assertDatabaseCount('battles', 0);
    }

    public function test_failed_escape_has_unique_round_and_does_not_move_or_remove_player(): void
    {
        $id = $this->enter();
        BattleTestDoubles::bind(escape: false);
        $result = app(RunAway::class)->execute($this->economyUser(), $id);
        $this->assertNotNull($result);
        $this->assertSame(2, (int) $result->getBattleRound()->round_number);
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 1]);
        $this->assertDatabaseHas('battle_details', ['battle_id' => $id, 'user_id' => 1, 'status' => 1]);
    }

    public function test_successful_escape_finishes_only_its_battle_and_replay_cannot_move_player_again(): void
    {
        $id = $this->enter();
        DB::table('battles')->insert(['id' => 99, 'location_id' => 2]);
        DB::table('battle_details')->insert(['battle_id' => 99, 'user_id' => 2]);
        $this->assertNull(app(RunAway::class)->execute($this->economyUser(), $id));
        $this->assertDatabaseHas('battles', ['id' => $id, 'status' => 0]);
        $this->assertDatabaseHas('battles', ['id' => 99, 'status' => 1]);
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 2]);
        $this->expectException(HttpException::class);
        app(RunAway::class)->execute($this->economyUser(), $id);
    }

    public function test_outsider_cannot_escape_through_someone_elses_battle(): void
    {
        $id = $this->enter();
        $this->expectException(HttpException::class);
        try {
            app(RunAway::class)->execute($this->economyUser(2), $id);
        } finally {
            $this->assertDatabaseHas('users', ['id' => 2, 'location_id' => 1]);
            $this->assertDatabaseCount('battle_rounds', 1);
        }
    }

    public function test_attack_uses_current_hp_and_records_damage_without_overkill(): void
    {
        $id = $this->enter();
        app(FightOrchestrator::class)->attack($id, 1, 0);
        $this->assertDatabaseHas('monster_on_locations', ['id' => 1, 'hp_now' => 10]);
        app(FightOrchestrator::class)->attack($id, 1, 0);
        $this->assertDatabaseHas('monster_on_locations', ['id' => 1, 'hp_now' => 0, 'active' => 0]);
        $this->assertSame(20, (int) DB::table('battle_monster_damage')->sum('damage'));
        $this->assertDatabaseCount('battle_test_kills', 1);
    }

    public function test_stale_regeneration_does_not_restore_old_hp_or_resurrect_a_dead_spawn(): void
    {
        $this->freezeTime();
        $spawn = MonsterOnLocation::findOrFail(1);
        DB::table('monster_on_locations')->update(['hp_max' => 100, 'hp_now' => 20, 'last_regen_at' => now()->subSeconds(60)]);
        $spawn->regenerate();
        $this->assertSame(25, (int) $spawn->hp_now);
        DB::table('monster_on_locations')->update(['hp_now' => 0, 'active' => 0]);
        $spawn->regenerate();
        $this->assertSame(0, (int) $spawn->hp_now);
    }

    public function test_regeneration_preserves_pending_dot_damage_inside_combat(): void
    {
        $spawn = MonsterOnLocation::findOrFail(1);
        DB::table('monsters')->update(['does_not_regenerate_hp' => true]);
        $spawn->hp_now = 12;
        $spawn->regenerate();
        $this->assertDatabaseHas('monster_on_locations', ['id' => 1, 'hp_now' => 12]);
    }

    public function test_solo_survival_reward_is_claimed_once_even_with_stale_session(): void
    {
        $this->seedDungeonReward();
        $session = DungeonSession::findOrFail(1);
        $location = Location::findOrFail(1);
        $coordinator = app(DungeonCoordinator::class);
        $coordinator->tryAdvanceSurvivalWave($session, $location);
        $coordinator->tryAdvanceSurvivalWave($session, $location);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 103]);
        $this->assertNotNull($session->fresh()->completed_at);
    }

    public function test_followers_use_the_primary_wave_counter_and_only_one_wave_is_spawned(): void
    {
        $this->seedDungeonReward(shared: true);
        DB::table('dungeons')->update(['wave_count' => 3]);
        DB::table('dungeon_sessions')->insert(['id' => 2, 'dungeon_id' => 1, 'user_id' => 2, 'primary_session_id' => 1, 'dungeon_run_id' => 1]);
        DB::table('dungeon_run_participants')->insert(['dungeon_run_id' => 1, 'user_id' => 2, 'dungeon_session_id' => 2]);
        DB::table('location_has_monsters')->insert(['location_id' => 1, 'monster_id' => 1]);
        $coordinator = app(DungeonCoordinator::class);
        $coordinator->tryAdvanceSurvivalWave(DungeonSession::findOrFail(2), Location::findOrFail(1));
        $coordinator->tryAdvanceSurvivalWave(DungeonSession::findOrFail(1), Location::findOrFail(1));
        $this->assertSame([2, 2], DB::table('dungeon_sessions')->orderBy('id')->pluck('current_wave')->map(fn ($value) => (int) $value)->all());
        $this->assertDatabaseCount('monster_on_locations', 1);
    }

    public function test_shared_survival_reward_and_completion_roll_back_when_delivery_fails(): void
    {
        $this->seedDungeonReward(shared: true);
        DB::statement("CREATE TRIGGER fail_reward BEFORE UPDATE OF diamond ON users BEGIN SELECT RAISE(ABORT, 'Delivery failed'); END");
        $this->expectException(QueryException::class);
        try {
            app(DungeonCoordinator::class)->tryAdvanceSurvivalWave(DungeonSession::findOrFail(1), Location::findOrFail(1));
        } finally {
            $this->assertDatabaseHas('dungeon_runs', ['id' => 1, 'status' => 'active']);
            $this->assertDatabaseHas('dungeon_sessions', ['id' => 1, 'completed_at' => null]);
            $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'diamond' => 100]);
        }
    }

    public function test_expiry_rechecks_session_and_closes_active_battle_before_teleport(): void
    {
        $id = $this->enter();
        $this->seedDungeonReward();
        DB::table('monster_on_locations')->insert(['id' => 1, 'monster_id' => 1, 'location_id' => 1, 'dungeon_session_id' => 1]);
        DB::table('dungeon_sessions')->update(['expires_at' => now()->subSecond()]);
        $coordinator = app(DungeonCoordinator::class);
        $this->assertTrue($coordinator->expireSessionIfNeeded($this->economyUser()));
        $this->assertFalse($coordinator->expireSessionIfNeeded($this->economyUser()));
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 2]);
        $this->assertDatabaseHas('battles', ['id' => $id, 'status' => 0]);
        $this->assertDatabaseCount('dungeon_sessions', 0);
    }

    public function test_expiry_does_not_close_unrelated_battle_or_teleport_a_former_participant(): void
    {
        $this->seedDungeonReward(shared: true);
        DB::table('locations')->insert(['id' => 3]);
        DB::table('users')->where('id', 2)->update(['location_id' => 3]);
        DB::table('dungeon_run_participants')->insert(['dungeon_run_id' => 1, 'user_id' => 2, 'dungeon_session_id' => 99]);
        DB::table('battles')->insert(['id' => 99, 'location_id' => 3]);
        DB::table('battle_details')->insert(['battle_id' => 99, 'user_id' => 2]);
        DB::table('dungeon_sessions')->update(['expires_at' => now()->subSecond()]);
        $this->assertTrue(app(DungeonCoordinator::class)->expireSessionIfNeeded($this->economyUser()));
        $this->assertDatabaseHas('users', ['id' => 2, 'location_id' => 3]);
        $this->assertDatabaseHas('battles', ['id' => 99, 'status' => 1]);
    }
}
