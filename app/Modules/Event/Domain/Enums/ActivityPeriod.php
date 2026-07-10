<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Enums;

enum ActivityPeriod: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';

    public function label(): string
    {
        return match ($this) {
            self::DAILY => 'Дневные',
            self::WEEKLY => 'Недельные',
        };
    }

    /**
     * Ключ текущего цикла: дата для дневных, год-неделя для недельных.
     * Прогресс с другим ключом относится к прошлому циклу и не учитывается.
     */
    public function currentKey(): string
    {
        return match ($this) {
            self::DAILY => now()->format('Y-m-d'),
            self::WEEKLY => now()->format('o-\WW'),
        };
    }
}
