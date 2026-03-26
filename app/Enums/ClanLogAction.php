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
    case CLAN_RENAMED        = 'clan_renamed';
    case BONUS_POINTS_EARNED = 'bonus_points_earned';
    case SKILL_LEARNED       = 'skill_learned';
    case SKILL_UPGRADED      = 'skill_upgraded';
    case QUEST_STARTED        = 'quest_started';
    case QUEST_COMPLETED      = 'quest_completed';
    case QUEST_CANCELLED      = 'quest_cancelled';
    case TREASURY_DEPOSIT     = 'treasury_deposit';
    case TREASURY_WITHDRAW    = 'treasury_withdraw';

    public function label(): string
    {
        return match($this) {
            self::JOINED               => 'Вступил',
            self::LEFT                 => 'Вышел',
            self::KICKED               => 'Исключён',
            self::INVITED              => 'Приглашён',
            self::INVITED_CANCEL       => 'Приглашение отменено',
            self::PROMOTED             => 'Повышен',
            self::DEMOTED              => 'Понижен',
            self::BANNED               => 'Заблокирован',
            self::UNBANNED             => 'Разблокирован',
            self::CLAN_CREATED         => 'Клан создан',
            self::CLAN_LVL_UP          => 'Повышен уровень клана',
            self::CLAN_DELETED         => 'Клан удалён',
            self::CLAN_RENAMED         => 'Клан переименован',
            self::BONUS_POINTS_EARNED  => 'Заработаны бонусные очки',
            self::SKILL_LEARNED        => 'Изучен новый навык',
            self::SKILL_UPGRADED       => 'Изучен уровень навыка',
            self::QUEST_STARTED        => 'Клановый квест начат',
            self::QUEST_COMPLETED      => 'Клановый квест завершён',
            self::QUEST_CANCELLED      => 'Клановый квест отменён',
            self::TREASURY_DEPOSIT     => 'Пополнение казны',
            self::TREASURY_WITHDRAW    => 'Снятие из казны',
        };
    }
}
