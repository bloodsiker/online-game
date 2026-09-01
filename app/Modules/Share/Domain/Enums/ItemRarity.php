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
            self::COMMON => 8,
            self::UNCOMMON => 12,
            self::RARE => 18,
            self::EPIC => 27,
            self::LEGENDARY => 40,
            self::HEROIC => 60,
        };
    }

    public function gatheringRequiredSkillLevel(): int
    {
        return match ($this) {
            self::COMMON => 1,
            self::UNCOMMON => 50,
            self::RARE => 100,
            self::EPIC => 150,
            self::LEGENDARY => 200,
            self::HEROIC => 300,
        };
    }

    public function gatheringExperience(): int
    {
        return match ($this) {
            self::COMMON => 2,
            self::UNCOMMON => 3,
            self::RARE => 5,
            self::EPIC => 8,
            self::LEGENDARY => 11,
            self::HEROIC => 17,
        };
    }
}
