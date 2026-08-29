<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ItemRarity: string
{
    case COMMON = 'common';
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';
    case HEROIC = 'heroic';

    public function color(): string
    {
        return match ($this) {
            self::COMMON => '#666666',
            self::UNCOMMON => '#339900',
            self::RARE => '#3300ff',
            self::EPIC => '#990099',
            self::LEGENDARY => '#ff0000',
            self::HEROIC => '#e09100',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::COMMON => 'Обычный',
            self::UNCOMMON => 'Необычный',
            self::RARE => 'Редкий',
            self::EPIC => 'Эпический',
            self::LEGENDARY => 'Легендарный',
            self::HEROIC => 'Героический',
        };
    }

    public function defaultGatheringSeconds(): int
    {
        return match ($this) {
            self::COMMON => 5,
            self::UNCOMMON => 8,
            self::RARE => 12,
            self::EPIC => 18,
            self::LEGENDARY => 30,
            self::HEROIC => 45,
        };
    }
}
