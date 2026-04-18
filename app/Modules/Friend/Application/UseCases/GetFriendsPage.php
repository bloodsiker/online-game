<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\UseCases;

use App\Modules\Friend\Application\DTOs\FriendsPageDTO;
use App\Modules\Friend\Application\Mappers\FriendsViewMapper;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetFriendsPage
{
    public function __construct(
        private readonly FriendRelationshipRepository $repository,
        private readonly FriendsViewMapper $mapper,
    ) {}

    public function execute(User $user): FriendsPageDTO
    {
        $playerId = (int) $user->player_id;

        return $this->mapper->mapPage(
            $this->repository->getAcceptedFriends($playerId),
            $this->repository->getPendingOutgoingFriends($playerId),
            $this->repository->getPendingIncomingFriends($playerId),
            $this->repository->getEnemies($playerId),
            $this->repository->getIgnores($playerId),
        );
    }
}
