<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Structure\Blacksmith\Application\DTOs\UpgradeTransferPageDTO;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanTransferUpgrade;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeTransferService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Support\Collection;

final readonly class UpgradeTransferPageViewMapper
{
    public function __construct(
        private ItemTooltipCollector $collector,
        private CanTransferUpgrade $canTransferUpgrade,
        private UpgradeTransferService $transferService,
    ) {}

    /**
     * @param  Collection<int, mixed>  $items
     * @param  Collection<int, mixed>  $transgressors
     */
    public function map(Structure $blacksmith, Collection $items, Collection $transgressors): UpgradeTransferPageDTO
    {
        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($transgressors))
            ->renderScript();

        $toView = function ($slot): array {
            $itemInfo = $slot->item->itemInfo;

            return [
                'id' => $slot->item->id,
                'name' => $itemInfo->name,
                'image' => $itemInfo->image,
                'level' => (int) $slot->item->upgrade_lvl,
                'compatibilityKey' => $this->canTransferUpgrade->compatibilityKey($itemInfo) ?? 'incompatible-'.$slot->item->id,
                'typeLabel' => $itemInfo->getTypeName(),
                'slotLabel' => $itemInfo->slot?->label() ?? $itemInfo->getTypeName(),
                'rarityColor' => $itemInfo->rarity->color(),
            ];
        };

        $transgressorVariants = $transgressors
            ->groupBy(fn ($slot): int => (int) $slot->item->share_item_id)
            ->map(function (Collection $slots): array {
                $firstSlot = $slots->first();
                $itemInfo = $firstSlot->item->itemInfo;

                return [
                    'shareItemId' => (int) $itemInfo->id,
                    'tooltipItemId' => (int) $firstSlot->item->id,
                    'name' => $itemInfo->name,
                    'image' => $itemInfo->image,
                    'rarityLabel' => $itemInfo->rarity->label(),
                    'rarityColor' => $itemInfo->rarity->color(),
                    'chance' => $this->transferService->successChance($itemInfo->rarity),
                    'count' => (int) $slots->sum('count'),
                ];
            })
            ->sortBy('chance')
            ->values()
            ->all();

        return new UpgradeTransferPageDTO(
            blacksmith: $blacksmith,
            sourceItems: $items->filter(fn ($slot): bool => (int) $slot->item->upgrade_lvl > 0)->map($toView)->values()->all(),
            targetItems: $items->filter(fn ($slot): bool => (int) $slot->item->upgrade_lvl === 0)->map($toView)->values()->all(),
            transgressors: $transgressorVariants,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
