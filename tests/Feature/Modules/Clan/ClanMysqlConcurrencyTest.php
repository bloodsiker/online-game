<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Clan\Domain\Enums\ClanPermission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesClanFixture;
use Tests\Support\RunsConcurrentRequests;
use Tests\TestCase;

class ClanMysqlConcurrencyTest extends TestCase
{
    use CreatesClanFixture;
    use RunsConcurrentRequests;

    private string $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = (string) getenv('CLAN_RACE_TEST_DATABASE');
        if ($this->database === '') {
            $this->markTestSkipped('Requires an explicitly created isolated MySQL database named codex_clan_race_*.');
        }
        $this->assertMatchesRegularExpression('/^codex_clan_race_[a-z0-9_]+$/D', $this->database);
        $this->assertSame('mysql', DB::connection()->getDriverName());
        $this->assertSame($this->database, DB::connection()->getDatabaseName());
        if (! Schema::hasTable('users')) {
            $this->createClanTables();
        }
        foreach (['player_magic_skills', 'clan_learned_skills', 'clan_skill_level_item_requirements', 'clan_skill_levels', 'clan_skill_definitions', 'magic_skills',
            'item_action_logs', 'shop_purchase_logs', 'clan_tax_payments', 'clan_treasury_logs', 'clan_warehouse_logs', 'clan_warehouses',
            'clan_logs', 'clan_join_requests', 'clan_members', 'clan_roles', 'clans', 'shop_carts', 'shop_item_requirements', 'shop_items',
            'item_on_locations', 'item_gems', 'item_runes', 'backpacks', 'items', 'share_item_instant_rewards', 'share_recipes', 'share_items',
            'structures', 'locations', 'users', 'players'] as $table) {
            DB::table($table)->delete();
        }
        $this->seedClanFixture();
    }

    private function race(array $first, ?array $second = null): array
    {
        return $this->runConcurrentRequests('tests/Support/clan-race-worker.php', $this->database, 'CLAN_RACE_TEST_DATABASE', $first, $second);
    }

    private function successes(array $results): int
    {
        return count(array_filter($results, fn ($result) => $result['ok']));
    }

    public function test_double_acceptance_creates_one_membership_and_one_log(): void
    {
        $invite = $this->joinRequest();
        $this->assertSame(1, $this->successes($this->race(['accept_invite', 3, $invite->uuid, ''])));
        $this->assertSame(1, DB::table('clan_members')->where('user_id', 3)->count());
        $this->assertDatabaseCount('clan_logs', 1);
        $this->assertDatabaseHas('clan_join_requests', ['id' => $invite->id, 'status' => 'accepted']);
    }

    public function test_legacy_invites_to_two_clans_cannot_create_two_memberships(): void
    {
        $first = $this->joinRequest();
        $second = $this->joinRequest(clanId: 2);
        $this->assertSame(1, $this->successes($this->race(['accept_invite', 3, $first->uuid, ''], ['accept_invite', 3, $second->uuid, ''])));
        $this->assertSame(1, DB::table('clan_members')->where('user_id', 3)->count());
        $this->assertSame(1, DB::table('clan_join_requests')->where('status', 'accepted')->count());
    }

    public function test_two_clans_cannot_create_two_pending_invites_for_the_same_player(): void
    {
        $this->assertSame(1, $this->successes($this->race(['invite', 1, 'Outsider', ''], ['invite', 4, 'Outsider', ''])));
        $this->assertDatabaseCount('clan_join_requests', 1);
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_two_clans_can_invite_different_players_without_unhandled_gap_lock_errors(): void
    {
        DB::table('users')->insert(['id' => 5, 'name' => 'Another Candidate']);
        $this->assertSame(2, $this->successes($this->race(['invite', 1, 'Outsider', ''], ['invite', 4, 'Another Candidate', ''])));
        $this->assertDatabaseCount('clan_join_requests', 2);
        $this->assertDatabaseCount('clan_logs', 2);
    }

    public function test_acceptance_and_cancellation_cannot_both_process_the_same_invite(): void
    {
        $invite = $this->joinRequest();
        $this->assertSame(1, $this->successes($this->race(['accept_invite', 3, $invite->uuid, ''], ['cancel', 1, $invite->id, ''])));
        $accepted = DB::table('clan_join_requests')->where('id', $invite->id)->value('status') === 'accepted';
        $this->assertSame($accepted, DB::table('clan_members')->where('user_id', 3)->exists());
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_application_and_acceptance_use_the_same_lock_order(): void
    {
        $request = $this->joinRequest(application: true);
        $this->assertSame(1, $this->successes($this->race(['apply', 3, 2, ''], ['accept', 1, $request->id, ''])));
        $this->assertDatabaseHas('clan_members', ['user_id' => 3, 'clan_id' => 1]);
    }

    public function test_withdraw_and_accept_cannot_both_process_the_same_application(): void
    {
        $request = $this->joinRequest(application: true);
        $this->assertSame(1, $this->successes($this->race(['withdraw', 3, $request->id, ''], ['accept', 1, $request->id, ''])));
        $accepted = DB::table('clan_join_requests')->where('id', $request->id)->value('status') === 'accepted';
        $this->assertSame($accepted, DB::table('clan_members')->where('user_id', 3)->exists());
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_two_applicants_cannot_both_take_the_last_place(): void
    {
        for ($id = 10; $id < 47; $id++) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Member '.$id]);
            DB::table('clan_members')->insert(['clan_id' => 1, 'user_id' => $id, 'role_id' => 2]);
        }
        DB::table('users')->insert(['id' => 5, 'name' => 'Candidate 2']);
        $first = $this->joinRequest(application: true);
        $second = $this->joinRequest(userId: 5, application: true);
        $this->assertSame(1, $this->successes($this->race(['accept', 1, $first->id, ''], ['accept', 1, $second->id, ''])));
        $this->assertSame(40, DB::table('clan_members')->where('clan_id', 1)->count());
    }

    public function test_invite_and_permission_revocation_are_serialized(): void
    {
        DB::table('clan_roles')->where('id', 2)->update(['permissions' => ClanPermission::INVITE->bit()]);
        $result = $this->race(['invite', 2, 'Outsider', ''], ['revoke', 1, 2, '']);
        $this->assertTrue($result[1]['ok']);
        $this->assertSame($result[0]['ok'] ? 1 : 0, DB::table('clan_join_requests')->count());
        $this->assertDatabaseHas('clan_roles', ['id' => 2, 'permissions' => 0]);
    }

    public function test_double_skill_purchase_charges_for_one_displayed_level(): void
    {
        $this->assertSame(1, $this->successes($this->race(['learn', 1, 1, 0])));
        $this->assertDatabaseHas('clans', ['id' => 1, 'points' => 40]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 60]);
        $this->assertDatabaseHas('backpacks', ['id' => 1, 'count' => 7]);
        $this->assertDatabaseCount('clan_learned_skills', 1);
        $this->assertDatabaseCount('player_magic_skills', 2);
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_different_skill_purchases_cannot_overdraw_shared_clan_points(): void
    {
        DB::table('clan_roles')->where('id', 2)->update(['permissions' => ClanPermission::LEARN_SKILL->bit()]);
        DB::table('items')->insert(['id' => 2, 'share_item_id' => 1]);
        DB::table('backpacks')->insert(['user_id' => 2, 'item_id' => 2, 'count' => 10]);
        $this->assertSame(1, $this->successes($this->race(['learn', 1, 1, 0], ['learn', 2, 2, 0])));
        $this->assertDatabaseHas('clans', ['id' => 1, 'points' => 40]);
        $this->assertSame(160, (int) DB::table('users')->whereIn('id', [1, 2])->sum('money'));
        $this->assertSame(17, (int) DB::table('backpacks')->sum('count'));
        $this->assertDatabaseCount('clan_learned_skills', 1);
    }

    public function test_leaving_during_skill_purchase_cannot_keep_the_clan_skill(): void
    {
        $this->assertSame(2, $this->successes($this->race(['learn', 1, 1, 0], ['leave', 2, '', ''])));
        $this->assertDatabaseMissing('clan_members', ['user_id' => 2]);
        $this->assertDatabaseMissing('player_magic_skills', ['player_id' => 2]);
        $this->assertDatabaseHas('player_magic_skills', ['player_id' => 1, 'magic_skill_id' => 1]);
    }

    public function test_tax_payment_and_leaving_do_not_restore_skills_to_a_former_member(): void
    {
        DB::table('clan_learned_skills')->insert(['clan_id' => 1, 'clan_skill_definition_id' => 1, 'current_level' => 1]);
        $this->assertSame(2, $this->successes($this->race(['tax', 1, '', ''], ['leave', 2, '', ''])));
        $this->assertDatabaseHas('clans', ['id' => 1, 'treasury' => 50]);
        $this->assertDatabaseMissing('player_magic_skills', ['player_id' => 2]);
        $this->assertDatabaseHas('player_magic_skills', ['player_id' => 1, 'magic_skill_id' => 1]);
    }

    public function test_transfer_and_target_leaving_always_preserve_one_leader(): void
    {
        $this->assertSame(1, $this->successes($this->race(['transfer', 1, 2, ''], ['leave', 2, '', ''])));
        $leaders = DB::table('clan_members')->where('clan_id', 1)->where('role_id', 1)->pluck('user_id');
        $this->assertCount(1, $leaders);
        $this->assertSame((int) $leaders->first(), (int) DB::table('clans')->where('id', 1)->value('owner_id'));
    }

    public function test_two_leadership_transfers_cannot_create_two_leaders(): void
    {
        DB::table('clan_members')->insert(['clan_id' => 1, 'user_id' => 3, 'role_id' => 2]);
        $this->assertSame(1, $this->successes($this->race(['transfer', 1, 2, ''], ['transfer', 1, 3, ''])));
        $leaders = DB::table('clan_members')->where('clan_id', 1)->where('role_id', 1)->pluck('user_id');
        $this->assertCount(1, $leaders);
        $this->assertSame((int) $leaders->first(), (int) DB::table('clans')->where('id', 1)->value('owner_id'));
    }

    public function test_role_assignment_and_deletion_cannot_leave_an_orphaned_role(): void
    {
        DB::table('clan_roles')->insert(['id' => 5, 'clan_id' => 1, 'name' => 'Custom']);
        $this->assertSame(1, $this->successes($this->race(['assign', 1, 2, 5], ['delete_role', 1, 5, ''])));
        $assigned = DB::table('clan_members')->where('user_id', 2)->value('role_id') === 5;
        $this->assertSame($assigned, DB::table('clan_roles')->where('id', 5)->exists());
    }
}
