<?php

namespace App\Enums;

enum ClanLogAction: string
{
    case JOINED        = 'joined';
    case LEFT          = 'left';
    case KICKED        = 'kicked';
    case INVITED       = 'invited';
    case INVITED_CANCEL = 'invited_cancel';
    case PROMOTED      = 'promoted';
    case DEMOTED       = 'demoted';
    case BANNED        = 'banned';
    case UNBANNED      = 'unbanned';
    case CLAN_CREATED    = 'clan_created';
    case CLAN_LVL_UP     = 'clan_lvl_up';
    case CLAN_DELETED    = 'clan_deleted';
    case CLAN_RENAMED    = 'clan_renamed';

    public function label(): string
    {
        return match($this) {
            self::JOINED          => 'Вступил',
            self::LEFT            => 'Вышел',
            self::KICKED          => 'Исключён',
            self::INVITED         => 'Приглашён',
            self::INVITED_CANCEL  => 'Приглашение отменено',
            self::PROMOTED        => 'Повышен',
            self::DEMOTED         => 'Понижен',
            self::BANNED          => 'Заблокирован',
            self::UNBANNED        => 'Разблокирован',
            self::CLAN_CREATED    => 'Клан создан',
            self::CLAN_LVL_UP     => 'Повышен уровень клана',
            self::CLAN_DELETED    => 'Клан удалён',
            self::CLAN_RENAMED    => 'Клан переименован',
        };
    }
}
