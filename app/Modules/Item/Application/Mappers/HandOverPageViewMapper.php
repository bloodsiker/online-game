<?php

declare(strict_types=1);

namespace App\Modules\Item\Application\Mappers;

use App\Modules\Item\Application\DTOs\HandOverCandidateDTO;
use App\Modules\Item\Application\DTOs\HandOverPageDTO;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Collection;

class HandOverPageViewMapper
{
    public function map(Item $item, Collection $candidates, bool $isHandedItem, bool $isUserMoved, ?User $toUser): HandOverPageDTO
    {
        return new HandOverPageDTO(
            itemId: (int) $item->id,
            itemName: (string) $item->getName(),
            isHandedItem: $isHandedItem,
            isUserMoved: $isUserMoved,
            toUserName: $toUser?->name,
            candidates: $candidates->map(
                static fn (User $user): HandOverCandidateDTO => new HandOverCandidateDTO(
                    userId: (int) $user->id,
                    name: (string) $user->name,
                    url: route('items.hand_over_to_user', ['id' => $item->id, 'uid' => $user->id]),
                )
            )->all(),
            backpackUrl: route('backpack'),
            locationUrl: route('location'),
            sameItemsUrl: route('backpack', ['sid' => $item->itemInfo->id]),
        );
    }
}
