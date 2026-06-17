<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\UseCases;

use App\Modules\Dungeon\Application\Services\DungeonCoordinator;
use App\Modules\Dungeon\Domain\Contracts\DungeonReadRepository;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\PartyService;

class EnterDungeon
{
    public function __construct(
        private readonly DungeonReadRepository $readRepository,
        private readonly DungeonCoordinator $coordinator,
        private readonly PartyService $partyService,
    ) {}

    public function execute(User $user, int $dungeonId): void
    {
        $dungeon = $this->readRepository->findByIdOrFail($dungeonId);
        if ($this->coordinator->resumeExistingSessionIfAllowed($dungeon, $user) !== null) {
            return;
        }

        $party = $this->partyService->getMyParty();

        if ($dungeon->isGroup() && $party !== null) {
            $this->coordinator->enterWithParty($dungeon, $user, $party);

            return;
        }

        $this->coordinator->enterSolo($dungeon, $user);
    }
}
