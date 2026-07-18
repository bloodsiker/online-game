<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Party\Party;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Repositories\PartyRepository;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class PartyService
{
    /** Максимальный размер группы (как на проде — «на 5 воинов»). */
    public const MAX_SIZE = 5;

    public function __construct(
        private readonly PartyRepository $partyRepository,
    ) {}

    public function createParty(int $maxSize = self::MAX_SIZE): Party
    {
        $user = Auth::user();

        if ($this->partyRepository->findActiveByUser($user->id) !== null) {
            throw new RuntimeException('Вы уже состоите в группе.');
        }

        return $this->partyRepository->createParty($user->id, $maxSize);
    }

    public function invite(int $partyId, int $targetUserId): void
    {
        $party = $this->partyRepository->findOpenById($partyId);

        if ($party === null) {
            throw new RuntimeException('Группа не найдена или не принимает игроков.');
        }

        if (! $party->isLeader(Auth::id())) {
            throw new RuntimeException('Только лидер может приглашать игроков.');
        }

        if ($party->isFull()) {
            throw new RuntimeException('Группа заполнена.');
        }

        if ($this->partyRepository->isMember($partyId, $targetUserId)) {
            throw new RuntimeException('Игрок уже в группе.');
        }

        if ($this->partyRepository->findActiveByUser($targetUserId) !== null) {
            throw new RuntimeException('Игрок уже состоит в другой группе.');
        }

        $this->partyRepository->addMember($partyId, $targetUserId);
    }

    /** Приглашение по нику персонажа. */
    public function inviteByName(int $partyId, string $name): void
    {
        $target = User::whereHas('player', fn ($q) => $q->where('name', $name))
            ->orWhere('name', $name)
            ->first();

        if ($target === null) {
            throw new RuntimeException('Игрок с таким именем не найден.');
        }

        $this->invite($partyId, $target->id);
    }

    public function kick(int $partyId, int $targetUserId): void
    {
        $party = $this->partyRepository->findOpenById($partyId);

        if ($party === null) {
            throw new RuntimeException('Группа не найдена.');
        }

        if (! $party->isLeader(Auth::id())) {
            throw new RuntimeException('Только лидер может исключать игроков.');
        }

        if ($party->isLeader($targetUserId)) {
            throw new RuntimeException('Лидер не может исключить сам себя. Распустите группу.');
        }

        if (! $this->partyRepository->isMember($partyId, $targetUserId)) {
            throw new RuntimeException('Игрок не состоит в группе.');
        }

        $this->partyRepository->removeMember($partyId, $targetUserId);
    }

    public function leave(int $partyId): void
    {
        $userId = Auth::id();
        $party = $this->partyRepository->findOpenById($partyId)
            ?? Party::find($partyId);

        if ($party === null) {
            throw new RuntimeException('Группа не найдена.');
        }

        if ($party->isLeader($userId)) {
            $this->partyRepository->disband($party);

            return;
        }

        $this->partyRepository->removeMember($partyId, $userId);
    }

    public function disband(int $partyId): void
    {
        $party = Party::findOrFail($partyId);

        if (! $party->isLeader(Auth::id())) {
            throw new RuntimeException('Только лидер может распустить группу.');
        }

        $this->partyRepository->disband($party);
    }

    public function getMyParty(): ?Party
    {
        return $this->partyRepository->findActiveByUser(Auth::id())
            ?->load('leader.player', 'members.user.player', 'members.user.clanMembership.clan');
    }
}
