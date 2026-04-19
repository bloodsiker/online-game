<?php

declare(strict_types=1);

namespace App\Modules\Battle\Domain\Enums;

enum CombatClass: string
{
    case TANK = 'tank';
    case DODGE = 'dodge';
    case CRIT = 'crit';

    public function getLabel(): string
    {
        return match ($this) {
            self::TANK => 'Танк',
            self::DODGE => 'Уворот',
            self::CRIT => 'Крит',
        };
    }

    public function getBadgeClass(): string
    {
        return match ($this) {
            self::TANK => 'badge-primary',
            self::DODGE => 'badge-success',
            self::CRIT => 'badge-danger',
        };
    }

    /** Класс, над которым данный класс имеет преимущество */
    public function beats(): self
    {
        return match ($this) {
            self::TANK => self::DODGE,
            self::DODGE => self::CRIT,
            self::CRIT => self::TANK,
        };
    }
}
