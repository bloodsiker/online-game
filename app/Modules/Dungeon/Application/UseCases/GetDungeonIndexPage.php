<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\DTOs\DungeonIndexPageDTO;
use App\Modules\Dungeon\Application\Mappers\DungeonViewMapper;
use App\Modules\Dungeon\Domain\Contracts\DungeonReadRepository;

class GetDungeonIndexPage
{
    public function __construct(
        private readonly DungeonReadRepository $readRepository,
        private readonly GetActiveDungeonSession $getActiveDungeonSession,
        private readonly DungeonViewMapper $mapper,
    ) {}

    public function execute(int $userId): DungeonIndexPageDTO
    {
        return $this->mapper->mapIndex(
            $this->readRepository->getActive(),
            $this->getActiveDungeonSession->execute($userId),
        );
    }
}
