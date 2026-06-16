<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Domain\Contracts\DungeonSessionRepository;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;

class GetActiveDungeonSession
{
    public function __construct(
        private readonly DungeonSessionRepository $sessionRepository,
    ) {}

    public function execute(int $userId): ?DungeonSession
    {
        return $this->sessionRepository->findByUserId($userId);
    }
}
