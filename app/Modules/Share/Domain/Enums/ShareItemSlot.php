<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ShareItemSlot: string
{
    case HAND = 'hand';
    case HELMET = 'helmet';
    case SHOULDER = 'shoulder';
    case FOREARM = 'forearm';
    case ARMOR = 'armor';
    case LEGGING = 'legging';
    case CHAIN_ARMOR = 'chain_armor';
    case CLOAK = 'cloak';
    case SHOES = 'shoes';
    case GLOVES = 'gloves';
    case BELT = 'belt';
    case BAG = 'bag';
    case RING = 'ring';
    case EARRING = 'earring';
    case NECKLACE = 'necklace';
    case QUIVER = 'quiver';

    public function label(): string
    {
        return match ($this) {
            self::HAND => __('Рука'),
            self::HELMET => __('Шлем'),
            self::SHOULDER => __('Плечи'),
            self::FOREARM => __('Предплечье'),
            self::ARMOR => __('Доспех'),
            self::LEGGING => __('Поножи'),
            self::CHAIN_ARMOR => __('Кольчуга'),
            self::CLOAK => __('Плащ'),
            self::SHOES => __('Обувь'),
            self::GLOVES => __('Перчатки'),
            self::BELT => __('Пояс'),
            self::BAG => __('Сумка'),
            self::RING => __('Кольцо'),
            self::EARRING => __('Серьга'),
            self::NECKLACE => __('Ожерелье'),
            self::QUIVER => __('Колчан'),
        };
    }

    public static function armorSlots(): array
    {
        return [
            self::HELMET,
            self::SHOULDER,
            self::FOREARM,
            self::ARMOR,
            self::LEGGING,
            self::CHAIN_ARMOR,
            self::CLOAK,
            self::SHOES,
            self::GLOVES,
        ];
    }

    /**
     * Колонки player_equipments для ювелирных и аксессуарных слотов (кольца, серьги, ожерелья, колчан) (по порядку заполнения).
     *
     * @return list<string>
     */
    public function jewelryColumns(): array
    {
        return match ($this) {
            self::RING => ['ring_1', 'ring_2', 'ring_3', 'ring_4', 'ring_5'],
            self::EARRING => ['earring_1', 'earring_2'],
            self::NECKLACE => ['necklace_1', 'necklace_2'],
            self::QUIVER => ['quiver'],
            default => [],
        };
    }

    public function isJewelry(): bool
    {
        return $this->jewelryColumns() !== [];
    }

    /** @return list<self> */
    public static function jewelrySlots(): array
    {
        return [self::RING, self::EARRING, self::NECKLACE, self::QUIVER];
    }
}
