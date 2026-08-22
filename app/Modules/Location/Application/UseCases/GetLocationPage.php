<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\UseCases;

use App\Modules\Battle\Application\Services\Battle\BattleOrchestrator;
use App\Modules\Battle\Application\Services\Battle\MonsterSelector;
use App\Modules\Battle\Infrastructure\Persistence\Models\Battle;
use App\Modules\Dungeon\Application\UseCases\ExpireDungeonSession;
use App\Modules\Location\Application\DTOs\LocationFightDTO;
use App\Modules\Location\Application\DTOs\LocationResultDTO;
use App\Modules\Location\Application\Mappers\LocationPageViewMapper;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetLocationPage
{
    public function __construct(
        private readonly BattleOrchestrator $battleOrchestrator,
        private readonly MonsterSelector $monsterSelector,
        private readonly LocationReadRepository $readRepository,
        private readonly PlayerStatService $statService,
        private readonly ExpireDungeonSession $expireDungeonSession,
        private readonly LocationPageViewMapper $mapper,
    ) {}

    public function execute(User $user): LocationResultDTO
    {
        if ($this->expireDungeonSession->execute($user)) {
            session()->flash('message', 'Время в подземелье истекло! Вы возвращены к входу.');
        }

        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);
        $location = $this->readRepository->findLocationOrFail($user->location_id);

        $battle = $this->battleOrchestrator->handleLocationEntry($location);

        if ($battle instanceof Battle) {
            $randomAttackedMonster = $this->monsterSelector->getRandomActiveMonster($battle);
            if ($randomAttackedMonster?->locationMonster instanceof MonsterOnLocation) {
                $randomAttackedMonster->locationMonster->regenerate();
            }

            return new LocationResultDTO(
                fight: new LocationFightDTO(
                    battle: $battle,
                    randomAttackedMonster: $randomAttackedMonster,
                    player: $player,
                    playerDecorator: $playerDecorator,
                ),
                page: null,
            );
        }

        $dungeonSession = $this->readRepository->findDungeonSessionByUserId($user->id);

        return new LocationResultDTO(
            fight: null,
            page: $this->mapper->map(
                $user,
                $location,
                $playerDecorator,
                null,
                $this->readRepository->getMonstersOnLocation($location->id),
                $this->readRepository->getLocationUsers($location->id),
                $dungeonSession,
                $this->readRepository->countItemsOnLocation($user, $location->id),
            ),
        );
    }
}
