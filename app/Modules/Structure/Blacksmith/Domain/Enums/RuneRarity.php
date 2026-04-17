<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Enums;

enum RuneRarity: string
{
    case COMMON = 'common';
    case RARE = 'rare';
    case EPIC = 'epic';
    case LEGENDARY = 'legendary';

    public function label(): string
    {
        return match ($this) {
            self::COMMON => 'Обычная',
            self::RARE => 'Редкая',
            self::EPIC => 'Эпическая',
            self::LEGENDARY => 'Легендарная',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::COMMON => '#888888',
            self::RARE => '#2266cc',
            self::EPIC => '#9933cc',
            self::LEGENDARY => '#cc7700',
        };
    }

    /** [min, max] количество статов */
    public function statCount(): array
    {
        return match ($this) {
            self::COMMON => [1, 2],
            self::RARE => [2, 3],
            self::EPIC => [3, 4],
            self::LEGENDARY => [4, 5],
        };
    }

    /** Множитель значений статов */
    public function multiplier(): float
    {
        return match ($this) {
            self::COMMON => 1.0,
            self::RARE => 1.8,
            self::EPIC => 3.0,
            self::LEGENDARY => 5.0,
        };
    }

    /** Шанс получить пассивный навык (%) */
    public function passiveChance(): int
    {
        return match ($this) {
            self::COMMON => 0,
            self::RARE => 0,
            self::EPIC => 25,
            self::LEGENDARY => 60,
        };
    }

    /** Базовая стоимость перебросы (золото) */
    public function rerollBaseCost(): int
    {
        return match ($this) {
            self::COMMON => 100,
            self::RARE => 300,
            self::EPIC => 800,
            self::LEGENDARY => 2000,
        };
    }

    /** Шанс провала в режиме риска (%) */
    public function riskFailChance(): int
    {
        return match ($this) {
            self::COMMON => 0,
            self::RARE => 15,
            self::EPIC => 30,
            self::LEGENDARY => 45,
        };
    }
}
