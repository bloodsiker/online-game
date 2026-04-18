<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\Mappers;

use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Structure\Blacksmith\Application\DTOs\RunePageDTO;
use App\Modules\Structure\Blacksmith\Domain\Services\RuneService;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Services\ItemTooltip\ItemTooltipCollector;
use Illuminate\Support\Collection;

class RunePageViewMapper
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
        private readonly RuneService $runeService,
    ) {}

    /**
     * @param  Collection<int, mixed>  $items
     * @param  Collection<int, mixed>  $runes
     * @param  Collection<int, mixed>  $runeKeys
     */
    public function map(Structure $blacksmith, Collection $items, Collection $runes, Collection $runeKeys): RunePageDTO
    {
        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($runes))
            ->collectFrom(new BackpackItemTooltipStrategy($runeKeys))
            ->renderScript();

        return new RunePageDTO(
            blacksmith: $blacksmith,
            items: $items->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'upgradeLevel' => $slot->item->upgrade_lvl,
                'runeSlotCount' => $slot->item->rune_slot_count,
                'runes' => $slot->item->runes->map(fn ($rune) => [
                    'slot_index' => $rune->slot_index,
                    'share_item_id' => $rune->share_item_id,
                    'name' => $rune->runeInfo->name,
                    'img' => $rune->runeInfo->image,
                    'rarity' => $rune->runeInfo->rune_rarity?->value ?? 'common',
                    'rarity_label' => $rune->runeInfo->rune_rarity?->label() ?? '',
                    'stats' => $rune->stats,
                    'passive_skill' => $rune->passive_skill,
                    'reroll_count' => $rune->reroll_count,
                    'reroll_cost' => $this->runeService->nextRerollCost($rune, 0),
                ])->values()->all(),
            ])->values()->all(),
            runes: $runes->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'count' => $slot->count,
                'rarity' => $slot->item->itemInfo->rune_rarity?->value ?? 'common',
                'rarity_label' => $slot->item->itemInfo->rune_rarity?->label() ?? '',
            ])->values()->all(),
            runeKeys: $runeKeys->map(fn ($slot) => [
                'id' => $slot->item->id,
                'name' => $slot->item->itemInfo->name,
                'image' => $slot->item->itemInfo->image,
                'count' => $slot->count,
            ])->values()->all(),
            itemTooltipScript: $itemTooltipScript,
        );
    }
}
