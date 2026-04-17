<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Models\Structure;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Structure\Blacksmith\Application\DTOs\GemPageDTO;
use App\Services\ItemTooltip\ItemTooltipCollector;
use Illuminate\Support\Collection;

class GemPageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
    ) {}

    /**
     * @param  Collection<int, mixed>  $items
     * @param  Collection<int, mixed>  $gems
     * @param  Collection<int, mixed>  $socketKits
     */
    public function map(Structure $blacksmith, Collection $items, Collection $gems, Collection $socketKits): GemPageDTO
    {
        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($gems))
            ->collectFrom(new BackpackItemTooltipStrategy($socketKits))
            ->renderScript();

        return new GemPageDTO(
            blacksmith: $blacksmith,
            items: $items->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'upgradeLevel' => $slot->item->upgrade_lvl,
                'socketCount' => $slot->item->socket_count,
                'gemsCount' => $slot->item->gems->count(),
                'gems' => $slot->item->gems->map(fn ($gem) => [
                    'socket_index' => $gem->socket_index,
                    'share_item_id' => $gem->share_item_id,
                    'name' => $gem->gemInfo->name,
                    'img' => $gem->gemInfo->image,
                    'stats' => $gem->gemInfo->gem_stats ?? [],
                ])->values()->all(),
            ])->values()->all(),
            gems: $gems->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'count' => $slot->count,
                'stats' => $slot->item->itemInfo->gem_stats ?? [],
            ])->values()->all(),
            socketKits: $socketKits->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'count' => $slot->count,
            ])->values()->all(),
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
