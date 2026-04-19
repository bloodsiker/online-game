<?php

declare(strict_types=1);

namespace App\Modules\Referral\Domain\Enums;

enum ReferralRewardType: string
{
    case GOLD = 'gold';
    case DIAMOND = 'diamond';
    case ITEM = 'item';

    public function label(): string
    {
        return match ($this) {
            self::GOLD => 'Золото',
            self::DIAMOND => 'Алмазы',
            self::ITEM => 'Предмет',
        };
    }
}
