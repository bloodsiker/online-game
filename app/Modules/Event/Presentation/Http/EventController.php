<?php

declare(strict_types=1);

namespace App\Modules\Event\Presentation\Http;

use App\Modules\Event\Application\UseCases\GetActivityCards;
use App\Modules\Event\Application\UseCases\GetPlayerMapInfluences;
use App\Modules\Event\Application\UseCases\GetWorldEventCards;
use App\Modules\Event\Domain\Enums\ActivityPeriod;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class EventController
{
    public function __construct(
        private readonly GetActivityCards $getActivityCards,
        private readonly ItemTooltipCollector $tooltipCollector,
        private readonly GetWorldEventCards $getWorldEventCards,
        private readonly GetPlayerMapInfluences $getPlayerMapInfluences,
    ) {}

    public function index(Request $request): View
    {
        $mode = (string) $request->query('mode', 'events');
        if (! in_array($mode, ['events', 'events_future', 'events_my', 'influence', 'activity', 'rewards'], strict: true)) {
            $mode = 'events';
        }

        $period = ActivityPeriod::tryFrom((string) $request->query('group', 'daily')) ?? ActivityPeriod::DAILY;
        $activities = $this->getActivityCards->execute($request->user()->id, $period);
        $worldEventCards = in_array($mode, ['activity', 'rewards', 'influence'], strict: true)
            ? collect()
            : $this->getWorldEventCards->execute($request->user()->id, $mode);
        $mapInfluences = $mode === 'influence'
            ? $this->getPlayerMapInfluences->execute($request->user()->id)
            : collect();
        $rewardItemIds = $activities->pluck('rewardItemId')
            ->merge($worldEventCards->flatMap(
                fn (array $card) => $card['stage']?->targets?->pluck('share_item_id') ?? collect(),
            ))
            ->filter()
            ->unique()
            ->values();

        $rewardItems = $rewardItemIds->isEmpty()
            ? collect()
            : ShareItem::query()
                ->whereIn('id', $rewardItemIds)
                ->get();

        $this->tooltipCollector->collectFrom(new ShareItemTooltipStrategy($rewardItems));

        return view('event::index', [
            'mode' => $mode,
            'group' => $period->value,
            'activities' => $activities,
            'worldEventCards' => $worldEventCards,
            'mapInfluences' => $mapInfluences,
            'itemTooltipScript' => $this->tooltipCollector->renderScript(),
        ]);
    }

    public function state(Request $request): JsonResponse
    {
        $cards = $this->getWorldEventCards->execute($request->user()->id, 'events');

        return response()->json([
            'events' => $cards->map(fn (array $card): array => [
                'runId' => $card['run']->id,
                'stageId' => $card['stage']?->id,
                'stageTitle' => $card['stage']?->title,
                'stagePosition' => $card['stage']?->position,
                'stageCount' => $card['stageCount'],
                'collected' => (int) $card['run']->collected_count,
                'globalLimit' => (int) $card['stage']?->global_limit,
                'remaining' => max(0, (int) $card['stage']?->global_limit - (int) $card['run']->collected_count),
                'globalPercent' => $card['globalPercent'],
                'playerProgress' => $card['progress'],
                'playerLimit' => (int) $card['stage']?->player_limit,
                'playerPercent' => $card['playerPercent'],
                'mapInfluence' => $card['mapInfluence'],
            ])->values(),
        ]);
    }
}
