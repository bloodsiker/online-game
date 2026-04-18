<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\UseCases;

use App\Modules\Friend\Application\DTOs\FriendActionResultDTO;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class AcceptFriend
{
    public function __construct(private readonly FriendRelationshipRepository $repository) {}

    public function execute(User $user, int $relationshipId): FriendActionResultDTO
    {
        $relationship = $this->repository->findRelationshipById($relationshipId);

        if ($relationship === null || $relationship->target_id !== (int) $user->player_id || ! $relationship->isPending()) {
            return new FriendActionResultDTO(false, 'Нет такого запроса.', 'error');
        }

        $relationship->status = 'accepted';
        $this->repository->save($relationship);

        return new FriendActionResultDTO(true, 'Запрос принят.', 'success');
    }
}
