<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Services;

use App\Modules\Event\Application\DTOs\WorldEventCollectionResult;
use App\Modules\Event\Domain\Enums\WorldEventObjectiveType;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;

final class WorldEventCollectionService
{
    public function __construct(private readonly WorldEventProgressService $progressService) {}

    public function collect(User $user, ItemOnLocation $slot): ?WorldEventCollectionResult
    {
        if ($slot->world_event_run_id === null) {
            return null;
        }

        $result = $this->progressService->advance(
            user: $user,
            runId: (int) $slot->world_event_run_id,
            objectiveType: WorldEventObjectiveType::COLLECT,
            targetId: (int) $slot->item->share_item_id,
            amount: max(1, (int) $slot->count),
        );

        if ($result->allowed && $result->targetExpiresAt !== null) {
            $slot->item->expires_at = $result->targetExpiresAt;
            $slot->item->save();
        }

        return $result;
    }
}
