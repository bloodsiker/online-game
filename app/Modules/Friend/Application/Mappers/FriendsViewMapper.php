<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\Mappers;

use App\Modules\Friend\Application\DTOs\FriendEntryDTO;
use App\Modules\Friend\Application\DTOs\FriendsFrameDTO;
use App\Modules\Friend\Application\DTOs\FriendsPageDTO;
use App\Modules\Friend\Infrastructure\Persistence\Models\PlayerRelationship;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class FriendsViewMapper
{
    /**
     * @param  Collection<int, PlayerRelationship>  $friends
     * @param  Collection<int, PlayerRelationship>  $outgoing
     * @param  Collection<int, PlayerRelationship>  $incoming
     * @param  Collection<int, PlayerRelationship>  $enemies
     * @param  Collection<int, PlayerRelationship>  $ignores
     */
    public function mapPage(
        Collection $friends,
        Collection $outgoing,
        Collection $incoming,
        Collection $enemies,
        Collection $ignores,
    ): FriendsPageDTO {
        return new FriendsPageDTO(
            friends: $this->mapRelationships($friends, false),
            outgoing: $this->mapRelationships($outgoing, false),
            incoming: $this->mapRelationships($incoming, true),
            enemies: $this->mapRelationships($enemies, false),
            ignores: $this->mapRelationships($ignores, false),
        );
    }

    /**
     * @param  Collection<int, PlayerRelationship>  $friends
     */
    public function mapFrame(Collection $friends): FriendsFrameDTO
    {
        return new FriendsFrameDTO($this->mapRelationships($friends, false));
    }

    /**
     * @param  Collection<int, PlayerRelationship>  $relationships
     * @return list<FriendEntryDTO>
     */
    private function mapRelationships(Collection $relationships, bool $usePlayerSide): array
    {
        $onlineThreshold = CarbonImmutable::now()->subMinutes(10);

        return $relationships->map(
            static function (PlayerRelationship $relationship) use ($onlineThreshold, $usePlayerSide): FriendEntryDTO {
                $relatedPlayer = $usePlayerSide ? $relationship->player : $relationship->target;
                $user = $relatedPlayer->user;
                $clan = $user->clanMembership?->clan;
                $isOnline = $user->last_online_at !== null && $user->last_online_at->greaterThan($onlineThreshold);

                return new FriendEntryDTO(
                    relationshipId: $relationship->id,
                    userId: $user->id,
                    userName: $user->name,
                    level: (int) $relatedPlayer->lvl,
                    isOnline: $isOnline,
                    lastOnlineLabel: $user->last_online_at?->diffForHumans(),
                    lastOnlineTime: $user->last_online_at?->format('H:i'),
                    clanName: $clan?->name,
                    clanIconUrl: $clan?->icon ? Storage::disk('public')->url($clan->icon) : null,
                );
            }
        )->values()->all();
    }
}
