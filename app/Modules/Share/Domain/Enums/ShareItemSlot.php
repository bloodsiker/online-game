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
}
