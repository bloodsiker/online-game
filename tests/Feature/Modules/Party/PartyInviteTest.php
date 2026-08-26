<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Party;

use App\Modules\Chat\Domain\Models\ChatMessage;
use App\Modules\Party\Application\Services\PartyService;
use App\Modules\Party\Infrastructure\Persistence\Models\PartyInvite;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PartyInviteTest extends TestCase
{
    private User $leader;

    private User $candidate;

    private int $partyId;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', ':memory:');
        DB::purge('sqlite');
        $this->createTables();

        $leaderPlayerId = DB::table('players')->insertGetId(['lvl' => 5]);
        $candidatePlayerId = DB::table('players')->insertGetId(['lvl' => 4]);
        $this->leader = User::query()->create(['name' => 'Лидер']);
        $this->leader->player_id = $leaderPlayerId;
        $this->leader->save();
        $this->candidate = User::query()->create(['name' => 'Кандидат']);
        $this->candidate->player_id = $candidatePlayerId;
        $this->candidate->save();

        $this->partyId = (int) DB::table('parties')->insertGetId([
            'leader_user_id' => $this->leader->id,
            'invite_code' => 'abc123',
            'max_players' => 5,
            'status' => 'open',
        ]);
        DB::table('party_members')->insert([
            'party_id' => $this->partyId,
            'user_id' => $this->leader->id,
        ]);

        Auth::setUser($this->leader);
    }

    public function test_invite_creates_pending_invite_and_private_chat_message_only_for_candidate(): void
    {
        $invited = app(PartyService::class)->invite($this->partyId, (int) $this->candidate->id);

        $this->assertSame($this->candidate->id, $invited->id);

        // Игрок ещё НЕ в группе
        $this->assertSame(
            1,
            DB::table('party_members')->where('party_id', $this->partyId)->count(),
            'Приглашённый не должен попадать в группу до принятия',
        );

        // Приглашение в статусе pending
        $invite = PartyInvite::query()->sole();
        $this->assertSame(PartyInvite::STATUS_PENDING, $invite->status);
        $this->assertSame($this->candidate->id, (int) $invite->invited_user_id);
        $this->assertSame($this->leader->id, (int) $invite->inviter_user_id);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $invite->uuid);

        // Сообщение в чате адресовано только кандидату
        $messages = ChatMessage::query()->get();
        $this->assertSame(1, $messages->count());
        $this->assertSame($this->candidate->id, (int) $messages[0]->target_user_id);
        $this->assertSame('party_invite', $messages[0]->type->value);
        $this->assertStringContainsString('приглашает вас в группу', $messages[0]->message);
        $this->assertStringContainsString('<b>Лидер</b> [5]', $messages[0]->message);
        $this->assertStringContainsString('chatOpenUserInfo('.$this->leader->id.'); return false;', $messages[0]->message);
        $this->assertStringContainsString('player_info.gif', $messages[0]->message);
        $this->assertStringContainsString('Принять', $messages[0]->message);
        $this->assertSame(2, substr_count($messages[0]->message, 'party-invite-action'));
        $this->assertStringContainsString($invite->uuid, $messages[0]->message);
        $this->assertStringNotContainsString('/party/accept/'.$invite->id.'"', $messages[0]->message);
        $this->assertSame($messages[0]->id, (int) $invite->chat_message_id);
    }

    public function test_repeated_invite_refreshes_single_pending_row(): void
    {
        $service = app(PartyService::class);
        $service->invite($this->partyId, (int) $this->candidate->id);
        $firstUuid = PartyInvite::query()->sole()->uuid;
        $service->invite($this->partyId, (int) $this->candidate->id);

        $this->assertSame(1, PartyInvite::query()->count());
        $this->assertNotSame($firstUuid, PartyInvite::query()->sole()->uuid);
    }

    public function test_accept_adds_member_and_notifies_party_in_chat(): void
    {
        $service = app(PartyService::class);
        $service->invite($this->partyId, (int) $this->candidate->id);
        $invite = PartyInvite::query()->sole();
        $inviteMessageId = $invite->chat_message_id;

        Auth::setUser($this->candidate);
        $service->acceptInvite($this->candidate, $invite->uuid);

        // Оба участника
        $members = DB::table('party_members')->where('party_id', $this->partyId)->pluck('user_id');
        $this->assertEqualsCanonicalizing([$this->leader->id, $this->candidate->id], $members->all());
        $this->assertSame(PartyInvite::STATUS_ACCEPTED, $invite->fresh()->status);
        $this->assertNull($invite->fresh()->chat_message_id);
        $this->assertDatabaseMissing('chat_messages', ['id' => $inviteMessageId]);

        // Сообщение о вступлении пишется в канал группы.
        $joinMessages = ChatMessage::query()
            ->where('message', 'like', '%вступил в вашу группу%')
            ->get();
        $this->assertCount(1, $joinMessages);
        $this->assertSame('party', $joinMessages[0]->channel->value);
        $this->assertSame('party_notice', $joinMessages[0]->type->value);
        $this->assertSame($this->partyId, (int) $joinMessages[0]->party_id);
        $this->assertNull($joinMessages[0]->target_user_id);
        $this->assertStringContainsString('Игрок <b>Кандидат</b> [4]', $joinMessages[0]->message);
        $this->assertStringContainsString('chatOpenUserInfo('.$this->candidate->id.'); return false;', $joinMessages[0]->message);

        // Чужой пользователь не может принять чужое приглашение
        $third = User::query()->create(['name' => 'Третий']);
        Auth::setUser($third);
        $this->expectException(\RuntimeException::class);
        $service->acceptInvite($third, $invite->uuid);
    }

    public function test_leave_sends_left_message_to_remaining_members(): void
    {
        $service = app(PartyService::class);
        $service->invite($this->partyId, (int) $this->candidate->id);
        $invite = PartyInvite::query()->sole();

        Auth::setUser($this->candidate);
        $service->acceptInvite($this->candidate, $invite->uuid);

        ChatMessage::query()->delete();

        Auth::setUser($this->candidate);
        $service->leave($this->partyId);

        $this->assertSame(
            1,
            DB::table('party_members')->where('party_id', $this->partyId)->count(),
        );

        $leftMessages = ChatMessage::query()->where('message', 'like', '%покинул группу%')->get();
        $this->assertSame(1, $leftMessages->count());
        $this->assertSame($this->leader->id, (int) $leftMessages[0]->target_user_id);
        $this->assertSame('party_notice', $leftMessages[0]->type->value);
        $this->assertStringContainsString('<b>Кандидат</b> [4]', $leftMessages[0]->message);
        $this->assertStringContainsString('chatOpenUserInfo('.$this->candidate->id.'); return false;', $leftMessages[0]->message);
    }

    public function test_kick_sends_notice_to_party_channel(): void
    {
        $service = app(PartyService::class);
        $service->invite($this->partyId, (int) $this->candidate->id);
        $invite = PartyInvite::query()->sole();

        Auth::setUser($this->candidate);
        $service->acceptInvite($this->candidate, $invite->uuid);
        ChatMessage::query()->delete();

        Auth::setUser($this->leader);
        $service->kick($this->partyId, (int) $this->candidate->id);

        $message = ChatMessage::query()->where('message', 'like', '%исключён из группы%')->sole();
        $this->assertSame('party', $message->channel->value);
        $this->assertSame('party_notice', $message->type->value);
        $this->assertSame($this->partyId, (int) $message->party_id);
        $this->assertStringContainsString('Игрок <b>Кандидат</b> [4]', $message->message);
        $this->assertStringContainsString('chatOpenUserInfo('.$this->candidate->id.'); return false;', $message->message);
        $this->assertDatabaseHas('chat_messages', [
            'target_user_id' => $this->candidate->id,
            'message' => 'Вы были исключены из группы.',
            'type' => 'party_notice',
        ]);
    }

    public function test_decline_removes_the_original_invitation_from_chat(): void
    {
        $service = app(PartyService::class);
        $service->invite($this->partyId, (int) $this->candidate->id);
        $invite = PartyInvite::query()->sole();
        $inviteMessageId = $invite->chat_message_id;

        Auth::setUser($this->candidate);
        $service->declineInvite($this->candidate, $invite->uuid);

        $this->assertSame(PartyInvite::STATUS_DECLINED, $invite->fresh()->status);
        $this->assertNull($invite->fresh()->chat_message_id);
        $this->assertDatabaseMissing('chat_messages', ['id' => $inviteMessageId]);
        $this->assertDatabaseHas('chat_messages', [
            'party_id' => $this->partyId,
            'type' => 'party_notice',
        ]);
        $declineMessage = ChatMessage::query()->where('message', 'like', '%отклонил приглашение в группу%')->sole();
        $this->assertStringContainsString('<b>Кандидат</b> [4]', $declineMessage->message);
        $this->assertStringContainsString('chatOpenUserInfo('.$this->candidate->id.'); return false;', $declineMessage->message);
    }

    private function createTables(): void
    {
        foreach (['users', 'players', 'parties', 'party_members', 'party_invites', 'chat_messages'] as $t) {
            Schema::dropIfExists($t);
        }

        Schema::create('users', function (Blueprint $t): void {
            $t->increments('id');
            $t->string('name');
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
        Schema::create('parties', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('leader_user_id');
            $t->string('invite_code', 16);
            $t->unsignedTinyInteger('max_players')->default(4);
            $t->string('status')->default('open');
            $t->timestamps();
        });
        Schema::create('party_members', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('party_id');
            $t->unsignedInteger('user_id');
            $t->boolean('is_ready')->default(0);
            $t->timestamps();
        });
        Schema::create('party_invites', function (Blueprint $t): void {
            $t->increments('id');
            $t->unsignedInteger('party_id');
            $t->unsignedInteger('inviter_user_id');
            $t->unsignedInteger('invited_user_id');
            $t->uuid('uuid')->unique();
            $t->unsignedInteger('chat_message_id')->nullable();
            $t->string('status')->default('pending');
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
            $t->string('type');
            $t->timestamps();
        });
    }
}
