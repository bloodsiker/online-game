<?php

namespace App\Enums;

enum ClanJoinRequestStatus: string
{
    case INVITE  = 'invite';
    case REQUEST = 'request';

    public function label(): string
    {
        return match($this) {
            self::INVITE  => 'ожидание согласия',
            self::REQUEST => 'ожидание подтверждения',
        };
    }
}
