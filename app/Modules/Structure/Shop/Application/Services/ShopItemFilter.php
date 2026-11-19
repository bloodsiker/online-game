<?php

declare(strict_types=1);

namespace App\Modules\Structure\Shop\Application\Services;

use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Structure\Shop\Application\DTOs\ShopBuyItemDTO;
use App\Modules\Structure\Shop\Application\DTOs\ShopFilterDTO;

/** Фильтрация и сортировка уже собранных карточек витрины. */
class ShopItemFilter
{
    /**
     * @param  list<ShopBuyItemDTO>  $items
     * @return list<ShopBuyItemDTO>
     */
    public function apply(
        array $items,
        ShopFilterDTO $filter,
        int $playerLevel,
        int $reputationPoints,
        int $money,
        int $diamonds,
    ): array {
        [$levelFrom, $levelTo] = match ($filter->levelMode) {
            ShopFilterDTO::LEVEL_MINE => [null, $playerLevel],
            ShopFilterDTO::LEVEL_RANGE => [$filter->levelFrom, $filter->levelTo],
            default => [null, null],
        };
        $search = mb_strtolower(trim($filter->search));

        $result = array_values(array_filter($items, function (ShopBuyItemDTO $item) use (
            $filter, $levelFrom, $levelTo, $search, $playerLevel, $reputationPoints, $money, $diamonds,
        ): bool {
            $level = $item->requiredLevel ?? 0;

            return ($search === '' || str_contains(mb_strtolower($item->name), $search))
                && ($levelFrom === null || $level >= $levelFrom)
                && ($levelTo === null || $level <= $levelTo)
                && $filter->allowsRarity($item->rarity)
                && (! $filter->availableOnly || $this->isAvailable($item, $playerLevel, $reputationPoints, $money, $diamonds));
        }));

        if ($filter->sort !== ShopFilterDTO::SORT_DEFAULT) {
            usort($result, fn (ShopBuyItemDTO $a, ShopBuyItemDTO $b): int => match ($filter->sort) {
                ShopFilterDTO::SORT_PRICE_ASC => [$a->diamond, $a->price] <=> [$b->diamond, $b->price],
                ShopFilterDTO::SORT_PRICE_DESC => [$b->diamond, $b->price] <=> [$a->diamond, $a->price],
                ShopFilterDTO::SORT_LEVEL_ASC => ($a->requiredLevel ?? 0) <=> ($b->requiredLevel ?? 0),
                ShopFilterDTO::SORT_LEVEL_DESC => ($b->requiredLevel ?? 0) <=> ($a->requiredLevel ?? 0),
                ShopFilterDTO::SORT_RARITY_DESC => $this->rarityRank($b->rarity) <=> $this->rarityRank($a->rarity),
                default => 0,
            });
        }

        return $result;
    }

    private function isAvailable(ShopBuyItemDTO $item, int $playerLevel, int $reputationPoints, int $money, int $diamonds): bool
    {
        if (($item->requiredLevel ?? 0) > $playerLevel
            || $item->minReputationPoints > $reputationPoints
            || $item->price > $money
            || $item->diamond > $diamonds
            || ($item->purchaseAvailability !== null && (int) $item->purchaseAvailability['remaining'] <= 0)) {
            return false;
        }

        foreach ($item->requirements as $requirement) {
            if ($requirement['availableQuantity'] < $requirement['quantity']) {
                return false;
            }
        }

        return true;
    }

    private function rarityRank(string $rarity): int
    {
        $index = array_search(ItemRarity::tryFrom($rarity), ItemRarity::cases(), true);

        return $index === false ? -1 : (int) $index;
    }
}
