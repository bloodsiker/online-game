<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Battle;

use App\Modules\Battle\Application\Services\Battle\BattleService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\BattleTestDoubles;
use Tests\Support\CreatesBattleFixture;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class BattleMysqlConcurrencyTest extends TestCase
{
    use CreatesBattleFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('BATTLE_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires isolated codex_battle_race_* MySQL database.');
        }
        $this->assertMatchesRegularExpression('/^codex_battle_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        if (! Schema::hasTable('users')) {
            $this->createBattleTables();
        }
        foreach ([
            'battle_entry_locks', 'battle_test_kills', 'battle_round_hits', 'battle_monster_damage', 'battle_rounds', 'battle_details', 'battles',
            'monster_effects', 'effects', 'monster_on_locations', 'location_has_monsters', 'monsters', 'gathering_attempts',
            'dungeon_rewards', 'dungeon_run_participants', 'dungeon_sessions', 'dungeon_runs', 'dungeon_stages', 'dungeons',
            'item_on_locations', 'item_gems', 'item_runes', 'share_item_instant_rewards',
            'shop_purchase_logs', 'item_action_logs', 'clan_tax_payments', 'clan_treasury_logs',
            'clan_warehouse_logs', 'clan_warehouses', 'clan_logs', 'clan_join_requests', 'clan_members',
            'clan_roles', 'clans', 'shop_carts', 'shop_item_requirements', 'shop_items', 'backpacks',
            'items', 'share_recipes', 'share_items', 'structures', 'locations', 'users', 'players',
        ] as $table) {
            DB::table($table)->delete();
        }
        $this->seedBattleFixture();
        BattleTestDoubles::bind();
        Auth::setUser($this->economyUser());
    }

    private function race(array $first, ?array $second = null, ?callable $holdLocks = null): array
    {
        return $this->runConcurrentRequests('tests/Support/battle-race-worker.php', $this->database, 'BATTLE_RACE_TEST_DATABASE', $first, $second, $holdLocks);
    }

    private function enter(): int
    {
        return app(BattleService::class)->attackMonster(Location::findOrFail(1), 1)->id;
    }

    public function test_parallel_entry_creates_one_battle_and_one_monster_detail(): void
    {
        $this->race(['entry', 1, 0, ''], ['entry', 2, 0, '']);
        $this->assertDatabaseCount('battles', 1);
        $this->assertSame(2, DB::table('battle_details')->whereNotNull('user_id')->count());
        $this->assertSame(1, DB::table('battle_details')->whereNotNull('location_monster_id')->count());
        $this->assertSame(2, DB::table('battle_rounds')->distinct()->count('round_number'));
    }

    public function test_automatic_and_manual_entry_share_the_creation_guard(): void
    {
        DB::table('monsters')->update(['aggression' => 101]);
        $this->race(['auto', 1, 0, ''], ['entry', 2, 0, '']);
        $this->assertDatabaseCount('battles', 1);
        $this->assertSame(1, DB::table('battle_details')->whereNotNull('location_monster_id')->count());
    }

    public function test_parallel_hits_do_not_lose_damage_or_duplicate_the_kill(): void
    {
        $id = $this->enter();
        Auth::setUser($this->economyUser(2));
        app(BattleService::class)->attackMonster(Location::findOrFail(1), 1);
        $this->race(['hit', 1, $id, ''], ['hit', 2, $id, '']);
        $this->assertDatabaseHas('monster_on_locations', ['id' => 1, 'hp_now' => 0, 'active' => 0]);
        $this->assertSame(20, (int) DB::table('battle_monster_damage')->sum('damage'));
        $this->assertDatabaseCount('battle_test_kills', 1);
    }

    public function test_legacy_separate_battles_still_lock_the_same_spawn(): void
    {
        $first = $this->enter();
        DB::table('battles')->insert(['id' => 99, 'location_id' => 1]);
        DB::table('battle_details')->insert([
            ['battle_id' => 99, 'user_id' => 2, 'location_monster_id' => null],
            ['battle_id' => 99, 'user_id' => null, 'location_monster_id' => 1],
        ]);
        DB::table('monster_on_locations')->update(['hp_now' => 10]);
        $this->race(['hit', 1, $first, ''], ['hit', 2, 99, '']);
        $this->assertDatabaseCount('battle_test_kills', 1);
        $this->assertSame(10, (int) DB::table('battle_monster_damage')->sum('damage'));
        $this->assertDatabaseHas('monster_on_locations', ['hp_now' => 0]);
    }

    public function test_escape_and_hit_are_serialized_and_do_not_reuse_round_numbers(): void
    {
        $id = $this->enter();
        $this->race(['escape', 1, $id, ''], ['hit', 1, $id, '']);
        $this->assertDatabaseHas('users', ['id' => 1, 'location_id' => 2]);
        $this->assertDatabaseHas('battles', ['id' => $id, 'status' => 0]);
        $this->assertSame(DB::table('battle_rounds')->count(), DB::table('battle_rounds')->distinct()->count('round_number'));
    }

    public function test_regeneration_waits_for_damage_and_uses_the_latest_hp(): void
    {
        $id = $this->enter();
        DB::table('monster_on_locations')->update(['hp_max' => 100, 'hp_now' => 20, 'last_regen_at' => now()->subSeconds(60)]);
        $this->race(['hit', 1, $id, ''], ['regen', 2, 0, ''], holdLocks: function (): void {
            DB::table('monster_on_locations')->where('id', 1)->lockForUpdate()->first();
        });
        $this->assertDatabaseHas('monster_on_locations', ['hp_now' => 15]);
    }

    public function test_two_solo_completions_pay_once(): void
    {
        $this->seedDungeonReward();
        $this->race(['wave', 1, 0, '']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 103]);
        $this->assertNotNull(DB::table('dungeon_sessions')->value('completed_at'));
    }

    public function test_two_shared_completions_pay_once(): void
    {
        $this->seedDungeonReward(shared: true);
        $this->race(['wave', 1, 0, '']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150, 'diamond' => 103]);
        $this->assertDatabaseHas('dungeon_runs', ['id' => 1, 'status' => 'completed']);
    }

    public function test_followers_cannot_advance_two_waves_from_one_clear(): void
    {
        $this->seedDungeonReward(shared: true);
        DB::table('dungeons')->update(['wave_count' => 3]);
        DB::table('dungeon_sessions')->insert(['id' => 2, 'dungeon_id' => 1, 'user_id' => 2, 'primary_session_id' => 1, 'dungeon_run_id' => 1]);
        DB::table('dungeon_run_participants')->insert(['dungeon_run_id' => 1, 'user_id' => 2, 'dungeon_session_id' => 2]);
        DB::table('location_has_monsters')->insert(['location_id' => 1, 'monster_id' => 1]);
        $this->race(['wave', 1, 0, 1], ['wave', 2, 0, 2]);
        $this->assertSame([2, 2], DB::table('dungeon_sessions')->orderBy('id')->pluck('current_wave')->map(fn ($value) => (int) $value)->all());
        $this->assertDatabaseCount('monster_on_locations', 1);
    }

    public function test_movement_and_battle_entry_cannot_leave_a_living_participant_on_another_location(): void
    {
        $this->race(['entry', 1, 0, ''], ['move', 1, 0, '']);
        $locationId = (int) DB::table('users')->where('id', 1)->value('location_id');
        if ($locationId === 1) {
            $this->assertDatabaseHas('battle_details', ['user_id' => 1, 'status' => 1]);
        } else {
            $this->assertSame(2, $locationId);
            $this->assertDatabaseCount('battle_details', 0);
        }
    }

    public function test_expiry_and_completion_cannot_both_pay_and_fail_the_same_run(): void
    {
        $this->seedDungeonReward(shared: true);
        DB::table('dungeon_sessions')->update(['expires_at' => now()->subSecond()]);
        $this->race(['wave', 1, 0, ''], ['expire', 1, 0, '']);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100, 'location_id' => 2]);
        $this->assertDatabaseHas('dungeon_runs', ['id' => 1, 'status' => 'failed']);
        $this->assertDatabaseCount('dungeon_sessions', 0);
    }
}
