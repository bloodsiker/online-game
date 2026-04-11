<?php

declare(strict_types=1);

namespace App\Enums;

enum PartyStatus: string
{
    case OPEN       = 'open';
    case IN_DUNGEON = 'in_dungeon';
    case DISBANDED  = 'disbanded';

    public function label(): string
    {
        return match($this) {
            self::OPEN       => 'Открыта',
            self::IN_DUNGEON => 'В данже',
            self::DISBANDED  => 'Распущена',
        };
    }
}