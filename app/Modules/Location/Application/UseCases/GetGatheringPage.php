<?php

declare(strict_types=1);

namespace App\Modules\Location\Application\UseCases;

use App\Modules\Location\Application\DTOs\GatheringPageDTO;
use App\Modules\Location\Domain\Services\GatheringService;
use App\Modules\User\Infrastructure\Persistence\Models\User;

class GetGatheringPage
{
    public function __construct(
        private readonly GatheringService $gatheringService,
    ) {}

    public function execute(User $user): GatheringPageDTO
    {
        $location = $user->loadMissing('currentLocation.map')->currentLocation;
        $state = $this->gatheringService->state($user);

        return new GatheringPageDTO(
            locationId: (int) $location->id,
            locationName: (string) $location->name,
            locationDescription: (string) ($location->description ?? ''),
            locationImage: $location->image,
            mapId: (int) ($location->map_id ?? 0),
            mapName: (string) ($location->map?->name ?? 'Без карты'),
            enabled: (bool) $state['enabled'],
            message: $state['message'],
            professions: $state['professions'],
            nodes: $state['nodes'],
            activeAttempt: $state['activeAttempt'],
            backUrl: route('location'),
            stateUrl: route('gathering.state'),
            startUrl: route('gathering.start', ['node' => '__NODE__']),
            completeUrl: route('gathering.complete'),
            cancelUrl: route('gathering.cancel'),
        );
    }
}
