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
            self::STRENGTH => __('Сила'),
            self::AGILITY => __('Ловкость'),
            self::INTUITION => __('Интуиция'),
            self::WISDOM => __('Мудрость'),
            self::INTELLIGENCE => __('Интеллект'),
            self::ENDURANCE => __('Выносливость'),
        };
    }
}
