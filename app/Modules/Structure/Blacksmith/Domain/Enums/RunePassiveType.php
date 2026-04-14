<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Domain\Enums;

enum RunePassiveType: string
{
    case VAMPIRISM     = 'vampirism';      // хил % от нанесённого урона
    case CHAIN_ATTACK  = 'chain_attack';   // удар по второй цели за % урона
    case STUN          = 'stun';           // % шанс оглушить на 1 ход
    case DOUBLE_STRIKE = 'double_strike';  // % шанс ударить дважды
    case REFLECT       = 'reflect';        // отражение % входящего урона
    case RAGE          = 'rage';           // +% урон когда HP < 30%
    case SHIELD        = 'shield';         // % шанс полностью заблокировать атаку

    public function label(): string
    {
        return match ($this) {
            self::VAMPIRISM     => 'Вампиризм',
            self::CHAIN_ATTACK  => 'Цепная атака',
            self::STUN          => 'Оглушение',
            self::DOUBLE_STRIKE => 'Двойной удар',
            self::REFLECT       => 'Отражение',
            self::RAGE          => 'Ярость',
            self::SHIELD        => 'Щит',
        };
    }

    public function description(int|float $value): string
    {
        return match ($this) {
            self::VAMPIRISM     => "При атаке {$value}% шанс восстановить 10% нанесённого урона",
            self::CHAIN_ATTACK  => "При атаке {$value}% шанс поразить второго врага за 50% урона",
            self::STUN          => "При атаке {$value}% шанс оглушить цель на 1 ход",
            self::DOUBLE_STRIKE => "При атаке {$value}% шанс ударить дважды",
            self::REFLECT       => "Отражает {$value}% входящего урона обратно атакующему",
            self::RAGE          => "При HP < 30% увеличивает урон на {$value}%",
            self::SHIELD        => "При получении урона {$value}% шанс полностью заблокировать удар",
        };
    }

    /** Диапазон значений для пассивки [min, max] */
    public function valueRange(): array
    {
        return match ($this) {
            self::VAMPIRISM     => [8,  15],
            self::CHAIN_ATTACK  => [15, 25],
            self::STUN          => [8,  15],
            self::DOUBLE_STRIKE => [12, 20],
            self::REFLECT       => [5,  10],
            self::RAGE          => [15, 25],
            self::SHIELD        => [8,  15],
        };
    }
}