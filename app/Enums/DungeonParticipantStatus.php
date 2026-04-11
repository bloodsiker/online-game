<?php

declare(strict_types=1);

namespace App\Enums;

enum DungeonParticipantStatus: string
{
    case ACTIVE = 'active';
    case DEAD   = 'dead';
    case LEFT   = 'left';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => 'Активен',
            self::DEAD   => 'Погиб',
            self::LEFT   => 'Покинул',
        };
    }
}