<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Services;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemGem;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemRune;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerEquipment;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;

/**
 * Loads every occupied equipment slot and all data needed for stat calculation
 * with a fixed number of queries instead of one query per slot.
 */
final class PlayerEquipmentLoader
{
    public function load(Player $player): ?PlayerEquipment
    {
        $player->loadMissing('playerEquip');

        /** @var PlayerEquipment|null $equipment */
        $equipment = $player->getRelation('playerEquip');
        if ($equipment === null || $this->isFullyLoaded($equipment)) {
            return $equipment;
        }

        $itemIds = collect(PlayerEquipment::ITEM_SLOT_RELATIONS)
            ->map(static fn (string $column): ?int => $equipment->getAttribute($column) !== null
                ? (int) $equipment->getAttribute($column)
                : null)
            ->filter()
            ->unique()
            ->values();

        if ($itemIds->isEmpty()) {
            foreach (PlayerEquipment::ITEM_SLOT_RELATIONS as $relation => $_column) {
                $equipment->setRelation($relation, null);
            }

            return $equipment;
        }

        $items = Item::query()
            ->without('itemInfo')
            ->whereKey($itemIds->all())
            ->get()
            ->keyBy('id');

        $itemGems = ItemGem::query()
            ->whereIn('item_id', $itemIds->all())
            ->orderBy('socket_index')
            ->get();

        $itemRunes = ItemRune::query()
            ->whereIn('item_id', $itemIds->all())
            ->orderBy('slot_index')
            ->get();

        $shareItemIds = $items->pluck('share_item_id')
            ->merge($itemGems->pluck('share_item_id'))
            ->merge($itemRunes->pluck('share_item_id'))
            ->filter()
            ->unique()
            ->values();

        $shareItems = ShareItem::query()
            ->with('stats')
            ->whereKey($shareItemIds->all())
            ->get()
            ->keyBy('id');

        foreach ($itemGems as $itemGem) {
            $itemGem->setRelation('gemInfo', $shareItems->get((int) $itemGem->share_item_id));
        }

        foreach ($itemRunes as $itemRune) {
            $itemRune->setRelation('runeInfo', $shareItems->get((int) $itemRune->share_item_id));
        }

        foreach ($items as $item) {
            $item->setRelation('itemInfo', $shareItems->get((int) $item->share_item_id));
            $item->setRelation(
                'gems',
                new EloquentCollection($itemGems->where('item_id', $item->id)->values()->all()),
            );
            $item->setRelation(
                'runes',
                new EloquentCollection($itemRunes->where('item_id', $item->id)->values()->all()),
            );
        }

        foreach (PlayerEquipment::ITEM_SLOT_RELATIONS as $relation => $column) {
            $itemId = $equipment->getAttribute($column);
            $equipment->setRelation($relation, $itemId !== null ? $items->get((int) $itemId) : null);
        }

        return $equipment;
    }

    private function isFullyLoaded(PlayerEquipment $equipment): bool
    {
        foreach (PlayerEquipment::ITEM_SLOT_RELATIONS as $relation => $_column) {
            if (! $equipment->relationLoaded($relation)) {
                return false;
            }

            $item = $equipment->getRelation($relation);
            if (! $item instanceof Item) {
                continue;
            }

            if (! $item->relationLoaded('itemInfo')
                || ! $item->relationLoaded('gems')
                || ! $item->relationLoaded('runes')) {
                return false;
            }

            $itemInfo = $item->getRelation('itemInfo');
            if (! $itemInfo instanceof ShareItem || ! $itemInfo->relationLoaded('stats')) {
                return false;
            }

            foreach ($item->getRelation('gems') as $gem) {
                if (! $gem->relationLoaded('gemInfo')) {
                    return false;
                }
            }

            foreach ($item->getRelation('runes') as $rune) {
                if (! $rune->relationLoaded('runeInfo')) {
                    return false;
                }
            }
        }

        return true;
    }
}
