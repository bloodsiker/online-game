<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Services;

use App\Modules\Event\Application\DTOs\WorldEventCollectionResult;
use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Monster\Infrastructure\Persistence\Models\MonsterOnLocation;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;

final class WorldEventKillService
{
    public function __construct(private readonly WorldEventProgressService $progressService) {}

    public function progressKill(Player $player, MonsterOnLocation $monster): ?WorldEventCollectionResult
    {
        if ($monster->world_event_run_id === null) {
            return null;
        }

        $player->loadMissing(['user' => static fn ($query) => $query->without('player')]);

        return $this->progressService->advance(
            user: $player->user,
            runId: (int) $monster->world_event_run_id,
            objectiveType: WorldEventObjectiveType::KILL,
            targetId: (int) $monster->monster_id,
        );
    }
}
