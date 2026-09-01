<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Application\DTOs\BreakPageDTO;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use Illuminate\Support\Collection;

class BreakPageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    /**
     * @param  Collection<int, mixed>  $items
     */
    public function map(Structure $blacksmith, Collection $items, ShareItem $crystal): BreakPageDTO
    {
        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new ShareItemTooltipStrategy(collect([$crystal])))
            ->renderScript();

        return new BreakPageDTO(
            blacksmith: $blacksmith,
            items: $items->map(fn ($item) => [
                'itemId' => $item->item->id,
                'name' => $item->item->itemInfo->name,
                'image' => $item->item->itemInfo->transparent_image ?? $item->item->itemInfo->image,
                'rarityColor' => $item->item->itemInfo->rarity->color(),
                'breakCrystal' => $item->item->itemInfo->break_crystal,
            ])->values()->all(),
            crystal: $crystal,
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
