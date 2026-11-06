<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Clan\Application\UseCases\PayClanTax;
use App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanSkillDefinition;
use App\Modules\Clan\Domain\Services\ClanLogService;
use App\Modules\Clan\Domain\Services\ClanService;
use App\Modules\Clan\Domain\Services\ClanSkillService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Mockery;
use RuntimeException;
use Tests\Support\CreatesClanFixture;
use Tests\TestCase;

class ClanTransactionsTest extends TestCase
{
    use CreatesClanFixture;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
        DB::purge('sqlite');
        $this->createClanTables();
        $this->seedClanFixture();
    }

    private function rejected(callable $action): void
    {
        try {
            $action();
            $this->fail('An outdated or unauthorized action must be rejected.');
        } catch (RuntimeException $exception) {
            $this->assertNotSame('', $exception->getMessage());
        }
    }

    private function learn(int $definitionId = 1, int $expectedLevel = 0): ?string
    {
        $user = $this->economyUser();

        return app(ClanSkillService::class)->learn(Clan::findOrFail(1), ClanSkillDefinition::findOrFail($definitionId), $user->player, $user, $expectedLevel);
    }

    public function test_accept_invite_joins_once_and_applies_skills_in_the_same_transaction(): void
    {
        DB::table('clan_learned_skills')->insert(['clan_id' => 1, 'clan_skill_definition_id' => 1, 'current_level' => 1]);
        $request = $this->joinRequest();
        $user = $this->economyUser(3);
        $user->load('clanMembership');
        app(ClanService::class)->acceptInvite($user, $request->uuid);
        $this->rejected(fn () => app(ClanService::class)->acceptInvite($user, $request->uuid));
        $this->assertSame(1, DB::table('clan_members')->where('user_id', 3)->count());
        $this->assertDatabaseHas('player_magic_skills', ['player_id' => 3, 'magic_skill_id' => 1]);
        $this->assertSame(ClanJoinRequestStatus::ACCEPTED, $request->fresh()->status);
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_accept_invite_rechecks_capacity_and_preserves_pending_invite_if_full(): void
    {
        $request = $this->joinRequest();
        for ($id = 10; $id < 48; $id++) {
            DB::table('users')->insert(['id' => $id, 'name' => 'Member '.$id]);
            DB::table('clan_members')->insert(['clan_id' => 1, 'user_id' => $id, 'role_id' => 2]);
        }
        $this->rejected(fn () => app(ClanService::class)->acceptInvite($this->economyUser(3), $request->uuid));
        $this->assertSame(40, DB::table('clan_members')->where('clan_id', 1)->count());
        $this->assertSame(ClanJoinRequestStatus::INVITE, $request->fresh()->status);
    }

    public function test_cancel_cannot_use_old_uuid_or_another_clans_permissions(): void
    {
        $request = $this->joinRequest();
        $this->rejected(fn () => app(ClanService::class)->inviteCancel($this->economyUser(4), $request));
        $old = clone $request;
        app(ClanService::class)->declineInvite($this->economyUser(3), $request->uuid);
        app(ClanService::class)->invite($this->economyUser(1), 'Outsider');
        $this->rejected(fn () => app(ClanService::class)->inviteCancel($this->economyUser(1), $old));
        $this->assertSame(ClanJoinRequestStatus::INVITE, $request->fresh()->status);
    }

    public function test_application_acceptance_rechecks_revoked_permissions_and_request_state(): void
    {
        $actor = $this->economyUser(1);
        $actor->load('clanMembership.role');
        $request = $this->joinRequest(application: true);
        DB::table('clan_roles')->where('id', 1)->update(['permissions' => 0]);
        $this->rejected(fn () => app(ClanService::class)->acceptApplication($actor, $request));
        DB::table('clan_roles')->where('id', 1)->update(['permissions' => ClanPermission::allBits()]);
        app(ClanService::class)->withdrawApplication($this->economyUser(3), $request);
        $this->rejected(fn () => app(ClanService::class)->acceptApplication($actor, $request));
        $this->assertDatabaseMissing('clan_members', ['user_id' => 3]);
    }

    public function test_stale_player_can_neither_apply_nor_accept_invite_after_joining_another_clan(): void
    {
        $user = $this->economyUser(3);
        $user->load('clanMembership');
        $request = $this->joinRequest();
        DB::table('clan_members')->insert(['clan_id' => 2, 'user_id' => 3, 'role_id' => 4]);
        $this->rejected(fn () => app(ClanService::class)->applyToClan($user, Clan::findOrFail(1)));
        $this->rejected(fn () => app(ClanService::class)->acceptInvite($user, $request->uuid));
        $this->assertDatabaseHas('clan_members', ['user_id' => 3, 'clan_id' => 2]);
        $this->assertSame(ClanJoinRequestStatus::INVITE, $request->fresh()->status);
    }

    public function test_skill_purchase_rechecks_level_and_charges_exactly_once(): void
    {
        $this->assertNull($this->learn());
        $this->assertNotNull($this->learn());
        $this->assertDatabaseHas('clans', ['id' => 1, 'points' => 40]);
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 60]);
        $this->assertDatabaseHas('backpacks', ['user_id' => 1, 'count' => 7]);
        $this->assertDatabaseHas('clan_learned_skills', ['current_level' => 1]);
        $this->assertDatabaseCount('player_magic_skills', 2);
        $this->assertDatabaseCount('clan_logs', 1);
    }

    public function test_skill_upgrade_updates_every_member_and_removes_the_old_level(): void
    {
        $this->assertNull($this->learn());
        DB::table('clans')->where('id', 1)->update(['points' => 100]);
        $this->assertNull($this->learn(expectedLevel: 1));
        $this->assertDatabaseHas('clan_learned_skills', ['current_level' => 2]);
        $this->assertDatabaseMissing('player_magic_skills', ['magic_skill_id' => 1]);
        $this->assertSame(2, DB::table('player_magic_skills')->where('magic_skill_id', 2)->count());
    }

    public function test_missing_materials_roll_back_money_points_and_skill(): void
    {
        DB::table('backpacks')->where('id', 1)->update(['count' => 2]);
        $this->assertNotNull($this->learn());
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('clans', ['id' => 1, 'points' => 100]);
        $this->assertDatabaseCount('clan_learned_skills', 0);
        $this->assertDatabaseCount('player_magic_skills', 0);
        $this->assertDatabaseCount('clan_logs', 0);
    }

    public function test_duplicate_skill_insert_returns_a_message_and_rolls_back_all_costs(): void
    {
        DB::unprepared('CREATE TRIGGER simulate_skill_race BEFORE INSERT ON clan_learned_skills
            BEGIN INSERT INTO clan_learned_skills (clan_id, clan_skill_definition_id, current_level)
            VALUES (NEW.clan_id, NEW.clan_skill_definition_id, NEW.current_level); END');
        $this->assertSame(__('clan.action.skill_changed'), $this->learn());
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('clans', ['id' => 1, 'points' => 100]);
        $this->assertDatabaseHas('backpacks', ['id' => 1, 'count' => 10]);
        $this->assertDatabaseCount('clan_learned_skills', 0);
        $this->assertDatabaseCount('player_magic_skills', 0);
    }

    public function test_skill_purchase_rechecks_tax_membership_and_permissions(): void
    {
        $user = $this->economyUser(1);
        $clan = Clan::findOrFail(1);
        $user->load('clanMembership.role');
        DB::table('clans')->where('id', 1)->update(['tax_paid_until' => now()->subSecond()]);
        $this->assertNotNull(app(ClanSkillService::class)->learn($clan, ClanSkillDefinition::findOrFail(1), $user->player, $user));
        DB::table('clans')->where('id', 1)->update(['tax_paid_until' => now()->addDay()]);
        DB::table('clan_roles')->where('id', 1)->update(['permissions' => 0]);
        $this->assertNotNull(app(ClanSkillService::class)->learn($clan, ClanSkillDefinition::findOrFail(1), $user->player, $user));
        $this->assertDatabaseCount('clan_learned_skills', 0);
    }

    public function test_failed_skill_log_rolls_back_currency_materials_and_all_member_skills(): void
    {
        $log = Mockery::mock(ClanLogService::class);
        $log->shouldReceive('write')->andThrow(new RuntimeException('Log unavailable'));
        app()->instance(ClanLogService::class, $log);
        $this->assertNotNull($this->learn());
        $this->assertDatabaseHas('users', ['id' => 1, 'money' => 100]);
        $this->assertDatabaseHas('clans', ['id' => 1, 'points' => 100]);
        $this->assertDatabaseHas('backpacks', ['id' => 1, 'count' => 10]);
        $this->assertDatabaseCount('clan_learned_skills', 0);
        $this->assertDatabaseCount('player_magic_skills', 0);
    }

    public function test_transfer_updates_both_roles_and_owner_and_rejects_old_leader_permissions(): void
    {
        $user = $this->economyUser(1);
        $user->load('clanMembership.role');
        app(ClanService::class)->saveMemberRoles($user, [1 => ['grade' => 2], 2 => ['grade' => 1]]);
        $this->assertDatabaseHas('clans', ['id' => 1, 'owner_id' => 2]);
        $this->assertDatabaseHas('clan_members', ['user_id' => 1, 'role_id' => 2]);
        $this->assertDatabaseHas('clan_members', ['user_id' => 2, 'role_id' => 1]);
        $this->rejected(fn () => app(ClanService::class)->saveMemberRoles($user, [1 => ['grade' => 1], 2 => ['grade' => 2]]));
        $this->rejected(fn () => app(ClanService::class)->leaveClan($this->economyUser(2)));
    }

    public function test_officer_cannot_transfer_leadership_and_missing_target_cannot_remove_leader(): void
    {
        DB::table('clan_roles')->where('id', 2)->update(['permissions' => ClanPermission::CHANGE_RANKS->bit()]);
        $this->rejected(fn () => app(ClanService::class)->saveMemberRoles($this->economyUser(2), [1 => ['grade' => 2], 2 => ['grade' => 1]]));
        $this->rejected(fn () => app(ClanService::class)->saveMemberRoles($this->economyUser(1), [1 => ['grade' => 2], 3 => ['grade' => 1]]));
        $this->assertDatabaseHas('clans', ['id' => 1, 'owner_id' => 1]);
        $this->assertDatabaseHas('clan_members', ['user_id' => 1, 'role_id' => 1]);
    }

    public function test_failed_transfer_log_rolls_back_owner_and_both_roles(): void
    {
        $log = Mockery::mock(ClanLogService::class);
        $log->shouldReceive('write')->andThrow(new RuntimeException('Log unavailable'));
        app()->instance(ClanLogService::class, $log);
        $this->rejected(fn () => app(ClanService::class)->saveMemberRoles($this->economyUser(1), [1 => ['grade' => 2], 2 => ['grade' => 1]]));
        $this->assertDatabaseHas('clans', ['id' => 1, 'owner_id' => 1]);
        $this->assertDatabaseHas('clan_members', ['user_id' => 1, 'role_id' => 1]);
        $this->assertDatabaseHas('clan_members', ['user_id' => 2, 'role_id' => 2]);
    }

    public function test_leaving_removes_clan_skills_inside_the_membership_transaction(): void
    {
        $this->assertNull($this->learn());
        app(ClanService::class)->leaveClan($this->economyUser(2));
        $this->assertDatabaseMissing('clan_members', ['user_id' => 2]);
        $this->assertDatabaseMissing('player_magic_skills', ['player_id' => 2]);
        $this->assertDatabaseHas('player_magic_skills', ['player_id' => 1, 'magic_skill_id' => 1]);
    }

    public function test_failed_clan_creation_rolls_back_roles_membership_and_uploaded_icon(): void
    {
        Storage::fake('public');
        $log = Mockery::mock(ClanLogService::class);
        $log->shouldReceive('write')->andThrow(new RuntimeException('Log unavailable'));
        app()->instance(ClanLogService::class, $log);
        $this->rejected(fn () => app(ClanService::class)->create($this->economyUser(3), 'New Clan', UploadedFile::fake()->create('icon.gif', 1, 'image/gif')));
        $this->assertDatabaseCount('clans', 2);
        $this->assertDatabaseCount('clan_roles', 4);
        $this->assertDatabaseCount('clan_members', 3);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_tax_payment_rechecks_revoked_rights_and_reapplies_skills_atomically(): void
    {
        config(['game.clan_monthly_tax' => 50]);
        $user = $this->economyUser(1);
        $user->load('clanMembership.role');
        DB::table('clan_roles')->where('id', 1)->update(['permissions' => 0]);
        $this->rejected(fn () => app(PayClanTax::class)->execute($user, 1));
        $this->assertDatabaseCount('clan_tax_payments', 0);
        $this->assertDatabaseHas('clans', ['id' => 1, 'treasury' => 100]);
        DB::table('clan_roles')->where('id', 1)->update(['permissions' => ClanPermission::allBits()]);
        DB::table('clan_learned_skills')->insert(['clan_id' => 1, 'clan_skill_definition_id' => 1, 'current_level' => 1]);
        app(PayClanTax::class)->execute($user, 1);
        $this->assertDatabaseHas('clans', ['id' => 1, 'treasury' => 50]);
        $this->assertDatabaseCount('clan_tax_payments', 1);
        $this->assertDatabaseCount('player_magic_skills', 2);
    }
}
