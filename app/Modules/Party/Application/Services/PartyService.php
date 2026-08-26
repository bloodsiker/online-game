<?php

declare(strict_types=1);

namespace App\Modules\Party\Application\Services;

use App\Modules\Chat\Application\Services\ChatService;
use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\Party\Infrastructure\Persistence\Models\Party;
use App\Modules\Party\Infrastructure\Persistence\Models\PartyInvite;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PartyService
{
    /** Максимальный размер группы (как на проде — «на 5 воинов»). */
    public const MAX_SIZE = 5;

    public function __construct(
        private readonly PartyRepositoryInterface $partyRepository,
        private readonly ChatService $chatService,
    ) {}

    public function createParty(int $maxSize = self::MAX_SIZE): Party
    {
        $user = Auth::user();

        if ($this->partyRepository->findActiveByUser($user->id) !== null) {
            throw new RuntimeException('Вы уже состоите в группе.');
        }

        return $this->partyRepository->createParty($user->id, $maxSize);
    }

    /**
     * Лидер не добавляет игрока напрямую: создаётся приглашение и в чат
     * приглашённому (видно только ему) уходит сообщение с кнопками решения.
     *
     * @return User Приглашённый игрок (для флеш-сообщения лидеру)
     */
    public function invite(int $partyId, int $targetUserId): User
    {
        $leader = Auth::user();
        $party = $this->partyRepository->findOpenById($partyId);

        if ($party === null) {
            throw new RuntimeException('Группа не найдена или не принимает игроков.');
        }

        if (! $party->isLeader((int) $leader->id)) {
            throw new RuntimeException('Только лидер может приглашать игроков.');
        }

        if ((int) $leader->id === $targetUserId) {
            throw new RuntimeException('Нельзя пригласить самого себя.');
        }

        if ($party->isFull()) {
            throw new RuntimeException('Группа заполнена.');
        }

        /** @var User|null $target */
        $target = User::query()->find($targetUserId);
        if ($target === null) {
            throw new RuntimeException('Игрок с таким именем не найден.');
        }

        if ($this->partyRepository->isMember($partyId, $target->id)) {
            throw new RuntimeException('Игрок уже в группе.');
        }

        if ($this->partyRepository->findActiveByUser($target->id) !== null) {
            throw new RuntimeException('Игрок уже состоит в другой группе.');
        }

        $invite = $this->partyRepository->createOrRefreshPendingInvite($partyId, (int) $leader->id, $target->id);
        $this->chatService->removeSystemMessageForUser($target, $invite->chat_message_id);

        $acceptUrl = route('party.accept', ['inviteUuid' => $invite->uuid]);
        $declineUrl = route('party.decline', ['inviteUuid' => $invite->uuid]);

        $message = $this->chatService->sendPartyInviteToUser(
            $target,
            sprintf(
                '%s приглашает вас в группу. <a href="%s" target="_top" class="party-invite-action" style="color:#2e7d32;" onclick="return handlePartyInviteAction(this);">[Принять]</a> <a href="%s" target="_top" class="party-invite-action" style="color:#a33;" onclick="return handlePartyInviteAction(this);">[Отклонить]</a>',
                $this->partyMemberLabel($leader),
                $acceptUrl,
                $declineUrl,
            ),
        );
        $invite->update(['chat_message_id' => $message->id]);

        return $target;
    }

    /** Приглашение по нику персонажа. */
    public function inviteByName(int $partyId, string $name): User
    {
        $target = User::whereHas('player', fn ($q) => $q->where('name', $name))
            ->orWhere('name', $name)
            ->first();

        if ($target === null) {
            throw new RuntimeException('Игрок с таким именем не найден.');
        }

        return $this->invite($partyId, (int) $target->id);
    }

    public function acceptInvite(User $user, string $inviteUuid): void
    {
        $invite = $this->partyRepository->findPendingInviteByUuid($inviteUuid);

        if ($invite === null || (int) $invite->invited_user_id !== (int) $user->id) {
            throw new RuntimeException('Приглашение не найдено или уже обработано.');
        }

        $party = $this->partyRepository->findOpenById((int) $invite->party_id);

        if ($party === null) {
            $this->partyRepository->setInviteStatus($invite, PartyInvite::STATUS_CANCELLED);
            $this->removeInviteChatMessage($user, $invite);
            throw new RuntimeException('Группа больше не принимает игроков.');
        }

        if ($party->isFull()) {
            $this->partyRepository->setInviteStatus($invite, PartyInvite::STATUS_CANCELLED);
            $this->removeInviteChatMessage($user, $invite);
            throw new RuntimeException('Группа уже заполнена.');
        }

        if ($this->partyRepository->findActiveByUser((int) $user->id) !== null) {
            $this->partyRepository->setInviteStatus($invite, PartyInvite::STATUS_DECLINED);
            $this->removeInviteChatMessage($user, $invite);
            throw new RuntimeException('Вы уже состоите в другой группе.');
        }

        DB::transaction(function () use ($invite, $user): void {
            $this->partyRepository->addMember((int) $invite->party_id, (int) $user->id);
            $this->partyRepository->setInviteStatus($invite, PartyInvite::STATUS_ACCEPTED);
        });

        $this->removeInviteChatMessage($user, $invite);

        $this->chatService->sendSystemToParty(
            (int) $invite->party_id,
            sprintf(
                'Игрок %s вступил в вашу группу.',
                $this->partyMemberLabel($user),
            ),
        );
    }

    public function declineInvite(User $user, string $inviteUuid): void
    {
        $invite = $this->partyRepository->findPendingInviteByUuid($inviteUuid);

        if ($invite === null || (int) $invite->invited_user_id !== (int) $user->id) {
            throw new RuntimeException('Приглашение не найдено или уже обработано.');
        }

        $this->partyRepository->setInviteStatus($invite, PartyInvite::STATUS_DECLINED);
        $this->removeInviteChatMessage($user, $invite);
        $this->chatService->sendSystemToParty(
            (int) $invite->party_id,
            sprintf('Игрок %s отклонил приглашение в группу.', $this->partyMemberLabel($user)),
        );
    }

    public function kick(int $partyId, int $targetUserId): void
    {
        $party = $this->partyRepository->findOpenById($partyId);

        if ($party === null) {
            throw new RuntimeException('Группа не найдена.');
        }

        $leader = Auth::user();
        if (! $party->isLeader((int) $leader->id)) {
            throw new RuntimeException('Только лидер может исключать игроков.');
        }

        if ($party->isLeader($targetUserId)) {
            throw new RuntimeException('Лидер не может исключить сам себя. Распустите группу.');
        }

        if (! $this->partyRepository->isMember($partyId, $targetUserId)) {
            throw new RuntimeException('Игрок не состоит в группе.');
        }

        $kickedName = $this->memberName($partyId, $targetUserId);
        $kicked = User::query()->find($targetUserId);

        $this->partyRepository->removeMember($partyId, $targetUserId);

        $this->chatService->sendSystemToParty(
            $partyId,
            sprintf('Игрок %s исключён из группы.', $kicked ? $this->partyMemberLabel($kicked) : e($kickedName)),
        );

        if ($kickedName !== null) {
            $kicked?->id && $this->chatService->sendPartyNoticeToUser($kicked, 'Вы были исключены из группы.');
        }
    }

    public function leave(int $partyId): void
    {
        $userId = (int) Auth::id();
        $party = $this->partyRepository->findOpenById($partyId)
            ?? Party::find($partyId);

        if ($party === null) {
            throw new RuntimeException('Группа не найдена.');
        }

        if ($party->isLeader($userId)) {
            $leftName = $this->memberName($partyId, $userId);
            $this->broadcastToMembers($partyId, 'Группа распущена лидером.');
            $this->partyRepository->disband($party);

            return;
        }

        $leftName = $this->memberName($partyId, $userId);
        $this->partyRepository->removeMember($partyId, $userId);

        if ($leftName !== null) {
            $this->broadcastToMembers($partyId, sprintf('%s покинул группу.', $this->partyMemberLabel(Auth::user())));
        }
    }

    public function disband(int $partyId): void
    {
        $party = Party::findOrFail($partyId);

        if (! $party->isLeader((int) Auth::id())) {
            throw new RuntimeException('Только лидер может распустить группу.');
        }

        $this->broadcastToMembers($partyId, 'Группа распущена лидером.');
        $this->partyRepository->disband($party);
    }

    public function getMyParty(): ?Party
    {
        return $this->partyRepository->findActiveByUser(Auth::id())
            ?->load('leader.player', 'members.user.player', 'members.user.clanMembership.clan');
    }

    private function memberName(int $partyId, int $userId): ?string
    {
        foreach ($this->partyRepository->getMemberUsers($partyId) as $member) {
            if ((int) $member->id === $userId) {
                return $member->name;
            }
        }

        return null;
    }

    private function removeInviteChatMessage(User $user, PartyInvite $invite): void
    {
        $this->chatService->removeSystemMessageForUser($user, $invite->chat_message_id);
        $invite->chat_message_id = null;
        $invite->save();
    }

    private function partyMemberLabel(User $user): string
    {
        return sprintf(
            '<b>%s</b> [%d] <a href="#" onclick="chatOpenUserInfo(%d); return false;" title="Информация о персонаже"><img src="%s" width="10" height="10" align="absmiddle" alt=""></a>',
            e($user->name),
            (int) ($user->player?->lvl ?? 0),
            $user->id,
            asset('main/images/player_info.gif'),
        );
    }

    /**
     * Служебное сообщение о событии группы — персональная копия каждому
     * участнику (канал Main с target_user_id видно только адресату).
     */
    private function broadcastToMembers(int $partyId, string $message, ?int $exceptUserId = null): void
    {
        foreach ($this->partyRepository->getMemberUsers($partyId) as $member) {
            if ($exceptUserId !== null && (int) $member->id === $exceptUserId) {
                continue;
            }
            $this->chatService->sendPartyNoticeToUser($member, $message);
        }
    }
}
