<?php

declare(strict_types=1);

namespace App\Enums;

enum PlayerStatKey: string
{
    case STR = 'str';
    case AGIL = 'agil';
    case INT = 'int';
    case MUD = 'mud';
    case INTEL = 'intel';

    public function label(): string
    {
        return match ($this) {
            self::STR => 'Сила',
            self::AGIL => 'Ловкость',
            self::INT => 'Интуиция',
            self::MUD => 'Мудрость',
            self::INTEL => 'Интеллект',
        };
    }
}
