<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\UseCases;

use App\Modules\Friend\Application\DTOs\FriendsFrameDTO;
use App\Modules\Friend\Application\Mappers\FriendsViewMapper;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetFriendsFrame
{
    public function __construct(
        private readonly FriendRelationshipRepository $repository,
        private readonly FriendsViewMapper $mapper,
    ) {}

    public function execute(User $user): FriendsFrameDTO
    {
        return $this->mapper->mapFrame(
            $this->repository->getAcceptedFriends((int) $user->player_id)
        );
    }
}
