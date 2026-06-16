<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\UseCases;

use App\Modules\Battle\Application\Services\Battle\BattleService;
use App\Modules\Dungeon\Application\UseCases\GetActiveDungeonSession;
use App\Modules\Location\Application\DTOs\LocationPageDTO;
use App\Modules\Location\Application\Mappers\LocationPageViewMapper;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\PlayerMovementService;

class MoveToLocation
{
    public function __construct(
        private readonly PlayerMovementService $playerMovementService,
        private readonly BattleService $battleService,
        private readonly LocationReadRepository $readRepository,
        private readonly PlayerStatService $statService,
        private readonly GetActiveDungeonSession $getActiveDungeonSession,
        private readonly LocationPageViewMapper $mapper,
    ) {}

    public function execute(User $user, string $direction): LocationPageDTO
    {
        $result = $this->playerMovementService->move($user, $direction);

        if (! $result->success) {
            session()->flash('message', $result->message);
        }

        $location = $this->readRepository->findLocationOrFail($user->location_id);
        $battle = $this->battleService->battleOnLocation($location);

        return $this->mapper->map(
            $user,
            $location,
            $this->statService->resolve($user->player),
            $battle?->id,
            $this->readRepository->getMonstersOnLocation($location->id),
            $this->readRepository->getLocationUsers($location->id),
            $this->getActiveDungeonSession->execute($user->id),
            $this->readRepository->getItemsOnLocation($user, $location->id)->count(),
        );
    }
}
