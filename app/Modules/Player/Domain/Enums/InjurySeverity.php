<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Enums;

enum InjurySeverity: int
{
    case LIGHT = 1;
    case MEDIUM = 2;
    case SEVERE = 3;

    public function label(): string
    {
        return match ($this) {
            self::LIGHT => 'Лёгкая',
            self::MEDIUM => 'Средняя',
            self::SEVERE => 'Тяжёлая',
        };
    }
}
