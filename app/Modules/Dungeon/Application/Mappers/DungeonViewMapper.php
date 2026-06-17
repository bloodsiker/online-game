<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Application\Mappers;

use App\Enums\DungeonDeathBehavior;
use App\Modules\Dungeon\Application\DTOs\ActiveDungeonSessionDTO;
use App\Modules\Dungeon\Application\DTOs\DungeonIndexPageDTO;
use App\Modules\Dungeon\Application\DTOs\DungeonShowPageDTO;
use App\Modules\Dungeon\Application\DTOs\DungeonViewDTO;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\Dungeon;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use Illuminate\Support\Collection;

class DungeonViewMapper
{
    /**
     * @param  Collection<int, Dungeon>  $dungeons
     */
    public function mapIndex(Collection $dungeons, ?DungeonSession $activeSession): DungeonIndexPageDTO
    {
        return new DungeonIndexPageDTO(
            dungeons: $dungeons->map(fn (Dungeon $dungeon): DungeonViewDTO => $this->mapDungeon($dungeon))->all(),
            activeSession: $this->mapActiveSession($activeSession),
        );
    }

    public function mapShow(Dungeon $dungeon, ?DungeonSession $activeSession): DungeonShowPageDTO
    {
        return new DungeonShowPageDTO(
            dungeon: $this->mapDungeon($dungeon),
            activeSession: $this->mapActiveSession($activeSession),
        );
    }

    public function mapActiveSession(?DungeonSession $session): ?ActiveDungeonSessionDTO
    {
        if ($session === null) {
            return null;
        }

        return new ActiveDungeonSessionDTO(
            dungeonId: $session->dungeon_id,
            dungeonName: (string) $session->dungeon->name,
            expiresAtTimestamp: $session->expires_at?->timestamp,
            canReenter: $session->dungeon->death_behavior === DungeonDeathBehavior::KICK_CAN_REENTER
                || (int) $session->user?->currentLocation?->dungeon_id !== (int) $session->dungeon_id,
        );
    }

    private function mapDungeon(Dungeon $dungeon): DungeonViewDTO
    {
        return new DungeonViewDTO(
            id: $dungeon->id,
            name: (string) $dungeon->name,
            description: $dungeon->description,
            tier: (int) $dungeon->tier,
            maxPlayers: (int) $dungeon->max_players,
            minLevel: (int) $dungeon->min_level,
            cooldownSeconds: (int) $dungeon->cooldown_seconds,
            cooldownType: $dungeon->cooldown_type?->value,
            timeLimitSeconds: $dungeon->time_limit_seconds,
            requiresKey: $dungeon->requiresKey(),
            entryKeyName: $dungeon->entryItem?->name,
            entryLocationId: $dungeon->entry_location_id,
            deathBehavior: $dungeon->death_behavior->value,
            deathBehaviorLabel: $dungeon->death_behavior->label(),
            deathReturnLocationId: $dungeon->death_return_location_id,
            monsterRespawn: (bool) $dungeon->monster_respawn,
        );
    }
}
