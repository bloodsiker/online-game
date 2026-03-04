<?php

namespace App\Services;

use App\DTO\MoveResultDTO;
use App\Models\User;

final readonly class PlayerMovementService
{
    public function __construct(
        private BackpackService $backpackService,
    ) {
    }

    public function move(User $user, string $direction): MoveResultDTO
    {
        $location = $user->currentLocation;

        if (!$location->$direction) {
            return MoveResultDTO::blocked('Нельзя идти в этом направлении');
        }

        $backpackUsed = $this->backpackService->getCountableItemsCount($user);

        if ($backpackUsed > $user->getBagCount()) {
            return MoveResultDTO::blocked('У вас перегружен рюкзак. Нельзя перемещаться.');
        }

        $capacity = $user->getBagCount();

        $speedModifier = $this->getSpeedModifier($backpackUsed, $capacity);

        $this->applyMove($user, $location->$direction);

        return MoveResultDTO::success(
            speedModifier: $speedModifier
        );
    }

    private function applyMove(User $user, int $newLocationId): void
    {
        $user->prev_location_id = $user->location_id;
        $user->location_id = $newLocationId;
        $user->save();
    }

    private function getSpeedModifier(int $used, int $capacity): float
    {
        $ratio = $capacity > 0 ? $used / $capacity : 2;

        return match (true) {
            $ratio <= 1.0 => 1.0,
            $ratio <= 1.2 => 0.8,
            $ratio <= 1.5 => 0.5,
            default       => 0.2,
        };
    }
}
