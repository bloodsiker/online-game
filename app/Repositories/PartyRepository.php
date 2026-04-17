<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Enums\PartyStatus;
use App\Models\Party\Party;
use App\Models\Party\PartyMember;

class PartyRepository extends AbstractRepository
{
    public function getModelClass(): string
    {
        return Party::class;
    }

    public function findActiveByUser(int $userId): ?Party
    {
        return Party::whereHas('members', fn ($q) => $q->where('user_id', $userId))
            ->whereIn('status', [PartyStatus::OPEN, PartyStatus::IN_DUNGEON])
            ->first();
    }

    public function findOpenById(int $partyId): ?Party
    {
        return Party::where('id', $partyId)
            ->where('status', PartyStatus::OPEN)
            ->first();
    }

    public function createParty(int $leaderId, int $maxSize): Party
    {
        /** @var Party $party */
        $party = Party::create([
            'leader_user_id' => $leaderId,
            'status' => PartyStatus::OPEN,
            'max_size' => $maxSize,
        ]);

        PartyMember::create([
            'party_id' => $party->id,
            'user_id' => $leaderId,
        ]);

        return $party;
    }

    public function addMember(int $partyId, int $userId): PartyMember
    {
        return PartyMember::create([
            'party_id' => $partyId,
            'user_id' => $userId,
        ]);
    }

    public function removeMember(int $partyId, int $userId): void
    {
        PartyMember::where('party_id', $partyId)
            ->where('user_id', $userId)
            ->delete();
    }

    public function isMember(int $partyId, int $userId): bool
    {
        return PartyMember::where('party_id', $partyId)
            ->where('user_id', $userId)
            ->exists();
    }

    public function disband(Party $party): void
    {
        PartyMember::where('party_id', $party->id)->delete();
        $party->update(['status' => PartyStatus::DISBANDED]);
    }
}
