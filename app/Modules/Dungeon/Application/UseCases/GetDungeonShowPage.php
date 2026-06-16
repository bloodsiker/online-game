<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\DTOs\DungeonShowPageDTO;
use App\Modules\Dungeon\Application\Mappers\DungeonViewMapper;
use App\Modules\Dungeon\Domain\Contracts\DungeonReadRepository;

class GetDungeonShowPage
{
    public function __construct(
        private readonly DungeonReadRepository $readRepository,
        private readonly GetActiveDungeonSession $getActiveDungeonSession,
        private readonly DungeonViewMapper $mapper,
    ) {}

    public function execute(int $userId, int $dungeonId): DungeonShowPageDTO
    {
        return $this->mapper->mapShow(
            $this->readRepository->findActiveByIdOrFail($dungeonId),
            $this->getActiveDungeonSession->execute($userId),
        );
    }
}
