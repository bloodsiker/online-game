<?php

declare(strict_types=1);

namespace App\Enums;

enum ShareItemSlot: string
{
    case HAND        = 'hand';
    case HELMET      = 'helmet';
    case SHOULDER    = 'shoulder';
    case FOREARM     = 'forearm';
    case ARMOR       = 'armor';
    case LEGGING     = 'legging';
    case CHAIN_ARMOR = 'chain_armor';
    case CLOAK       = 'cloak';
    case SHOES       = 'shoes';
    case GLOVES      = 'gloves';
    case BELT        = 'belt';
    case BAG         = 'bag';

    public function label(): string
    {
        return match ($this) {
            self::HAND        => 'Рука',
            self::HELMET      => 'Шлем',
            self::SHOULDER    => 'Плечи',
            self::FOREARM     => 'Предплечье',
            self::ARMOR       => 'Доспех',
            self::LEGGING     => 'Поножи',
            self::CHAIN_ARMOR => 'Кольчуга',
            self::CLOAK       => 'Плащ',
            self::SHOES       => 'Обувь',
            self::GLOVES      => 'Перчатки',
            self::BELT        => 'Пояс',
            self::BAG         => 'Сумка',
        };
    }

    /** Слоты для брони (не рука, не пояс, не сумка) */
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
}
