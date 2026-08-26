<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Player\Domain\Services\PlayerStatService;
use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradePageDTO;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Support\Collection;

class UpgradePageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
        private readonly PlayerStatService $statService,
        private readonly UpgradeService $upgradeService,
    ) {}

    /**
     * @param  Collection<int, mixed>  $items
     * @param  Collection<int, mixed>  $baseScrolls
     * @param  Collection<int, mixed>  $bonusScrolls
     */
    public function map(Structure $blacksmith, mixed $player, Collection $items, Collection $baseScrolls, Collection $bonusScrolls): UpgradePageDTO
    {
        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($baseScrolls))
            ->collectFrom(new BackpackItemTooltipStrategy($bonusScrolls))
            ->renderScript();

        $itemViews = $items->map(function ($slot) {
            $lvl = $slot->item->upgrade_lvl;
            $pity = $slot->item->upgrade_pity;

            return [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'level' => $lvl,
                'pity' => $pity,
                'failStreak' => $slot->item->upgrade_fail_streak,
                'successChance' => $this->upgradeService->getSuccessChance($lvl, $pity, false),
                'successChanceLucky' => $this->upgradeService->getSuccessChance($lvl, $pity, true),
                'destroyChance' => $this->upgradeService->getDestroyChance($lvl),
                'cost' => $this->upgradeService->getGoldCost($lvl),
                'isMax' => $lvl >= 15,
            ];
        })->values()->all();

        $baseScrollViews = $baseScrolls->map(fn ($scroll) => [
            'id' => $scroll->item->id,
            'name' => $scroll->item->itemInfo->name,
            'image' => $scroll->item->itemInfo->image,
            'count' => $scroll->count,
        ])->values()->all();

        $bonusScrollViews = $bonusScrolls->map(fn ($scroll) => [
            'id' => $scroll->item->id,
            'name' => $scroll->item->itemInfo->name,
            'image' => $scroll->item->itemInfo->image,
            'count' => $scroll->count,
            'bonusType' => $scroll->item->itemInfo->upgrade_scroll_type?->value ?? '',
            'description' => $scroll->item->itemInfo->upgrade_scroll_type?->description() ?? '',
        ])->values()->all();

        return new UpgradePageDTO(
            blacksmith: $blacksmith,
            player: $player,
            playerDecorator: $this->statService->resolve($player),
            items: $itemViews,
            baseScrolls: $baseScrollViews,
            bonusScrolls: $bonusScrollViews,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
