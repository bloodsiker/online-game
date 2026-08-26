<?php

declare(strict_types=1);

namespace App\Modules\Party\Infrastructure\Persistence;

use App\Modules\Party\Domain\Contracts\PartyRepositoryInterface;
use App\Modules\Party\Domain\Enums\PartyStatus;
use App\Modules\Party\Infrastructure\Persistence\Models\Party;
use App\Modules\Party\Infrastructure\Persistence\Models\PartyInvite;
use App\Modules\Party\Infrastructure\Persistence\Models\PartyMember;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class EloquentPartyRepository implements PartyRepositoryInterface
{
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
            'max_players' => $maxSize,
            'invite_code' => bin2hex(random_bytes(6)),
        ]);

        PartyMember::create([
            'party_id' => $party->id,
            'user_id' => $leaderId,
        ]);

        return $party;
    }

    public function addMember(int $partyId, int $userId): void
    {
        PartyMember::create([
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

    public function disband(Party $party, PartyStatus $status = PartyStatus::DISBANDED): void
    {
        PartyMember::where('party_id', $party->id)->delete();
        $party->update(['status' => $status]);
    }

    public function getMemberUsers(int $partyId): Collection
    {
        return User::query()
            ->whereIn('id', PartyMember::query()->select('user_id')->where('party_id', $partyId))
            ->get();
    }

    public function createOrRefreshPendingInvite(int $partyId, int $inviterId, int $invitedId): PartyInvite
    {
        /** @var PartyInvite|null $existing */
        $existing = PartyInvite::query()
            ->where('party_id', $partyId)
            ->where('invited_user_id', $invitedId)
            ->where('status', PartyInvite::STATUS_PENDING)
            ->first();

        if ($existing !== null) {
            $existing->update([
                'inviter_user_id' => $inviterId,
                'uuid' => (string) Str::uuid(),
            ]);

            return $existing;
        }

        return PartyInvite::create([
            'party_id' => $partyId,
            'inviter_user_id' => $inviterId,
            'invited_user_id' => $invitedId,
            'uuid' => (string) Str::uuid(),
            'status' => PartyInvite::STATUS_PENDING,
        ]);
    }

    public function findPendingInviteByUuid(string $inviteUuid): ?PartyInvite
    {
        return PartyInvite::query()
            ->where('uuid', $inviteUuid)
            ->where('status', PartyInvite::STATUS_PENDING)
            ->first();
    }

    public function setInviteStatus(PartyInvite $invite, string $status): void
    {
        $invite->update(['status' => $status]);
    }
}
