<?php

declare(strict_types=1);

namespace App\Modules\ItemAssembly\Domain\Enums;

enum AssemblyResultMode: string
{
    case RANDOM = 'random';
    case FIXED = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::RANDOM => 'Один случайный предмет из пула',
            self::FIXED => 'Фиксированный предмет',
        };
    }
}
