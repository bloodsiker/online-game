<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Enums;

enum RuneRarity: string
{
    case COMMON = 'common';
    case UNCOMMON = 'uncommon';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';
    case HEROIC = 'heroic';

    public function label(): string
    {
        return match ($this) {
            self::COMMON => 'Обычная',
            self::UNCOMMON => 'Необычная',
            self::RARE => 'Редкая',
            self::EPIC => 'Эпическая',
            self::LEGENDARY => 'Легендарная',
            self::HEROIC => 'Героическая',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMMON => '#888888',
            self::UNCOMMON => '#33aa66',
            self::RARE => '#2266cc',
            self::EPIC => '#9933cc',
            self::LEGENDARY => '#cc7700',
            self::HEROIC => '#cc2200',
        };
    }

    /** [min, max] количество статов */
    public function statCount(): array
    {
        return match ($this) {
            self::COMMON => [1, 2],
            self::UNCOMMON => [2, 2],
            self::RARE => [2, 3],
            self::EPIC => [3, 4],
            self::LEGENDARY => [4, 5],
            self::HEROIC => [5, 6],
        };
    }

    /** Множитель значений статов */
    public function multiplier(): float
    {
        return match ($this) {
            self::COMMON => 1.0,
            self::UNCOMMON => 1.4,
            self::RARE => 1.8,
            self::EPIC => 3.0,
            self::LEGENDARY => 5.0,
            self::HEROIC => 7.5,
        };
    }

    /** Шанс получить пассивный навык (%) */
    public function passiveChance(): int
    {
        return match ($this) {
            self::COMMON => 0,
            self::UNCOMMON => 0,
            self::RARE => 0,
            self::EPIC => 25,
            self::LEGENDARY => 60,
            self::HEROIC => 85,
        };
    }

    /** Базовая стоимость перебросы (золото) */
    public function rerollBaseCost(): int
    {
        return match ($this) {
            self::COMMON => 100,
            self::UNCOMMON => 200,
            self::RARE => 300,
            self::EPIC => 800,
            self::LEGENDARY => 2000,
            self::HEROIC => 5000,
        };
    }

    /** Шанс провала в режиме риска (%) */
    public function riskFailChance(): int
    {
        return match ($this) {
            self::COMMON => 0,
            self::UNCOMMON => 8,
            self::RARE => 15,
            self::EPIC => 30,
            self::LEGENDARY => 45,
            self::HEROIC => 60,
        };
    }
}
