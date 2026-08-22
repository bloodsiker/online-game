<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\UseCases;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Battle\Application\Services\Battle\BattleService;
use App\Modules\Dungeon\Application\UseCases\GetActiveDungeonSession;
use App\Modules\Location\Application\DTOs\LocationPageDTO;
use App\Modules\Location\Application\Mappers\LocationPageViewMapper;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationGate;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class PassThroughGate
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly BattleService $battleService,
        private readonly LocationReadRepository $readRepository,
        private readonly PlayerStatService $statService,
        private readonly GetActiveDungeonSession $getActiveDungeonSession,
        private readonly LocationPageViewMapper $mapper,
    ) {}

    public function execute(User $user, int $gateId): LocationPageDTO
    {
        $gate = LocationGate::find($gateId);

        if ($gate === null || $gate->mode !== 'presence_pass' || $gate->from_location_id !== $user->location_id) {
            session()->flash('message', 'Проход недоступен.');
        } elseif ($gate->shareItem === null || $this->backpackService->getItem($user, $gate->shareItem) === null) {
            $itemName = $gate->shareItem?->name ?? 'специальный предмет';
            session()->flash('message', "Проход закрыт. Нужен предмет: {$itemName}.");
        } else {
            $user->prev_location_id = $user->location_id;
            $user->location_id = $gate->to_location_id;
            $user->save();
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
            $this->readRepository->countItemsOnLocation($user, $location->id),
        );
    }
}
