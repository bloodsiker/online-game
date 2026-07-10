<?php

declare(strict_types=1);

namespace App\Modules\Event\Application\UseCases;

use App\Modules\Event\Application\DTOs\ActivityCardDTO;
use App\Modules\Event\Application\Mappers\ActivityCardViewMapper;
use App\Modules\Event\Domain\Enums\ActivityPeriod;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivity;
use App\Modules\Event\Infrastructure\Persistence\Models\EventActivityProgress;
use Illuminate\Support\Collection;

class GetActivityCards
{
    public function __construct(
        private readonly ActivityCardViewMapper $mapper,
    ) {}

    /** @return Collection<int, ActivityCardDTO> */
    public function execute(int $userId, ActivityPeriod $period): Collection
    {
        $activities = EventActivity::query()
            ->with(['rewardItem', 'bonusRewardItem'])
            ->where('is_active', true)
            ->where('period', $period)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $progressByActivity = EventActivityProgress::query()
            ->where('user_id', $userId)
            ->whereIn('event_activity_id', $activities->pluck('id'))
            ->where('period_key', $period->currentKey())
            ->pluck('progress', 'event_activity_id');

        return $activities->map(
            fn (EventActivity $activity): ActivityCardDTO => $this->mapper->map(
                activity: $activity,
                progress: (int) ($progressByActivity[$activity->id] ?? 0),
            ),
        );
    }
}
