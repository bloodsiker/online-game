<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Services;

use App\Modules\Share\Domain\Enums\ItemRarity;

/**
 * Правила «оправы» по стандартной редкости предмета (share_items.rarity) —
 * отдельная колонка/enум не нужны, пять редкостей совпадают с ItemRarity.
 * Легендарная не увеличивает потолок (MAX_SOCKETS=4, как у эпической),
 * но даёт его гарантированно, без риска впустую потратить оправу.
 */
final class MountRarityConfig
{
    private const SOCKET_RANGES = [
        'common' => [1, 1],
        'uncommon' => [1, 2],
        'rare' => [2, 3],
        'epic' => [2, 4],
        'legendary' => [4, 4],
    ];

    private const OPEN_COSTS = [
        'common' => 500,
        'uncommon' => 1500,
        'rare' => 4000,
        'epic' => 10000,
        'legendary' => 20000,
    ];

    /** ItemRarity::label() даёт мужской род («Обычный») — «Оправа» женского рода. */
    private const LABELS_FEMININE = [
        'common' => 'Обычная',
        'uncommon' => 'Необычная',
        'rare' => 'Редкая',
        'epic' => 'Эпическая',
        'legendary' => 'Легендарная',
    ];

    /** @return array{0: int, 1: int} */
    public static function socketRange(ItemRarity $rarity): array
    {
        return self::SOCKET_RANGES[$rarity->value];
    }

    public static function openCost(ItemRarity $rarity): int
    {
        return self::OPEN_COSTS[$rarity->value];
    }

    public static function label(ItemRarity $rarity): string
    {
        return self::LABELS_FEMININE[$rarity->value];
    }

    /** @return list<ItemRarity> Редкости, доступные для оправ */
    public static function supportedRarities(): array
    {
        return [ItemRarity::COMMON, ItemRarity::UNCOMMON, ItemRarity::RARE, ItemRarity::EPIC, ItemRarity::LEGENDARY];
    }
}
