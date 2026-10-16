<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Clan;

use App\Modules\Chat\Application\DTOs\LocalizedChatMessage;
use App\Modules\Chat\Application\Services\ChatMessageTranslator;
use App\Modules\Chat\Domain\Events\ChatMessageCreated;
use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Clan\Application\Notifications\ClanApplicationReceivedNotification;
use App\Modules\Clan\Application\Services\ClanApplicationService;
use App\Modules\Clan\Application\UseCases\AcceptClanInvite;
use App\Modules\Clan\Application\UseCases\DeclineClanInvite;
use App\Modules\Clan\Application\UseCases\GetClanDirectoryPage;
use App\Modules\Clan\Application\UseCases\InviteToClan;
use App\Modules\Clan\Domain\Enums\ClanJoinRequestStatus;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Models\Clan;
use App\Modules\Clan\Domain\Models\ClanJoinRequest;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class ClanInviteTest extends TestCase
{
    private int $clanId;

    private int $defaultRoleId;

    private User $leader;

    private User $candidate;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();

        $leaderPlayerId = DB::table('players')->insertGetId(['lvl' => 10]);
        $candidatePlayerId = DB::table('players')->insertGetId(['lvl' => 4]);
        $this->leader = User::query()->create(['name' => 'Лидер', 'email' => 'leader@example.test']);
        $this->leader->player_id = $leaderPlayerId;
        $this->leader->save();
        $this->candidate = User::query()->create(['name' => 'Кандидат', 'email' => 'candidate@example.test']);
        $this->candidate->player_id = $candidatePlayerId;
        $this->candidate->save();

        $this->clanId = (int) DB::table('clans')->insertGetId([
            'name' => 'Клан', 'owner_id' => $this->leader->id, 'icon' => 'clan_icons/test.gif',
        ]);
        $leaderRoleId = (int) DB::table('clan_roles')->insertGetId([
            'clan_id' => $this->clanId, 'name' => 'Глава', 'permissions' => ClanPermission::allBits(), 'is_leader' => true, 'is_default' => true,
        ]);
        $this->defaultRoleId = (int) DB::table('clan_roles')->insertGetId([
            'clan_id' => $this->clanId, 'name' => 'Новичок', 'permissions' => 0, 'is_leader' => false, 'is_default' => true,
        ]);
        DB::table('clan_members')->insert([
            'clan_id' => $this->clanId, 'user_id' => $this->leader->id, 'role_id' => $leaderRoleId,
        ]);
    }

    public function test_invite_creates_pending_request_and_private_chat_message_only_for_candidate(): void
    {
        app(InviteToClan::class)->execute($this->leader, $this->candidate->name);

        // Игрок ещё НЕ в клане
        $this->assertSame(
            1,
            DB::table('clan_members')->where('clan_id', $this->clanId)->count(),
            'Приглашённый не должен попадать в клан до принятия',
        );

        $joinRequest = ClanJoinRequest::query()->sole();
        $this->assertSame(ClanJoinRequestStatus::INVITE, $joinRequest->status);
        $this->assertSame($this->candidate->id, (int) $joinRequest->user_id);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $joinRequest->uuid);

        $messages = ChatMessage::query()->get();
        $this->assertSame(1, $messages->count());
        $this->assertSame($this->candidate->id, (int) $messages[0]->target_user_id);
        $this->assertSame('clan_invite', $messages[0]->type->value);
        $rendered = $this->render($messages[0]);
        $this->assertStringContainsString('приглашает вас в клан', $rendered);
        $this->assertStringContainsString('<a href="'.route('clan.public', ['clan' => $this->clanId]).'" class="clan-invite-clan"', $rendered);
        $this->assertStringContainsString('class="chat-clan-icon"', $rendered);
        $this->assertStringContainsString('<b>Клан</b>', $rendered);
        $this->assertStringContainsString('<b>Лидер</b> [10]', $rendered);
        $this->assertSame(2, substr_count($rendered, 'clan-invite-action'));
        $this->assertStringContainsString($joinRequest->uuid, $rendered);
        $this->assertSame($messages[0]->id, (int) $joinRequest->chat_message_id);
    }

    public function test_accept_adds_member_and_notifies_clan_channel(): void
    {
        app(InviteToClan::class)->execute($this->leader, $this->candidate->name);
        $joinRequest = ClanJoinRequest::query()->sole();
        $inviteMessageId = $joinRequest->chat_message_id;

        app(AcceptClanInvite::class)->execute($this->candidate, $joinRequest->uuid);

        $this->assertDatabaseHas('clan_members', [
            'clan_id' => $this->clanId, 'user_id' => $this->candidate->id, 'role_id' => $this->defaultRoleId,
        ]);
        $this->assertSame(ClanJoinRequestStatus::ACCEPTED, $joinRequest->fresh()->status);
        $this->assertNull($joinRequest->fresh()->chat_message_id);
        $this->assertDatabaseMissing('chat_messages', ['id' => $inviteMessageId]);

        $joinMessage = ChatMessage::query()->where('clan_id', $this->clanId)->sole();
        $this->assertSame('clan', $joinMessage->channel->value);
        $rendered = $this->render($joinMessage);
        $this->assertStringContainsString('вступил в клан', $rendered);
        $this->assertStringContainsString('<b>Кандидат</b> [4]', $rendered);

        // Чужой пользователь не может принять чужое приглашение
        $third = User::query()->create(['name' => 'Третий']);
        $this->expectException(RuntimeException::class);
        app(AcceptClanInvite::class)->execute($third, $joinRequest->uuid);
    }

    public function test_decline_removes_invite_message_and_notifies_clan_channel(): void
    {
        app(InviteToClan::class)->execute($this->leader, $this->candidate->name);
        $joinRequest = ClanJoinRequest::query()->sole();
        $inviteMessageId = $joinRequest->chat_message_id;

        app(DeclineClanInvite::class)->execute($this->candidate, $joinRequest->uuid);

        $this->assertSame(ClanJoinRequestStatus::DECLINED, $joinRequest->fresh()->status);
        $this->assertNull($joinRequest->fresh()->chat_message_id);
        $this->assertDatabaseMissing('chat_messages', ['id' => $inviteMessageId]);
        $this->assertSame(
            0,
            DB::table('clan_members')->where('user_id', $this->candidate->id)->count(),
        );

        $declineMessage = ChatMessage::query()->where('clan_id', $this->clanId)->sole();
        $rendered = $this->render($declineMessage);
        $this->assertStringContainsString('отклонил приглашение в клан', $rendered);
        $this->assertStringContainsString('<b>Кандидат</b> [4]', $rendered);
    }

    private function render(ChatMessage $message): string
    {
        return app(ChatMessageTranslator::class)
            ->render(new LocalizedChatMessage($message->translation_key, $message->translation_params ?? []), 'ru');
    }

    public function test_invite_can_be_resent_after_decline(): void
    {
        app(InviteToClan::class)->execute($this->leader, $this->candidate->name);
        $firstUuid = ClanJoinRequest::query()->sole()->uuid;
        app(DeclineClanInvite::class)->execute($this->candidate, $firstUuid);

        app(InviteToClan::class)->execute($this->leader, $this->candidate->name);

        $this->assertSame(1, ClanJoinRequest::query()->count());
        $refreshed = ClanJoinRequest::query()->sole();
        $this->assertSame(ClanJoinRequestStatus::INVITE, $refreshed->status);
        $this->assertNotSame($firstUuid, $refreshed->uuid);
    }

    public function test_player_can_apply_and_notifies_clan_chat_and_leader_email(): void
    {
        Notification::fake();

        app(ClanApplicationService::class)->apply(
            $this->candidate,
            Clan::query()->findOrFail($this->clanId),
        );

        $application = ClanJoinRequest::query()->sole();
        $this->assertSame(ClanJoinRequestStatus::REQUEST, $application->status);
        $this->assertSame($this->candidate->id, (int) $application->user_id);
        $this->assertSame(1, DB::table('clan_members')->where('clan_id', $this->clanId)->count());

        $chatMessage = ChatMessage::query()->where('clan_id', $this->clanId)->sole();
        $this->assertSame('clan', $chatMessage->channel->value);
        $rendered = $this->render($chatMessage);
        $this->assertStringContainsString('подал заявку на вступление в клан', $rendered);
        $this->assertStringContainsString('<b>Кандидат</b> [4]', $rendered);

        $channels = array_map(
            static fn ($channel) => $channel->name,
            (new ChatMessageCreated($chatMessage))->broadcastOn(),
        );
        $this->assertEqualsCanonicalizing(['private-chat.clan.'.$this->clanId, 'private-chat.main'], $channels);

        Notification::assertSentOnDemand(ClanApplicationReceivedNotification::class);
    }

    public function test_clan_member_with_invite_permission_can_accept_an_application(): void
    {
        $application = ClanJoinRequest::query()->create([
            'clan_id' => $this->clanId,
            'user_id' => $this->candidate->id,
            'status' => ClanJoinRequestStatus::REQUEST,
        ]);

        app(ClanApplicationService::class)->accept($this->leader, $application);

        $this->assertDatabaseHas('clan_members', [
            'clan_id' => $this->clanId,
            'user_id' => $this->candidate->id,
            'role_id' => $this->defaultRoleId,
        ]);
        $this->assertSame(ClanJoinRequestStatus::ACCEPTED, $application->fresh()->status);

        $joinMessage = ChatMessage::query()->where('clan_id', $this->clanId)->sole();
        $this->assertStringContainsString('вступил в клан', $this->render($joinMessage));
    }

    public function test_clan_directory_ranks_clans_and_includes_member_counts(): void
    {
        $page = app(GetClanDirectoryPage::class)->execute($this->candidate);

        $this->assertSame(1, $page->clans->count());
        $this->assertSame(1, (int) $page->clans->first()->members_count);
        $this->assertFalse($page->applications->has($this->clanId));
    }

    private function createTables(): void
    {
        foreach ([
            'users', 'players', 'clans', 'clan_roles', 'clan_members',
            'clan_join_requests', 'clan_logs', 'clan_learned_skills', 'chat_messages',
        ] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('name');
            $t->string('email')->nullable();
            $t->unsignedInteger('player_id')->nullable();
            $t->boolean('is_admin')->default(0);
            $t->unsignedInteger('warehouse_count')->default(50);
            $t->unsignedInteger('bag_count')->default(25);
            $t->unsignedInteger('slot_count')->default(3);
            $t->unsignedInteger('location_id')->nullable();
            $t->timestamps();
        });
        Schema::create('players', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('lvl')->default(1);
        });
        Schema::create('clans', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('name')->unique();
            $t->string('icon')->nullable();
            $t->unsignedBigInteger('owner_id');
            $t->unsignedInteger('lvl')->default(1);
            $t->decimal('experience', 18, 2)->default(0);
            $t->timestamps();
        });
        Schema::create('clan_roles', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('clan_id');
            $t->string('name');
            $t->unsignedBigInteger('permissions')->default(0);
            $t->boolean('is_leader')->default(false);
            $t->boolean('is_default')->default(false);
            $t->timestamps();
        });
        Schema::create('clan_members', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('clan_id');
            $t->unsignedInteger('user_id');
            $t->unsignedInteger('role_id');
            $t->timestamps();

            $t->unique(['clan_id', 'user_id']);
        });
        Schema::create('clan_join_requests', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('clan_id');
            $t->unsignedInteger('user_id');
            $t->uuid('uuid')->nullable()->unique();
            $t->unsignedInteger('chat_message_id')->nullable();
            $t->string('status', 20);
            $t->text('message')->nullable();
            $t->timestamps();

            $t->unique(['clan_id', 'user_id']);
        });
        Schema::create('clan_logs', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('clan_id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('action');
            $t->text('details')->nullable();
            $t->timestamps();
        });
        Schema::create('clan_learned_skills', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('clan_id');
            $t->unsignedInteger('clan_skill_definition_id');
            $t->unsignedTinyInteger('current_level')->default(1);
            $t->timestamps();
        });
        Schema::create('chat_messages', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('user_id')->nullable();
            $t->string('channel');
            $t->unsignedInteger('target_user_id')->nullable();
            $t->unsignedInteger('clan_id')->nullable();
            $t->unsignedInteger('map_id')->nullable();
            $t->unsignedInteger('party_id')->nullable();
            $t->text('message');
            $t->string('translation_key')->nullable();
            $t->json('translation_params')->nullable();
            $t->string('type');
            $t->timestamps();
        });
    }
}
