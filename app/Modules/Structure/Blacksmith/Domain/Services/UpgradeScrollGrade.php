<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;

/**
 * Грейд основного свитка заточки задаётся его редкостью: каждый грейд
 * открывает свой диапазон целевых уровней заточки (по 5 уровней на грейд).
 */
final class UpgradeScrollGrade
{
    /** @var array<string, array{0: int, 1: int}> редкость => [мин, макс] целевой уровень */
    private const RANGES = [
        'common' => [1, 5],
        'uncommon' => [6, 10],
        'rare' => [11, 15],
        'epic' => [16, 20],
        'legendary' => [21, 25],
    ];

    /**
     * @return array{0: int, 1: int}|null
     */
    public static function rangeFor(?ItemRarity $rarity): ?array
    {
        return self::RANGES[($rarity ?? ItemRarity::COMMON)->value] ?? null;
    }

    public static function fits(?ItemRarity $rarity, int $currentLevel): bool
    {
        $range = self::rangeFor($rarity);
        $target = $currentLevel + 1;

        return $range !== null && $target >= $range[0] && $target <= $range[1];
    }

    /** Оружие точится свитком оружия, броня и щиты — свитком брони. */
    public static function requiredScrollType(?ShareItemType $itemType): UpgradeScrollType
    {
        return $itemType === ShareItemType::WEAPON ? UpgradeScrollType::BASE : UpgradeScrollType::BASE_ARMOR;
    }

    public static function requiredRarity(int $currentLevel): ?ItemRarity
    {
        $target = $currentLevel + 1;
        foreach (self::RANGES as $rarity => [$min, $max]) {
            if ($target >= $min && $target <= $max) {
                return ItemRarity::from($rarity);
            }
        }

        return null;
    }
}
