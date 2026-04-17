<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Party\Party;
use App\Repositories\PartyRepository;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class PartyService
{
    public function __construct(
        private readonly PartyRepository $partyRepository,
    ) {}

    public function createParty(int $maxSize = 4): Party
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
            ?->load('members.user.player');
    }
}
