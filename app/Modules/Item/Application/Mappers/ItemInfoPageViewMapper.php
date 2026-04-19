<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Mappers;

use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Item\Application\DTOs\ItemInfoPageDTO;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;

class ItemInfoPageViewMapper
{
    public function map(Item $item): ItemInfoPageDTO
    {
        return new ItemInfoPageDTO(
            itemId: (int) $item->id,
            shareItemId: (int) $item->itemInfo->id,
            name: (string) $item->getName(),
            image: $item->itemInfo->image ? asset($item->itemInfo->image) : null,
            minAttack: $item->itemInfo->min_attack !== null ? (int) $item->itemInfo->min_attack : null,
            maxAttack: $item->itemInfo->max_attack !== null ? (int) $item->itemInfo->max_attack : null,
            isHandSlot: $item->itemInfo->slot === ShareItemSlot::HAND,
            isTwoHand: (bool) $item->itemInfo->is_two_hand,
            skillName: $item->itemInfo->skill?->name,
            skillLevel: $item->itemInfo->skill_lvl !== null ? (int) $item->itemInfo->skill_lvl : null,
            typeName: (string) $item->itemInfo->getTypeName(),
            handOverUrl: route('items.hand_over', ['id' => $item->id]),
            dropUrl: route('items.drop', ['id' => $item->id]),
            sameItemsUrl: route('backpack', ['sid' => $item->itemInfo->id]),
            backpackUrl: route('backpack'),
        );
    }
}
