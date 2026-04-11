<?php

declare(strict_types=1);

namespace App\Enums;

enum DungeonRewardType: string
{
    case GOLD       = 'gold';
    case DIAMOND    = 'diamond';
    case ITEM       = 'item';
    case EXPERIENCE = 'experience';

    public function label(): string
    {
        return match($this) {
            self::GOLD       => 'Золото',
            self::DIAMOND    => 'Алмазы',
            self::ITEM       => 'Предмет',
            self::EXPERIENCE => 'Опыт',
        };
    }
}