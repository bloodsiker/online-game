<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\UseCases;

use App\Modules\Friend\Application\DTOs\FriendActionResultDTO;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class RemoveEnemy
{
    public function __construct(private readonly FriendRelationshipRepository $repository) {}

    public function execute(User $user, int $relationshipId): FriendActionResultDTO
    {
        $relationship = $this->repository->findRelationshipById($relationshipId);

        if ($relationship === null || $relationship->player_id !== (int) $user->player_id) {
            return new FriendActionResultDTO(false, '', 'error');
        }

        $this->repository->delete($relationship);

        return new FriendActionResultDTO(true, 'Удалён из врагов.', 'success');
    }
}
