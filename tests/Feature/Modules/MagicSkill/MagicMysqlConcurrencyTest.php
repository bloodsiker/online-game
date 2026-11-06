<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MagicSkill;

use App\Modules\Battle\Application\Services\Battle\BattleService;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesMagicFixture;
use Tests\Support\MagicStateTestDoubles;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class MagicMysqlConcurrencyTest extends TestCase
{
    use CreatesMagicFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('MAGIC_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires an explicitly created isolated MySQL database named codex_magic_race_*.');
        }
        $this->assertMatchesRegularExpression('/^codex_magic_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        Carbon::setTestNow('2026-11-03 12:00:20');
        if (! Schema::hasTable('users')) {
            $this->createMagicTables();
        }
        foreach (['player_active_effects', 'magic_skill_effects', 'player_magic_skills', 'magic_skills', 'player_skills', 'skills',
            'battle_monster_damage', 'battle_round_hits', 'battle_rounds', 'battle_details', 'battles', 'battle_entry_locks', 'battle_test_kills',
            'monster_effects', 'monster_on_locations', 'location_has_monsters', 'monsters', 'effects', 'gathering_attempts',
            'dungeon_run_participants', 'dungeon_sessions', 'dungeon_stages', 'dungeon_runs', 'dungeon_rewards', 'dungeons',
            'shop_purchase_logs', 'item_action_logs', 'clan_tax_payments', 'clan_treasury_logs', 'clan_warehouse_logs', 'clan_warehouses',
            'clan_logs', 'clan_join_requests', 'clan_members', 'clan_roles', 'clans', 'shop_carts', 'shop_item_requirements', 'shop_items',
            'item_on_locations', 'item_gems', 'item_runes', 'backpacks', 'items', 'share_item_instant_rewards', 'share_recipes', 'share_items',
            'structures', 'locations', 'users', 'players'] as $table) {
            DB::table($table)->delete();
        }
        $this->seedMagicFixture();
        DB::table('players')->insert(['id' => 3, 'user_id' => 3, 'hp_now' => 50, 'hp_max' => 100, 'mp_now' => 100,
            'last_regen_at' => now(), 'regen_hp_start' => 50, 'regen_mp_start' => 100]);
        DB::table('users')->where('id', 3)->update(['player_id' => 3, 'last_online_at' => now()]);
        MagicStateTestDoubles::bind();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function race(array $first, ?array $second = null, ?callable $holdLocks = null): array
    {
        return $this->runConcurrentRequests('tests/Support/magic-race-worker.php', $this->database, 'MAGIC_RACE_TEST_DATABASE', $first, $second, $holdLocks);
    }

    private function successes(array $results): int
    {
        return count(array_filter($results, fn ($result) => $result['ok']));
    }

    public function test_parallel_same_spell_has_one_cooldown_one_mana_charge_and_one_heal(): void
    {
        $this->assertSame(1, $this->successes($this->race(['cast', 1, 1, 2])));
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 90]);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 70]);
    }

    public function test_different_spells_cannot_overdraw_one_mana_pool(): void
    {
        DB::table('players')->where('id', 1)->update(['mp_now' => 15]);
        $this->assertSame(1, $this->successes($this->race(['cast', 1, 1, 2], ['cast', 1, 2, 2])));
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 5]);
    }

    public function test_two_healers_do_not_overwrite_each_others_target_hp(): void
    {
        $this->assertSame(2, $this->successes($this->race(['cast', 1, 1, 3], ['cast', 2, 1, 3])));
        $this->assertDatabaseHas('players', ['id' => 3, 'hp_now' => 90]);
        $this->assertSame(180, (int) DB::table('players')->whereIn('id', [1, 2])->sum('mp_now'));
    }

    public function test_mutual_casts_use_the_same_user_and_player_lock_order(): void
    {
        $this->assertSame(2, $this->successes($this->race(['cast', 1, 1, 2], ['cast', 2, 1, 1])));
        foreach ([1, 2] as $id) {
            $this->assertDatabaseHas('players', ['id' => $id, 'hp_now' => 70, 'mp_now' => 90]);
        }
    }

    public function test_parallel_buffs_refresh_one_target_effect_instead_of_creating_duplicates(): void
    {
        $this->assertSame(2, $this->successes($this->race(['cast', 1, 2, 3], ['cast', 2, 2, 3])));
        $this->assertDatabaseCount('player_active_effects', 1);
        $this->assertDatabaseHas('player_active_effects', ['player_id' => 3, 'effect_id' => 1]);
    }

    public function test_healing_and_a_real_combat_round_preserve_both_heal_and_damage(): void
    {
        Auth::setUser($this->economyUser(2));
        DB::table('monster_on_locations')->where('id', 1)->update(['hp_now' => 100, 'hp_max' => 100]);
        $battle = app(BattleService::class)->attackMonster(Location::findOrFail(1), 1);
        $this->assertSame(2, $this->successes($this->race(['cast', 1, 1, 2], ['fight', 2, $battle->id, ''])));
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 60, 'mp_now' => 100]);
        $this->assertDatabaseHas('monster_on_locations', ['id' => 1, 'hp_now' => 90]);
    }

    public function test_healing_and_heartbeat_apply_the_poison_tick_once(): void
    {
        DB::table('effects')->where('id', 1)->update(['active_type' => 'poison']);
        DB::table('player_active_effects')->insert(['player_id' => 2, 'effect_id' => 1, 'type' => 'poison', 'current_value' => 5,
            'applied_at' => now()->subSeconds(10), 'last_tick_at' => now()->subSeconds(10), 'expires_at' => now()->addMinute()]);
        $this->assertSame(2, $this->successes($this->race(['cast', 1, 1, 2], ['heartbeat', 2, '', ''])));
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 65]);
        $this->assertSame(now()->toDateTimeString(), DB::table('player_active_effects')->value('last_tick_at'));
    }

    public function test_recalculation_and_healing_are_serialized_without_resetting_spent_mana(): void
    {
        $this->assertSame(2, $this->successes($this->race(['cast', 1, 1, 2], ['recalculate', 1, '', ''])));
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 90]);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_max' => 10]);
        $this->assertContains((int) DB::table('players')->where('id', 2)->value('hp_now'), [7, 10]);
    }

    public function test_command_reloads_hp_after_waiting_for_a_concurrent_update(): void
    {
        $this->assertSame(2, $this->successes($this->race(['recalculate', 1, '', ''], holdLocks: function (): void {
            DB::table('users')->orderBy('id')->lockForUpdate()->get();
            DB::table('players')->where('id', 1)->update(['hp_now' => 40]);
            DB::table('players')->where('id', 2)->update(['hp_now' => 20]);
        })));
        $this->assertDatabaseHas('players', ['id' => 1, 'hp_now' => 4, 'regen_hp_start' => 4]);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 2, 'regen_hp_start' => 2]);
    }

    public function test_cast_rechecks_location_after_waiting_for_a_concurrent_move(): void
    {
        $this->assertSame(0, $this->successes($this->race(['cast', 1, 1, 2], holdLocks: function (): void {
            DB::table('users')->orderBy('id')->lockForUpdate()->get();
            DB::table('users')->where('id', 2)->update(['location_id' => 2]);
        })));
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 100]);
        $this->assertDatabaseHas('players', ['id' => 2, 'hp_now' => 50]);
    }

    public function test_combat_and_standalone_cast_guard_both_lock_player_before_pivot(): void
    {
        $this->assertSame(1, $this->successes($this->race(['combat_guard', 1, 1, ''], ['guard', 1, 1, ''], holdLocks: function (): void {
            DB::table('players')->where('id', 1)->lockForUpdate()->first();
        })));
        $this->assertDatabaseHas('players', ['id' => 1, 'mp_now' => 90]);
    }
}
