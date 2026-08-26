<?php

declare(strict_types=1);

namespace App\Modules\Friend\Application\UseCases;

use App\Modules\Friend\Application\DTOs\FriendActionResultDTO;
use App\Modules\Friend\Domain\Contracts\FriendRelationshipRepository;
use App\Modules\Friend\Domain\Enums\PlayerRelationshipType;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class AddFriend
{
    public function __construct(private readonly FriendRelationshipRepository $repository) {}

    public function execute(User $user, string $name): FriendActionResultDTO
    {
        $playerId = (int) $user->player_id;
        $name = trim($name);
        $target = $this->repository->findTargetPlayerByName($name);

        if ($target === null) {
            return new FriendActionResultDTO(false, 'Персонаж не найден.', 'error');
        }

        if ($target->id === $playerId) {
            return new FriendActionResultDTO(false, 'Нельзя добавить себя.', 'error');
        }

        if ($this->repository->existsRelationship($playerId, $target->id, PlayerRelationshipType::FRIEND)) {
            return new FriendActionResultDTO(false, 'Запрос уже отправлен или игрок уже в друзьях.', 'error');
        }

        $this->repository->createRelationship($playerId, $target->id, PlayerRelationshipType::FRIEND, 'pending');

        return new FriendActionResultDTO(true, 'Запрос дружбы отправлен.', 'success');
    }
}
