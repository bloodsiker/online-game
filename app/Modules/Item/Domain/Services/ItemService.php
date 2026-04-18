<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Enums\ShareItemSlot;
use App\Enums\ShareItemType;
use App\Models\Dungeon\DungeonSession;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemInChest;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\HotbarService;
use App\Services\ItemRequirementService;
use Illuminate\Support\Facades\DB;

class ItemService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly HotbarService $hotbarService,
        private readonly ItemRequirementService $requirementService,
    ) {}

    public function pickUpFromLocation(User $user, int $itemId): string
    {
        return DB::transaction(function () use ($user, $itemId) {
            $location = $user->currentLocation;
            $query = ItemOnLocation::where('location_id', $location->id)->where('item_id', $itemId);

            if ($location->dungeon_id !== null) {
                $session = DungeonSession::where('user_id', $user->id)->first();
                $dungeonSessionId = $session?->monsterSessionId();
                $dungeonSessionId !== null
                    ? $query->where('dungeon_session_id', $dungeonSessionId)
                    : $query->whereNull('dungeon_session_id');
            } else {
                $query->whereNull('dungeon_session_id');
            }

            $slot = $query->lockForUpdate()->first();

            if (! $slot) {
                $name = Item::find($itemId)?->itemInfo->name ?? 'предмет';

                return sprintf('Кто-то уже поднял предмет <b>"%s"</b>...', $name);
            }

            $item = $slot->item;
            $count = $slot->count;
            $slot->delete();

            $this->addPickedItemToBackpack($user, $item, $count);

            return sprintf('Вы подняли предмет <b>"%s"</b>...', $item->itemInfo->name);
        });
    }

    public function drop(User $user, int $itemId, int $qty): void
    {
        $backpackItem = Backpack::where('user_id', $user->id)
            ->where('item_id', $itemId)
            ->first();

        if (! $backpackItem) {
            return;
        }

        $qty = max(1, min($qty, $backpackItem->count));

        if ($qty >= $backpackItem->count) {
            $backpackItem->delete();
        } else {
            $backpackItem->count -= $qty;
            $backpackItem->save();
        }

        $user->currentLocation->itemsOnLocation()->attach($itemId, ['count' => $qty]);
    }

    public function handOver(User $user, Item $item, User $toUser): ?string
    {
        if ($user->location_id !== $toUser->location_id) {
            return sprintf('Персонаж %s не находиться рядом возле вас', $toUser->name);
        }

        $backpackItem = Backpack::where('user_id', $user->id)
            ->where('item_id', $item->id)
            ->first();

        if (! $backpackItem) {
            return null;
        }

        if ($backpackItem->isEquipped()) {
            return 'Не возможно передать, предмет надет на персонажа';
        }

        $backpackItem->delete();

        Backpack::create([
            'item_id' => $item->id,
            'user_id' => $toUser->id,
            'count' => 1,
        ]);

        return null;
    }

    public function openChest(Item $item): void
    {
        if ($item->itemInfo->type !== ShareItemType::CHEST) {
            return;
        }

        foreach ($item->itemInfo->itemHasItems as $hasItem) {
            $chance = mt_rand(0, 100000) / 1000;
            if ($chance <= $hasItem->pivot->drop_chance) {
                $drop = Item::create(['share_item_id' => $hasItem->id]);
                $count = mt_rand($hasItem->pivot->min_count, $hasItem->pivot->max_count);
                $item->itemsInChest()->attach($drop->id, ['count' => $count]);
            }
        }

        $item->is_open = 1;
        $item->save();
    }

    public function pickUpFromChest(User $user, Item $chest, int $itemId): string
    {
        if (! $chest->itemsInChest()->count()) {
            return '';
        }

        $slot = ItemInChest::where('item_id', $itemId)->first();
        $item = Item::find($itemId);

        if (! $slot || ! $item) {
            return sprintf('Кто-то уже поднял предмет <b>"%s"</b>...', $item?->itemInfo->name ?? 'предмет');
        }

        $count = $slot->count;
        $slot->delete();

        $this->addPickedItemToBackpack($user, $item, $count);

        if (! $chest->itemsInChest()->count()) {
            $chest->delete();
        }

        return sprintf('Вы подняли предмет <b>"%s"</b>...', $item->itemInfo->name);
    }

    public function equip(User $user, int $itemId): ?string
    {
        $backpackItem = Backpack::where('item_id', $itemId)
            ->where('user_id', $user->id)
            ->first();

        if (! $backpackItem || $backpackItem->equipped === 1) {
            return null;
        }

        $shareItem = $backpackItem->item->itemInfo;
        $error = $this->requirementService->check($user->player, $shareItem);
        if ($error) {
            return $error;
        }

        $playerEquip = $user->player->playerEquip;
        $typeItem = $shareItem->type;
        $slot = $shareItem->slot;
        $itemId = $backpackItem->item->id;

        if ($slot === ShareItemSlot::HAND) {
            if ($typeItem === ShareItemType::WEAPON && $playerEquip->hand_left && $playerEquip->hand_right) {
                return 'Слот занят';
            }
            if ($typeItem === ShareItemType::SHIELD && $playerEquip->hand_right) {
                return 'Слот занят';
            }

            if (! $playerEquip->hand_left && $typeItem === ShareItemType::WEAPON) {
                $playerEquip->hand_left = $itemId;
            } elseif (! $playerEquip->hand_right && $playerEquip->hand_left !== $itemId
                && in_array($typeItem, [ShareItemType::WEAPON, ShareItemType::SHIELD], true)) {
                $playerEquip->hand_right = $itemId;
            } else {
                return null;
            }

            $playerEquip->save();
            $backpackItem->equipped = 1;
            $backpackItem->save();

            return null;
        }

        if (in_array($slot, ShareItemSlot::armorSlots(), true)) {
            $slotName = $slot->value;
            if ($playerEquip->$slotName) {
                return 'Слот занят';
            }
            $playerEquip->$slotName = $itemId;
            $playerEquip->save();
            $backpackItem->equipped = 1;
            $backpackItem->save();

            return null;
        }

        if ($slot === ShareItemSlot::BELT && $typeItem === ShareItemType::BELT) {
            if (! $playerEquip->belt_first) {
                $playerEquip->belt_first = $itemId;
            } elseif (! $playerEquip->belt_second) {
                $playerEquip->belt_second = $itemId;
            } else {
                return 'Слоты для пояса заняты';
            }
            $playerEquip->save();
            $backpackItem->equipped = 1;
            $backpackItem->save();

            return null;
        }

        if ($slot === ShareItemSlot::BAG && $typeItem === ShareItemType::BAG) {
            if (! $playerEquip->bag_first) {
                $playerEquip->bag_first = $itemId;
            } elseif (! $playerEquip->bag_second) {
                $playerEquip->bag_second = $itemId;
            } else {
                return 'Слоты для сумки заняты';
            }
            $playerEquip->save();
            $backpackItem->equipped = 1;
            $backpackItem->save();

            return null;
        }

        return null;
    }

    public function unequip(User $user, int $itemId): void
    {
        $backpackItem = Backpack::where('item_id', $itemId)
            ->where('user_id', $user->id)
            ->first();

        if (! $backpackItem || $backpackItem->equipped === 0) {
            return;
        }

        $playerEquip = $user->player->playerEquip;
        $slot = $backpackItem->item->itemInfo->slot;
        $itemId = $backpackItem->item->id;

        if ($slot === ShareItemSlot::HAND) {
            if ($playerEquip->hand_left === $itemId) {
                $playerEquip->hand_left = null;
            } elseif ($playerEquip->hand_right === $itemId) {
                $playerEquip->hand_right = null;
            }
            $playerEquip->save();
            $backpackItem->equipped = 0;
            $backpackItem->save();

            return;
        }

        if (in_array($slot, ShareItemSlot::armorSlots(), true)) {
            $slotName = $slot->value;
            if ($itemId === $playerEquip->$slotName) {
                $playerEquip->$slotName = null;
                $playerEquip->save();
                $backpackItem->equipped = 0;
                $backpackItem->save();
            }

            return;
        }

        if ($slot === ShareItemSlot::BELT) {
            if ($playerEquip->belt_first === $itemId) {
                $playerEquip->belt_first = null;
            } elseif ($playerEquip->belt_second === $itemId) {
                $playerEquip->belt_second = null;
            }
            $playerEquip->save();
            $backpackItem->equipped = 0;
            $backpackItem->save();
            $this->hotbarService->trimExcessSlots($user->player);

            return;
        }

        if ($slot === ShareItemSlot::BAG) {
            if ($playerEquip->bag_first === $itemId) {
                $playerEquip->bag_first = null;
            } elseif ($playerEquip->bag_second === $itemId) {
                $playerEquip->bag_second = null;
            }
            $playerEquip->save();
            $backpackItem->equipped = 0;
            $backpackItem->save();
        }
    }

    private function addPickedItemToBackpack(User $user, Item $item, int $count): void
    {
        if ($item->itemInfo->type === ShareItemType::RESOURCE) {
            $existing = $this->backpackService->getItem($user, $item->itemInfo);
            if ($existing) {
                $existing->increment('count', $count);
                if ($existing->item_id !== $item->id) {
                    $item->delete();
                }

                return;
            }
        }

        Backpack::create([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'count' => $count,
        ]);
    }
}
