<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\UseCases;

use App\Modules\Friend\Application\DTOs\FriendActionResultDTO;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Friend\Domain\Enums\PlayerRelationshipType;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class AddIgnore
{
    public function __construct(private readonly FriendRelationshipRepository $repository) {}

    public function execute(User $user, string $name): FriendActionResultDTO
    {
        $playerId = (int) $user->player_id;
        $target = $this->repository->findTargetPlayerByName(trim($name));

        if ($target === null || $target->id === $playerId) {
            return new FriendActionResultDTO(false, 'Персонаж не найден.', 'error');
        }

        $this->repository->firstOrCreateRelationship($playerId, $target->id, PlayerRelationshipType::IGNORE);

        return new FriendActionResultDTO(true, 'Игрок добавлен в игнор-лист.', 'success');
    }
}
