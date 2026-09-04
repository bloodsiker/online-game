<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Application\UseCases;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Application\DTOs\BlacksmithActionResultDTO;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Support\Facades\DB;

class UpgradeItemRarity
{
    public function execute(
        User $user,
        int $structureId,
        int $itemId,
        string $expectedStructureType = Structure::TYPE_BLACKSMITH,
        ?ShareItemType $expectedItemType = null,
    ): BlacksmithActionResultDTO {
        return DB::transaction(function () use ($user, $structureId, $itemId, $expectedStructureType, $expectedItemType): BlacksmithActionResultDTO {
            $blacksmith = Structure::query()->whereKey($structureId)->lockForUpdate()->first();
            if ($blacksmith === null || $blacksmith->type !== $expectedStructureType) {
                return new BlacksmithActionResultDTO(false, 'Мастерская не найдена.');
            }
            if ((int) $blacksmith->location_id !== (int) $user->location_id) {
                return new BlacksmithActionResultDTO(false, 'Для апгрейда нужно находиться у нужного здания.');
            }

            $slot = Backpack::query()
                ->with(['item.itemInfo.rarityUpgradeTarget', 'item.itemInfo.rarityUpgradeMaterials'])
                ->where('user_id', $user->id)
                ->where('item_id', $itemId)
                ->lockForUpdate()
                ->first();

            if ($slot === null || $slot->equipped) {
                return new BlacksmithActionResultDTO(false, 'Предмет должен находиться в рюкзаке.');
            }

            $source = $slot->item->itemInfo;
            $target = $source->rarityUpgradeTarget;
            if ($expectedStructureType === Structure::TYPE_BLACKSMITH && $source->type === ShareItemType::TOOL) {
                return new BlacksmithActionResultDTO(false, 'Инструменты улучшаются в мастерской инструментов.');
            }
            if ($expectedItemType !== null && ($source->type !== $expectedItemType || $target?->type !== $expectedItemType)) {
                return new BlacksmithActionResultDTO(false, 'В этой мастерской можно улучшать только инструменты.');
            }
            if ($target === null) {
                return new BlacksmithActionResultDTO(false, 'Для этого предмета апгрейд не настроен.');
            }
            if ($target->type !== $source->type || $this->rarityRank($target->rarity) <= $this->rarityRank($source->rarity)) {
                return new BlacksmithActionResultDTO(false, 'Результат апгрейда должен быть предметом того же типа и более высокой редкости.');
            }

            $lockedUser = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            if ($lockedUser->money < $source->upgrade_gold_cost) {
                return new BlacksmithActionResultDTO(false, 'Недостаточно монет для апгрейда.');
            }

            foreach ($source->rarityUpgradeMaterials as $material) {
                if ($material->id === $source->id) {
                    return new BlacksmithActionResultDTO(false, 'Исходный предмет нельзя использовать как материал для самого себя.');
                }
                if ($this->availableMaterialCount($user, $material->id) < (int) $material->pivot->count) {
                    return new BlacksmithActionResultDTO(false, sprintf('Не хватает материала «%s».', $material->name));
                }
            }

            foreach ($source->rarityUpgradeMaterials as $material) {
                $this->consumeMaterial($user, $material->id, (int) $material->pivot->count);
            }

            if ($source->upgrade_gold_cost > 0) {
                $lockedUser->decrement('money', $source->upgrade_gold_cost);
            }

            $item = Item::query()->whereKey($slot->item_id)->lockForUpdate()->firstOrFail();
            $item->share_item_id = $target->id;
            $item->save();

            return new BlacksmithActionResultDTO(true, sprintf('Апгрейд выполнен: «%s».', $target->name), true);
        });
    }

    private function availableMaterialCount(User $user, int $shareItemId): int
    {
        return (int) Backpack::query()
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', false)
            ->whereHas('item', fn ($query) => $query->where('share_item_id', $shareItemId))
            ->lockForUpdate()
            ->sum('count');
    }

    private function rarityRank(ItemRarity $rarity): int
    {
        return array_search($rarity, ItemRarity::cases(), true);
    }

    private function consumeMaterial(User $user, int $shareItemId, int $quantity): bool
    {
        $slots = Backpack::query()
            ->with('item')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', false)
            ->whereHas('item', fn ($query) => $query->where('share_item_id', $shareItemId))
            ->lockForUpdate()
            ->get();

        if ($slots->sum('count') < $quantity) {
            return false;
        }

        foreach ($slots as $slot) {
            $take = min($quantity, (int) $slot->count);
            if ($take === 0) {
                continue;
            }
            if ($take === (int) $slot->count) {
                $item = $slot->item;
                $slot->delete();
                if (! Backpack::query()->where('item_id', $item->id)->exists()) {
                    $item->delete();
                }
            } else {
                $slot->decrement('count', $take);
            }
            $quantity -= $take;
            if ($quantity === 0) {
                break;
            }
        }

        return true;
    }
}
