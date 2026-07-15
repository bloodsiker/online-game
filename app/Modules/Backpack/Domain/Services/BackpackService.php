<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\Services;

use App\Modules\Backpack\Domain\DTO\BackpackDTO;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ItemRequirementService;
use Illuminate\Database\Eloquent\Collection;

class BackpackService
{
    public function __construct(
        private readonly ItemRequirementService $requirementService,
    ) {}

    public function getBaseQuery(User $user)
    {
        return Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('equipped', 0);
    }

    public function getBackpackItems(User $user, array $filters = []): Collection
    {
        $query = $this->getBaseQuery($user);

        if (! empty($filters['sid'])) {
            $query->where('items.share_item_id', $filters['sid']);
        }

        $group = $filters['group'] ?? 'main';
        $query->whereHas('item.itemInfo', function ($q) use ($group) {
            $q->byGroup($group);
        });

        return $query->orderBy('backpacks.sort_order')->orderBy('backpacks.id')->get();
    }

    public function getCountableItemsCount(User $user): int
    {
        return $this->getBaseQuery($user)
            ->where('share_items.is_weight', true)
            ->count();
    }

    public function groupItemsByType(Collection $backpack): Collection
    {
        return $backpack->groupBy(
            fn ($item) => $item->item->itemInfo->type
        );
    }

    public function getBackpackData(User $user, array $filters = []): BackpackDTO
    {
        $backpack = $this->getBackpackItems($user, $filters);
        $items = $this->groupItemsByType($backpack);
        $countItems = $this->getCountableItemsCount($user);
        $group = $filters['group'] ?? 'main';

        return new BackpackDTO(
            backpack: $backpack,
            items: $items,
            countItems: $countItems,
            group: $group,
        );
    }

    public function hasItem(User $user, int $itemId, int $quantity = 1): bool
    {
        $count = Backpack::where('user_id', $user->id)
            ->where('item_id', $itemId)
            ->where('equipped', 0)
            ->sum('quantity');

        return $count >= $quantity;
    }

    public function getItem(User $user, ShareItem $shareItem): ?Backpack
    {
        return Backpack::whereHas('item', function ($query) use ($shareItem) {
            $query->where('share_item_id', $shareItem->id);
        })
            ->where('user_id', $user->id)
            ->where('equipped', 0)
            ->first();
    }

    public function getItemById(User $user, int $itemId): ?Backpack
    {
        return Backpack::where('user_id', $user->id)
            ->where('item_id', $itemId)
            ->where('equipped', 0)
            ->first();
    }

    public function hasItemByShareItem(User $user, ShareItem $shareItem, int $quantity = 1): bool
    {
        $count = Backpack::whereHas('item', function ($query) use ($shareItem) {
            $query->where('share_item_id', $shareItem->id);
        })
            ->where('user_id', $user->id)
            ->where('equipped', 0)
            ->sum('count');

        return $count >= $quantity;
    }

    public function addItemByShareItem(User $user, ShareItem $shareItem, int $quantity = 1): Backpack
    {
        $quantity = max(1, $quantity);

        if (! $shareItem->type->isStackable()) {
            $created = [];
            for ($i = 0; $i < $quantity; $i++) {
                $created[] = $this->createBackpackItemByShareItem($user, $shareItem);
            }

            return $created[0];
        }

        $backpackItem = $this->getItem($user, $shareItem);

        if ($backpackItem) {
            $backpackItem->increment('count', $quantity);

            return $backpackItem->fresh();
        }

        return $this->createBackpackItemByShareItem($user, $shareItem, $quantity);
    }

    /**
     * Выдаёт несколько единиц предмета с учётом стакаемости типа:
     * стакающиеся кладутся одной ячейкой (count += quantity),
     * экипировка — отдельными экземплярами по одному в ячейке.
     *
     * @return list<Backpack>
     */
    public function giveItemsByShareItem(User $user, ShareItem $shareItem, int $quantity = 1): array
    {
        $quantity = max(1, $quantity);

        if ($shareItem->type->isStackable()) {
            return [$this->addItemByShareItem($user, $shareItem, $quantity)];
        }

        $created = [];
        for ($i = 0; $i < $quantity; $i++) {
            $created[] = $this->createBackpackItemByShareItem($user, $shareItem);
        }

        return $created;
    }

    private function createBackpackItemByShareItem(User $user, ShareItem $shareItem, int $count = 1): Backpack
    {
        $item = Item::create([
            'share_item_id' => $shareItem->id,
        ]);

        $user->backpack()->attach($item->id, [
            'equipped' => 0,
            'count' => $count,
        ]);

        return $this->getItemById($user, $item->id);
    }

    public function addItem(User $user, int $itemId, int $quantity = 1, array $attributes = []): Backpack
    {
        $existingItem = $this->getItemById($user, $itemId);

        if ($existingItem) {
            $existingItem->increment('count', $quantity);

            return $existingItem->fresh();
        }

        return Backpack::create(array_merge([
            'user_id' => $user->id,
            'item_id' => $itemId,
            'count' => $quantity,
            'equipped' => 0,
        ], $attributes));
    }

    public function removeItemByShareItem(User $user, ShareItem $shareItem, int $quantity = 1): bool
    {
        $backpackItem = $this->getItem($user, $shareItem);

        if (! $backpackItem || $backpackItem->count < $quantity) {
            return false;
        }

        return $this->removeBackpackItem($backpackItem, $quantity);
    }

    public function removeItem(User $user, int $itemId, int $quantity = 1): bool
    {
        $backpackItem = $this->getItemById($user, $itemId);

        if (! $backpackItem || $backpackItem->count < $quantity) {
            return false;
        }

        return $this->removeBackpackItem($backpackItem, $quantity);
    }

    protected function removeBackpackItem(Backpack $backpackItem, int $quantity): bool
    {
        \DB::transaction(function () use ($backpackItem, $quantity) {
            if ($backpackItem->count <= $quantity) {
                $item = $backpackItem->item;
                $backpackItem->delete();

                $hasOtherReferences = Backpack::where('item_id', $item->id)->exists();
                if (! $hasOtherReferences) {
                    $item->delete();
                }
            } else {
                $backpackItem->decrement('count', $quantity);
            }
        });

        return true;
    }

    public function useItem(User $user, int $backpackId, array $params = []): bool
    {
        $backpackItem = Backpack::with('item.itemInfo')
            ->where('id', $backpackId)
            ->where('user_id', $user->id)
            ->where('equipped', 0)
            ->first();

        if (! $backpackItem || $backpackItem->quantity < 1) {
            return false;
        }

        $error = $this->requirementService->check($user->player, $backpackItem->item->itemInfo);
        if ($error) {
            return false;
        }

        $this->applyItemEffect($user, $backpackItem, $params);

        if ($backpackItem->quantity == 1) {
            $backpackItem->delete();
        } else {
            $backpackItem->decrement('quantity');
        }

        return true;
    }

    /**
     * Применить эффект использования предмета
     */
    protected function applyItemEffect(User $user, Backpack $backpackItem, array $params = []): void
    {
        $itemInfo = $backpackItem->item->itemInfo;
        $itemType = $itemInfo->type;

        switch ($itemType) {
            case ShareItemType::POTION:
                if ($itemInfo->heal_hp) {
                    $user->player->changeHp($itemInfo->heal_hp);
                }

                if ($itemInfo->heal_mp) {
                    $user->player->changeMp($itemInfo->heal_mp);
                }
                break;

            case ShareItemType::SCROLL:
                break;
        }
    }
}
