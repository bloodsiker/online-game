<?php

declare(strict_types=1);

namespace App\Modules\Party\Domain\Contracts;

use App\Modules\Party\Domain\Enums\PartyStatus;
use App\Modules\Party\Infrastructure\Persistence\Models\Party;
use App\Modules\Party\Infrastructure\Persistence\Models\PartyInvite;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

interface PartyRepositoryInterface
{
    public function findActiveByUser(int $userId): ?Party;

    public function findOpenById(int $partyId): ?Party;

    public function createParty(int $leaderId, int $maxSize): Party;

    public function addMember(int $partyId, int $userId): void;

    public function removeMember(int $partyId, int $userId): void;

    public function isMember(int $partyId, int $userId): bool;

    public function disband(Party $party, PartyStatus $status = PartyStatus::DISBANDED): void;

    /**
     * Участники группы как пользователи (для рассылки сообщений в чат).
     *
     * @return Collection<int, User>
     */
    public function getMemberUsers(int $partyId): Collection;

    /**
     * Создаёт новое или обновляет существующее ожидание приглашения паре
     * (группа, игрок) — чтобы не плодить дубликаты при повторном зове.
     */
    public function createOrRefreshPendingInvite(int $partyId, int $inviterId, int $invitedId): PartyInvite;

    public function findPendingInviteByUuid(string $inviteUuid): ?PartyInvite;

    public function setInviteStatus(PartyInvite $invite, string $status): void;
}
