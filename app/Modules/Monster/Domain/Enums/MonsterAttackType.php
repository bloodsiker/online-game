<?php

declare(strict_types=1);

namespace App\Modules\Monster\Domain\Enums;

enum MonsterAttackType: string
{
    case PHYSICAL = 'physical';
    case MAGIC = 'magic';

    public function isMagic(): bool
    {
        return $this === self::MAGIC;
    }

    public function isPhysical(): bool
    {
        return $this === self::PHYSICAL;
    }

    public function label(): string
    {
        return match ($this) {
            self::PHYSICAL => 'Физическая',
            self::MAGIC => 'Магическая',
        };
    }
}
