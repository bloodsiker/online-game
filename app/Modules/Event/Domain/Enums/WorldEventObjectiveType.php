<?php

declare(strict_types=1);

namespace App\Modules\Event\Domain\Enums;

enum WorldEventObjectiveType: string
{
    case COLLECT = 'collect';
    case KILL = 'kill';

    public function label(): string
    {
        return match ($this) {
            self::COLLECT => 'Сбор предметов',
            self::KILL => 'Убийство монстров',
        };
    }
}
