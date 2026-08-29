<?php

namespace App\Modules\Location\Domain\Services;

use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonGate;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Location\Application\DTOs\MoveResultDTO;
use App\Modules\Location\Infrastructure\Persistence\Models\GatheringAttempt;
use App\Modules\Location\Infrastructure\Persistence\Models\Location;
use App\Modules\Location\Infrastructure\Persistence\Models\LocationGate;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Domain\Services\PlayerEquipmentLoader;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerLocationAccess;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final readonly class PlayerMovementService
{
    public function __construct(
        private BackpackService $backpackService,
        private PlayerEquipmentLoader $equipmentLoader,
    ) {}

    public function move(User $user, string $direction): MoveResultDTO
    {
        $location = $user->currentLocation;

        if (! $location->$direction) {
            return MoveResultDTO::blocked('Нельзя идти в этом направлении');
        }

        $this->equipmentLoader->load($user->player);
        $backpackUsed = $this->backpackService->getCountableItemsCount($user);

        if ($backpackUsed > $user->getBagCount()) {
            return MoveResultDTO::blocked('У вас перегружен рюкзак. Нельзя перемещаться.');
        }

        $destLocation = Location::find($location->$direction);

        // Check if destination location is locked (quest lock)
        if ($destLocation && $destLocation->is_locked) {
            $hasAccess = PlayerLocationAccess::where('player_id', $user->player->id)
                ->where('location_id', $destLocation->id)
                ->exists();

            if (! $hasAccess) {
                return MoveResultDTO::blocked('Проход закрыт. Для доступа необходимо выполнить квест.');
            }
        }

        // Check dungeon gates
        if ($destLocation) {
            $gate = DungeonGate::where('from_location_id', $location->id)
                ->where('to_location_id', $destLocation->id)
                ->first();

            if ($gate !== null) {
                $blocked = $this->checkGate($gate, $user);
                if ($blocked !== null) {
                    return MoveResultDTO::blocked($blocked);
                }
            }

            $locationGate = LocationGate::where('from_location_id', $location->id)
                ->where('to_location_id', $destLocation->id)
                ->first();

            if ($locationGate !== null) {
                $blocked = $this->checkLocationGate($locationGate, $user);
                if ($blocked !== null) {
                    return MoveResultDTO::blocked($blocked);
                }
            }
        }

        $capacity = $user->getBagCount();
        $speedModifier = $this->getSpeedModifier($backpackUsed, $capacity);

        $this->applyMove($user, $location->$direction);

        return MoveResultDTO::success(speedModifier: $speedModifier);
    }

    private function checkGate(DungeonGate $gate, User $user): ?string
    {
        return match ($gate->unlock_type) {
            'area_cleared' => $this->checkAreaCleared($gate, $user),
            'boss_item' => $this->checkBossItem($gate, $user),
            default => null,
        };
    }

    private function checkAreaCleared(DungeonGate $gate, User $user): ?string
    {
        $session = DungeonSession::where('user_id', $user->id)->first();

        $query = MonsterOnLocation::where('location_id', $gate->from_location_id)
            ->where('active', 1);

        if ($session !== null) {
            $query->where('dungeon_session_id', $session->monsterSessionId());
        } else {
            $query->whereNull('dungeon_session_id');
        }

        if ($query->count() > 0) {
            return 'Проход заблокирован. Уничтожьте всех монстров в этой зоне.';
        }

        return null;
    }

    private function checkBossItem(DungeonGate $gate, User $user): ?string
    {
        if ($gate->boss_share_item_id === null) {
            return null;
        }

        $shareItem = ShareItem::find($gate->boss_share_item_id);
        if ($shareItem === null) {
            return null;
        }

        $hasItem = $this->backpackService->getItem($user, $shareItem) !== null;
        if (! $hasItem) {
            return 'Проход заблокирован. Нужен ключ от босса.';
        }

        return null;
    }

    private function checkLocationGate(LocationGate $gate, User $user): ?string
    {
        if ($gate->mode === 'teleport_use') {
            return 'Проход закрыт. Нужно использовать ключ из инвентаря.';
        }

        $shareItem = ShareItem::find($gate->share_item_id);
        if ($shareItem === null) {
            return null;
        }

        $hasItem = $this->backpackService->getItem($user, $shareItem) !== null;
        if (! $hasItem) {
            return 'Проход закрыт. Нужен специальный предмет.';
        }

        return null;
    }

    private function applyMove(User $user, int $newLocationId): void
    {
        GatheringAttempt::query()->where('player_id', $user->player->id)->delete();
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
            default => 0.2,
        };
    }
}
