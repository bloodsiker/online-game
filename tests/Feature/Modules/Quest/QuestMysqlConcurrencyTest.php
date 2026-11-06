<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Quest;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesQuestFixture;
use Tests\Support\QuestTestDoubles;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class QuestMysqlConcurrencyTest extends TestCase
{
    use CreatesQuestFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('QUEST_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires isolated codex_quest_race_* MySQL database.');
        }
        $this->assertMatchesRegularExpression('/^codex_quest_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        if (! Schema::hasTable('users')) {
            $this->createQuestTables();
        }
        foreach ([
            'quest_test_notifications', 'quest_rewards', 'quest_player_objectives', 'quest_clan_objectives',
            'quest_players', 'quest_clan_progress', 'quest_objectives', 'quest_stages', 'quests',
            'reputation_tiers', 'quest_influence_requirements',
            'item_on_locations', 'item_gems', 'item_runes', 'share_item_instant_rewards',
            'shop_purchase_logs', 'item_action_logs', 'clan_tax_payments', 'clan_treasury_logs',
            'clan_warehouse_logs', 'clan_warehouses', 'clan_logs', 'clan_join_requests', 'clan_members',
            'clan_roles', 'clans', 'shop_carts', 'shop_item_requirements', 'shop_items', 'backpacks',
            'items', 'share_recipes', 'share_items', 'structures', 'locations', 'users', 'players',
        ] as $table) {
            DB::table($table)->delete();
        }
        QuestTestDoubles::bind();
    }

    private function race(array $first, ?array $second = null): array
    {
        return $this->runConcurrentRequests('tests/Support/quest-race-worker.php', $this->database, 'QUEST_RACE_TEST_DATABASE', $first, $second);
    }

    public function test_two_personal_completions_consume_and_reward_once(): void
    {
        $this->seedQuestFixture();
        $results = $this->race(['complete', 1, 1, 0]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 150]);
        $this->assertDatabaseHas('players', ['id' => 1, 'exp' => 30]);
        $this->assertDatabaseHas('backpacks', ['count' => 8]);
        $this->assertDatabaseCount('quest_test_notifications', 1);
    }

    public function test_two_clan_completions_reward_once(): void
    {
        $this->seedQuestFixture(clan: true);
        DB::table('quest_rewards')->insert(['quest_id' => 1, 'type' => 'clan_points', 'amount' => 7]);
        $results = $this->race(['complete', 1, 1, 0]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('clans', ['points' => 7]);
        $this->assertDatabaseHas('backpacks', ['count' => 8]);
        $this->assertDatabaseCount('quest_test_notifications', 1);
    }

    public function test_two_stage_requests_advance_and_grant_only_once(): void
    {
        $this->seedQuestFixture();
        DB::table('quest_stages')->insert([
            ['id' => 1, 'quest_id' => 1, 'order' => 1, 'grant_share_item_id' => 1, 'grant_amount' => 3],
            ['id' => 2, 'quest_id' => 1, 'order' => 2, 'grant_share_item_id' => null, 'grant_amount' => 1],
        ]);
        DB::table('quest_players')->update(['current_stage_id' => 1]);
        DB::table('quest_objectives')->update(['stage_id' => 1]);
        $results = $this->race(['complete', 1, 1, 0]);
        $this->assertSame(1, count(array_filter($results, fn ($result) => $result['ok'])));
        $this->assertDatabaseHas('quest_players', ['current_stage_id' => 2, 'revision' => 1]);
        $this->assertDatabaseHas('backpacks', ['count' => 9]);
        $this->assertDatabaseCount('quest_test_notifications', 0);
    }

    public function test_double_take_does_not_duplicate_deliver_items(): void
    {
        $this->seedQuestFixture();
        DB::table('quest_player_objectives')->delete();
        DB::table('quest_players')->delete();
        $this->race(['take', 1, 0, 0]);
        $this->assertDatabaseCount('quest_players', 1);
        $this->assertDatabaseHas('backpacks', ['count' => 14]);
        $this->assertDatabaseCount('quest_test_notifications', 1);
    }

    public function test_two_members_cannot_take_over_or_duplicate_the_same_clan_quest(): void
    {
        $this->seedQuestFixture(clan: true);
        DB::table('quest_clan_objectives')->delete();
        DB::table('quest_clan_progress')->delete();
        $this->race(['take', 1, 0, 0], ['take', 2, 0, 0]);
        $this->assertDatabaseCount('quest_clan_progress', 1);
        $this->assertSame(14, (int) DB::table('backpacks')->sum('count'));
        $this->assertDatabaseCount('quest_test_notifications', 1);
    }

    public function test_clan_leader_cancellation_and_acceptor_completion_are_serialized(): void
    {
        $this->seedQuestFixture(clan: true);
        DB::table('quest_clan_progress')->update(['user_id' => 2]);
        DB::table('backpacks')->update(['user_id' => 2]);
        $this->race(['complete', 2, 1, 0], ['cancel_clan', 1, 1, 0]);
        $completed = DB::table('quest_clan_progress')->where('status', 'completed')->exists();
        $this->assertDatabaseHas('users', ['id' => 2, 'money' => $completed ? 150 : 100]);
        $this->assertSame($completed ? 8 : 6, (int) DB::table('backpacks')->sum('count'));
        $this->assertDatabaseCount('quest_test_notifications', $completed ? 1 : 0);
    }

    public function test_cancellation_and_completion_are_serialized(): void
    {
        $this->seedQuestFixture();
        $this->race(['complete', 1, 1, 0], ['cancel', 1, 1, 0]);
        $completed = DB::table('quest_players')->where('status', 'completed')->exists();
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => $completed ? 150 : 100]);
        $this->assertSame($completed ? 8 : 6, (int) DB::table('backpacks')->sum('count'));
        $this->assertDatabaseCount('quest_test_notifications', $completed ? 1 : 0);
    }
}
