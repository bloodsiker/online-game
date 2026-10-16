<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Enums;

enum ClanJoinRequestStatus: string
{
    case INVITE = 'invite';
    case REQUEST = 'request';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::INVITE => __('clan.status.invite'),
            self::REQUEST => __('clan.status.request'),
            self::ACCEPTED => __('clan.status.accepted'),
            self::DECLINED => __('clan.status.declined'),
            self::CANCELLED => __('clan.status.cancelled'),
        };
    }
}
