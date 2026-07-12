<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Enums;

enum PlayerStatKey: string
{
    case STRENGTH = 'strength';
    case AGILITY = 'agility';
    case INTUITION = 'intuition';
    case WISDOM = 'wisdom';
    case INTELLIGENCE = 'intelligence';
    case ENDURANCE = 'endurance';

    public function label(): string
    {
        return match ($this) {
            self::STRENGTH => 'Сила',
            self::AGILITY => 'Ловкость',
            self::INTUITION => 'Интуиция',
            self::WISDOM => 'Мудрость',
            self::INTELLIGENCE => 'Интеллект',
            self::ENDURANCE => 'Выносливость',
        };
    }
}
