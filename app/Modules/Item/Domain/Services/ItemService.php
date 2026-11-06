<?php

declare(strict_types=1);

namespace App\Modules\Item\Domain\Services;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Dungeon\Infrastructure\Persistence\Models\DungeonSession;
use App\Modules\Event\Application\DTOs\WorldEventCollectionResult;
use App\Modules\Event\Domain\Services\WorldEventCollectionService;
use App\Modules\Item\Domain\DTOs\InstantRewardResult;
use App\Modules\Item\Domain\Enums\ItemActionType;
use App\Modules\Item\Domain\Enums\LocationItemInteractionType;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemInChest;
use App\Modules\Item\Infrastructure\Persistence\Models\ItemOnLocation;
use App\Modules\Player\Application\Services\HotbarService;
use App\Modules\Player\Domain\Services\PlayerInjuryService;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerArtifact;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerInjury;
use App\Modules\Quest\Domain\Services\QuestProgressService;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\TreasureHunt\Infrastructure\Persistence\Models\TreasureHunt;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class ItemService
{
    public function __construct(
        private readonly BackpackService $backpackService,
        private readonly HotbarService $hotbarService,
        private readonly PlayerInjuryService $injuryService,
        private readonly ItemRequirementService $requirementService,
        private readonly QuestProgressService $questProgressService,
        private readonly ItemActionLogger $itemActionLogger,
        private readonly InstantItemRewardService $instantRewardService,
        private readonly ?WorldEventCollectionService $worldEventCollectionService = null,
    ) {}

    public function pickUpFromLocation(User $user, int $itemId): string
    {
        $moneyBalance = null;
        $message = DB::transaction(function () use ($user, $itemId, &$moneyBalance) {
            $user = User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $location = $user->currentLocation;
            if ($location === null) {
                return __('Локация недоступна.');
            }
            $query = ItemOnLocation::visible()
                ->where('location_id', $location->id)
                ->where('item_id', $itemId);

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

                return sprintf(__('Кто-то уже поднял предмет <b>"%s"</b>...'), $name);
            }

            if ($slot->isReservedForAnotherUser((int) $user->id)) {
                return __('Эта добыча временно закреплена за другим игроком.');
            }

            $item = $slot->item;

            if ($item->itemInfo->type === ShareItemType::CHEST
                && $slot->interaction_type !== LocationItemInteractionType::PICKUP) {
                return __('Этот сундук нужно открыть прямо на локации.');
            }

            $eventCollection = ($this->worldEventCollectionService ?? app(WorldEventCollectionService::class))->collect($user, $slot);
            if ($eventCollection !== null && ! $eventCollection->allowed) {
                return $eventCollection->error ?? __('Этот предмет события больше нельзя собрать.');
            }

            $count = $slot->count;
            $slot->delete();

            $instantReward = $this->deliverPickedItem($user, $item, $count);

            if ($instantReward !== null) {
                $moneyBalance = (int) $user->money;

                return sprintf(
                    __('Вы получили <b>%s</b> монет.'),
                    number_format($instantReward->amount, 0, '', ' '),
                );
            }

            return sprintf(__('Вы подняли предмет <b>"%s"</b>'), e($item->itemInfo->name));
        });
        if ($moneyBalance !== null) {
            $user->money = $moneyBalance;
        }

        return $message;
    }

    public function drop(User $user, int $itemId, int $qty): ?string
    {
        if ($qty <= 0) {
            return __('Укажите корректное количество.');
        }

        return DB::transaction(function () use ($user, $itemId, $qty): ?string {
            $user = User::without('player')->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $backpackItem = Backpack::query()->where('user_id', $user->id)->where('item_id', $itemId)
                ->orderBy('id')->lockForUpdate()->first();
            if ($backpackItem === null || $backpackItem->count < 1) {
                return __('Предмет больше недоступен.');
            }
            if ($backpackItem->isEquipped()) {
                return __('Невозможно выбросить надетый предмет.');
            }
            $item = $backpackItem->item;
            if (! $item->itemInfo->is_droppable) {
                return __('Этот предмет нельзя выбросить.');
            }
            $qty = min($qty, (int) $backpackItem->count);
            $isQuestItem = $item->itemInfo->type === ShareItemType::QUEST;
            $location = $isQuestItem ? null : $user->currentLocation;
            if (! $isQuestItem && $location === null) {
                return __('Локация недоступна.');
            }
            $fullyRemoved = $qty === (int) $backpackItem->count;
            if (Backpack::query()->whereKey($backpackItem->id)->where('count', '>=', $qty)->decrement('count', $qty) !== 1) {
                throw new \RuntimeException('Inventory changed while dropping an item.');
            }
            if ($fullyRemoved) {
                $backpackItem->delete();
            }
            $this->itemActionLogger->log($user, $item->itemInfo, $item->upgrade_lvl, ItemActionType::DROP, $qty);

            if ($isQuestItem) {
                $this->questProgressService->decreaseCollectProgress($user->player, $item->share_item_id, $qty);
                if ($fullyRemoved && ! Backpack::query()->where('item_id', $itemId)->exists()) {
                    $item->delete();
                }

                return null;
            }

            // Never share a mutable Item between the remaining stack and ground loot.
            $sharedInstance = Backpack::query()->where('item_id', $itemId)->exists()
                || ItemOnLocation::query()->where('item_id', $itemId)->exists();
            $groundItem = $fullyRemoved && ! $sharedInstance ? $item : $this->copySplitItem($item);
            if ($groundItem->itemInfo->type === ShareItemType::TREASURE_MAP) {
                $this->moveTreasureHunt($groundItem, (int) $user->id);
            }
            $slot = new ItemOnLocation;
            $slot->item_id = $groundItem->id;
            $slot->location_id = $location->id;
            $slot->count = $qty;
            $slot->expires_at = $item->itemInfo->groundExpiresAt();
            $slot->interaction_type = LocationItemInteractionType::PICKUP;
            // A discarded item is public loot, including items previously reserved in battle.
            $slot->loot_owner_id = null;
            $slot->loot_reserved_until = null;
            if ($location->dungeon_id !== null) {
                $slot->dungeon_session_id = DungeonSession::where('user_id', $user->id)->first()?->monsterSessionId();
            }
            $slot->save();

            return null;
        });
    }

    public function handOver(User $user, Item $item, User $toUser): ?string
    {
        if ((int) $user->id === (int) $toUser->id) {
            return __('Нельзя передать предмет самому себе.');
        }

        return DB::transaction(function () use ($user, $item, $toUser): ?string {
            $users = collect([$user->id, $toUser->id])->sort()->mapWithKeys(
                fn ($id): array => [$id => User::without('player')->whereKey($id)->lockForUpdate()->firstOrFail()],
            );
            $user = $users->get($user->id);
            $toUser = $users->get($toUser->id);
            if ((int) $user->location_id <= 0 || (int) $user->location_id !== (int) $toUser->location_id) {
                return sprintf(__('Персонаж %s не находиться рядом возле вас'), $toUser->name);
            }
            $backpackItem = Backpack::query()->where('user_id', $user->id)->where('item_id', $item->id)
                ->orderBy('id')->lockForUpdate()->first();
            if ($backpackItem === null || $backpackItem->count < 1) {
                return __('Предмет больше недоступен.');
            }
            if ($backpackItem->isEquipped()) {
                return __('Не возможно передать, предмет надет на персонажа');
            }
            $item = $backpackItem->item;
            if (! $item->itemInfo->is_give) {
                return __('Этот предмет нельзя передать другому игроку.');
            }
            // Keep the exact instance, quantity, expiration and upgrades.
            if (Backpack::query()->where('item_id', $item->id)->where('id', '!=', $backpackItem->id)->exists()
                || ItemOnLocation::query()->where('item_id', $item->id)->exists()) {
                $item = $this->copySplitItem($item);
                $backpackItem->item_id = $item->id;
            }
            $backpackItem->user_id = $toUser->id;
            $backpackItem->save();
            if ($item->itemInfo->type === ShareItemType::TREASURE_MAP) {
                $this->moveTreasureHunt($item, (int) $toUser->id);
            }
            $this->itemActionLogger->log($user, $item->itemInfo, $item->upgrade_lvl, ItemActionType::GIVE, (int) $backpackItem->count, null, $toUser);

            return null;
        });
    }

    private function copySplitItem(Item $item): Item
    {
        $copy = $item->replicate()->setRelations([]);
        $copy->save();
        foreach (['gems' => 'socket_count', 'runes' => 'rune_slot_count'] as $relation => $countField) {
            if ((int) $item->$countField > 0) {
                foreach ($item->$relation()->without($relation === 'gems' ? 'gemInfo' : 'runeInfo')->get() as $attachment) {
                    $attachmentCopy = $attachment->replicate();
                    $attachmentCopy->item_id = $copy->id;
                    $attachmentCopy->save();
                }
            }
        }
        if ($item->itemInfo->type === ShareItemType::TREASURE_MAP && $item->treasureHunt !== null) {
            $hunt = $item->treasureHunt->replicate();
            $hunt->map_item_id = $copy->id;
            $hunt->forceFill($this->stoppedTreasureSearch())->save();
        }

        return $copy;
    }

    private function stoppedTreasureSearch(): array
    {
        return ['search_token' => null, 'started_at' => null, 'completes_at' => null, 'expires_at' => null, 'chance_snapshot' => null];
    }

    private function moveTreasureHunt(Item $item, int $userId): void
    {
        TreasureHunt::query()->where('map_item_id', $item->id)
            ->update(['user_id' => $userId] + $this->stoppedTreasureSearch());
    }

    public function openChest(User $user, int $itemId): ?int
    {
        return DB::transaction(function () use ($user, $itemId): ?int {
            $item = Item::query()
                ->with('itemInfo.lockConfig')
                ->whereKey($itemId)
                ->lockForUpdate()
                ->first();

            $accessible = $item === null ? null : $this->accessibleChest($user, $itemId, $item);
            if ($item === null || $accessible === null) {
                return null;
            }

            if (($item->itemInfo->lockConfig?->lock_required_skill ?? 0) > 0 && ! $item->is_open) {
                return null;
            }

            if (! $item->is_open) {
                if ($accessible[1] === 'world') {
                    $eventCollection = $this->recordWorldEventChestOpened($user, $item);
                    if ($eventCollection !== null && ! $eventCollection->allowed) {
                        return null;
                    }
                }

                $this->populateChest($item);
            }

            return $item->id;
        });
    }

    private function populateChest(Item $item): void
    {
        if ($item->itemInfo->type !== ShareItemType::CHEST || $item->is_open) {
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

    public function recordWorldEventChestOpened(User $user, Item $chest): ?WorldEventCollectionResult
    {
        $slot = ItemOnLocation::query()
            ->where('item_id', $chest->id)
            ->where('interaction_type', LocationItemInteractionType::OPEN_HERE->value)
            ->lockForUpdate()
            ->first();

        if ($slot === null) {
            return null;
        }

        return ($this->worldEventCollectionService ?? app(WorldEventCollectionService::class))->collect($user, $slot);
    }

    public function pickUpFromChest(User $user, int $chestId, int $itemId): string
    {
        return DB::transaction(function () use ($user, $chestId, $itemId): string {
            $chest = Item::query()->whereKey($chestId)->lockForUpdate()->first();
            if ($chest === null || $this->accessibleChest($user, $chestId, $chest) === null || ! $chest->is_open) {
                return __('Сундук больше недоступен.');
            }

            $slot = ItemInChest::query()
                ->where('chest_id', $chestId)
                ->where('item_id', $itemId)
                ->with('item.itemInfo')
                ->lockForUpdate()
                ->first();
            $item = $slot?->item;

            if ($slot === null || $item === null) {
                return __('Кто-то уже поднял этот предмет...');
            }

            $count = $slot->count;
            $slot->delete();

            $instantReward = $this->deliverPickedItem($user, $item, $count);

            if (! ItemInChest::query()->where('chest_id', $chestId)->exists()) {
                $chest->delete();
            }

            if ($instantReward !== null) {
                return sprintf(
                    __('Вы получили <b>%s</b> монет.'),
                    number_format($instantReward->amount, 0, '', ' '),
                );
            }

            return sprintf(__('Вы подняли предмет <b>"%s"</b>...'), $item->itemInfo->name);
        });
    }

    /**
     * Генерирует и атомарно переносит всё содержимое сундука в рюкзак.
     * Вызывающий код должен удерживать FOR UPDATE на сундуке.
     *
     * @return list<array{share_item_id: int, name: string, image: string, count: int, reward_type?: string}>
     */
    public function claimAllChestContents(User $user, Item $chest): array
    {
        if (! $chest->is_open) {
            $this->populateChest($chest);
        }

        $loot = [];
        $slots = ItemInChest::query()
            ->where('chest_id', $chest->id)
            ->with('item.itemInfo')
            ->lockForUpdate()
            ->get();

        foreach ($slots as $slot) {
            $item = $slot->item;
            if ($item === null) {
                $slot->delete();

                continue;
            }

            $count = max(1, (int) $slot->count);
            $slot->delete();
            $instantReward = $this->deliverPickedItem($user, $item, $count);
            $loot[] = $instantReward?->toLootArray() ?? [
                'share_item_id' => (int) $item->share_item_id,
                'name' => $item->itemInfo->name,
                'image' => $item->itemInfo->image,
                'count' => $count,
            ];
        }

        $chest->delete();

        return $loot;
    }

    /** @return array{0: Item, 1: 'world'|'inventory'}|null */
    public function accessibleChest(User $user, int $itemId, ?Item $knownItem = null): ?array
    {
        $item = $knownItem ?? Item::query()->with('itemInfo')->find($itemId);
        if ($item === null || $item->itemInfo->type !== ShareItemType::CHEST) {
            return null;
        }

        if (Backpack::query()
            ->where('user_id', $user->id)
            ->where('item_id', $itemId)
            ->where('equipped', 0)
            ->exists()) {
            return [$item, 'inventory'];
        }

        $user->loadMissing('currentLocation');
        if ($user->currentLocation === null) {
            return null;
        }

        $query = ItemOnLocation::query()
            ->visible()
            ->where('item_id', $itemId)
            ->where('location_id', $user->location_id)
            ->where('interaction_type', LocationItemInteractionType::OPEN_HERE->value);

        if ($user->currentLocation->dungeon_id !== null) {
            $sessionId = DungeonSession::query()
                ->where('user_id', $user->id)
                ->first()?->monsterSessionId();
            $sessionId !== null
                ? $query->where('dungeon_session_id', $sessionId)
                : $query->whereNull('dungeon_session_id');
        } else {
            $query->whereNull('dungeon_session_id');
        }

        return $query->exists() ? [$item, 'world'] : null;
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

        $typeItem = $shareItem->type;
        $itemId = $backpackItem->item->id;

        if ($typeItem === ShareItemType::ARTIFACT) {
            $alreadyEquipped = PlayerArtifact::where('player_id', $user->player->id)
                ->where('share_item_id', $shareItem->id)
                ->exists();
            if ($alreadyEquipped) {
                return __('У вас уже надет артефакт этого типа.');
            }

            PlayerArtifact::create([
                'player_id' => $user->player->id,
                'share_item_id' => $shareItem->id,
                'item_id' => $itemId,
            ]);
            $backpackItem->equipped = 1;
            $backpackItem->save();

            return null;
        }

        $playerEquip = $user->player->playerEquip;
        $injuriesBySlot = $this->injuryService->activeByEquipmentColumn($user->player);
        $slot = $shareItem->slot;

        if ($slot === ShareItemSlot::HAND) {
            $leftInjury = $injuriesBySlot->get('hand_left');
            $rightInjury = $injuriesBySlot->get('hand_right');

            if ($typeItem === ShareItemType::TOOL
                && ($playerEquip->handLeft?->itemInfo?->type === ShareItemType::TOOL
                    || $playerEquip->handRight?->itemInfo?->type === ShareItemType::TOOL)) {
                return __('В руках уже находится инструмент.');
            }

            if ($playerEquip->handLeft?->itemInfo?->is_two_hand) {
                return __('Слот занят двуручным оружием');
            }

            if ($shareItem->is_two_hand) {
                if ($leftInjury !== null || $rightInjury !== null) {
                    return $this->injuryEquipError($leftInjury ?? $rightInjury);
                }

                if ($playerEquip->hand_left || $playerEquip->hand_right) {
                    return __('Нужны обе свободные руки');
                }

                $playerEquip->hand_left = $itemId;
                $playerEquip->save();
                $backpackItem->equipped = 1;
                $backpackItem->save();

                return null;
            }

            if (in_array($typeItem, [ShareItemType::TOOL, ShareItemType::WEAPON], true)
                && ($playerEquip->hand_left || $leftInjury !== null)
                && ($playerEquip->hand_right || $rightInjury !== null)) {
                if ($leftInjury !== null || $rightInjury !== null) {
                    return $this->injuryEquipError($leftInjury ?? $rightInjury);
                }

                return __('Слот занят');
            }
            if ($typeItem === ShareItemType::SHIELD && $rightInjury !== null) {
                return $this->injuryEquipError($rightInjury);
            }
            if ($typeItem === ShareItemType::SHIELD && $playerEquip->hand_right) {
                return __('Слот занят');
            }

            if ($typeItem === ShareItemType::TOOL && ! $playerEquip->hand_left && $leftInjury === null) {
                $playerEquip->hand_left = $itemId;
            } elseif ($typeItem === ShareItemType::TOOL && ! $playerEquip->hand_right && $rightInjury === null) {
                $playerEquip->hand_right = $itemId;
            } elseif (! $playerEquip->hand_left && $leftInjury === null && $typeItem === ShareItemType::WEAPON) {
                $playerEquip->hand_left = $itemId;
            } elseif (! $playerEquip->hand_right && $rightInjury === null && $playerEquip->hand_left !== $itemId
                && in_array($typeItem, [ShareItemType::WEAPON, ShareItemType::SHIELD], true)) {
                $playerEquip->hand_right = $itemId;
            } else {
                return $this->injuryEquipError($leftInjury ?? $rightInjury);
            }

            $playerEquip->save();
            $backpackItem->equipped = 1;
            $backpackItem->save();

            return null;
        }

        if (in_array($slot, ShareItemSlot::armorSlots(), true)) {
            $slotName = $slot->value;
            if ($injury = $injuriesBySlot->get($slotName)) {
                return $this->injuryEquipError($injury);
            }
            if ($playerEquip->$slotName) {
                return __('Слот занят');
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
                return __('Слоты для пояса заняты');
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
                return __('Слоты для сумки заняты');
            }
            $playerEquip->save();
            $backpackItem->equipped = 1;
            $backpackItem->save();

            return null;
        }

        return null;
    }

    private function injuryEquipError(?PlayerInjury $injury): string
    {
        if ($injury === null) {
            return __('Нет свободного слота для этого предмета.');
        }

        return sprintf(
            __('Нельзя надеть предмет: травмирована часть тела «%s», бинт ещё действует.'),
            mb_strtolower($injury->body_part->label()),
        );
    }

    public function unequip(User $user, int $itemId): void
    {
        $backpackItem = Backpack::where('item_id', $itemId)
            ->where('user_id', $user->id)
            ->first();

        if (! $backpackItem || $backpackItem->equipped === 0) {
            return;
        }

        $shareItem = $backpackItem->item->itemInfo;
        $itemId = $backpackItem->item->id;

        if ($shareItem->type === ShareItemType::ARTIFACT) {
            PlayerArtifact::where('player_id', $user->player->id)
                ->where('item_id', $itemId)
                ->delete();
            $backpackItem->equipped = 0;
            $backpackItem->save();

            return;
        }

        $playerEquip = $user->player->playerEquip;
        $slot = $shareItem->slot;

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

    /**
     * Единая точка доставки добычи. Мгновенные награды выдаются сервисом,
     * обычные предметы попадают в рюкзак. Будущие тайники могут вызывать
     * InstantItemRewardService напрямую, не создавая Item.
     */
    private function deliverPickedItem(User $user, Item $item, int $count): ?InstantRewardResult
    {
        $instantReward = $this->instantRewardService->grant($user, $item->itemInfo, $count);
        if ($instantReward !== null) {
            $item->delete();

            return $instantReward;
        }

        if ($item->itemInfo->is_stackable && $item->itemInfo->type !== ShareItemType::TREASURE_MAP) {
            $existing = Backpack::query()
                ->where('user_id', $user->id)
                ->where('equipped', 0)
                ->whereHas('item', function ($query) use ($item): void {
                    $query->where('share_item_id', $item->share_item_id)
                        ->when(
                            $item->expires_at === null,
                            fn ($query) => $query->whereNull('expires_at'),
                            fn ($query) => $query->where('expires_at', $item->expires_at),
                        );
                })
                ->orderBy('id')->lockForUpdate()->first();
            if ($existing) {
                $existing->increment('count', $count);
                if ((int) $existing->item_id !== (int) $item->id
                    && ! Backpack::query()->where('item_id', $item->id)->exists()
                    && ! ItemOnLocation::query()->where('item_id', $item->id)->exists()) {
                    $item->delete();
                }

                return null;
            }
        }

        if ($item->itemInfo->type === ShareItemType::TREASURE_MAP) {
            $this->moveTreasureHunt($item, (int) $user->id);
        }
        $sameInstance = Backpack::query()->where('user_id', $user->id)->where('item_id', $item->id)->lockForUpdate()->first();
        if ($sameInstance !== null) {
            $sameInstance->increment('count', $count);
        } else {
            Backpack::createForItem(userId: (int) $user->id, item: $item, count: $count);
        }

        return null;
    }
}
