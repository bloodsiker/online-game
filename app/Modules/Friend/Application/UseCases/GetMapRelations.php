<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\UseCases;

use App\Modules\Friend\Application\DTOs\MapRelationsDTO;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Friend\Domain\Enums\PlayerRelationshipType;
use App\Modules\User\Infrastructure\Persistence\Models\User;

/** Клан, принятые друзья и враги игрока — чтобы карта красила остальных по отношению к нему. */
final class GetMapRelations
{
    public function __construct(private readonly FriendRelationshipRepository $relationships) {}

    public function execute(?User $user): MapRelationsDTO
    {
        $playerId = (int) ($user?->player_id ?? 0);
        if ($user === null || $playerId <= 0) {
            return MapRelationsDTO::empty();
        }

        $user->loadMissing('clanMembership');

        return new MapRelationsDTO(
            clanId: $user->clanMembership?->clan_id !== null ? (int) $user->clanMembership->clan_id : null,
            friendUserIds: $this->relationships->getTargetUserIds(playerId: $playerId, type: PlayerRelationshipType::FRIEND, status: 'accepted'),
            enemyUserIds: $this->relationships->getTargetUserIds(playerId: $playerId, type: PlayerRelationshipType::ENEMY),
        );
    }
}
