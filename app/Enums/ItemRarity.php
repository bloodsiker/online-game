<?php

declare(strict_types=1);

namespace App\Enums;

enum ItemRarity: string
{
    case COMMON    = 'common';
    case UNCOMMON  = 'uncommon';
    case RARE      = 'rare';
    case EPIC      = 'epic';
    case LEGENDARY = 'legendary';

    public function color(): string
    {
        return match ($this) {
            self::COMMON    => '#666666',
            self::UNCOMMON  => '#339900',
            self::RARE      => '#3300ff',
            self::EPIC      => '#990099',
            self::LEGENDARY => '#ff0000',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::COMMON    => 'Обычный',
            self::UNCOMMON  => 'Необычный',
            self::RARE      => 'Редкий',
            self::EPIC      => 'Эпический',
            self::LEGENDARY => 'Легендарный',
        };
    }
}
