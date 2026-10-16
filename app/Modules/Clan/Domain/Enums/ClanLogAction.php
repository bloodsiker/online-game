<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Enums;

enum ClanLogAction: string
{
    case JOINED = 'joined';
    case LEFT = 'left';
    case KICKED = 'kicked';
    case INVITED = 'invited';
    case INVITED_CANCEL = 'invited_cancel';
    case INVITE_ACCEPTED = 'invite_accepted';
    case INVITE_DECLINED = 'invite_declined';
    case APPLICATION_SUBMITTED = 'application_submitted';
    case APPLICATION_ACCEPTED = 'application_accepted';
    case APPLICATION_DECLINED = 'application_declined';
    case APPLICATION_WITHDRAWN = 'application_withdrawn';
    case PROMOTED = 'promoted';
    case DEMOTED = 'demoted';
    case BANNED = 'banned';
    case UNBANNED = 'unbanned';
    case CLAN_CREATED = 'clan_created';
    case CLAN_LVL_UP = 'clan_lvl_up';
    case CLAN_DELETED = 'clan_deleted';
    case CLAN_RENAMED = 'clan_renamed';
    case BONUS_POINTS_EARNED = 'bonus_points_earned';
    case SKILL_LEARNED = 'skill_learned';
    case SKILL_UPGRADED = 'skill_upgraded';
    case QUEST_STARTED = 'quest_started';
    case QUEST_COMPLETED = 'quest_completed';
    case QUEST_CANCELLED = 'quest_cancelled';
    case TREASURY_DEPOSIT = 'treasury_deposit';
    case TREASURY_WITHDRAW = 'treasury_withdraw';
    case TAX_PAID = 'tax_paid';
    case BOSS_KILLED = 'boss_killed';

    public function label(): string
    {
        return match ($this) {
            self::JOINED => __('Вступил'),
            self::LEFT => __('Вышел'),
            self::KICKED => __('Исключён'),
            self::INVITED => __('Приглашён'),
            self::INVITED_CANCEL => __('Приглашение отменено'),
            self::INVITE_ACCEPTED => __('Приглашение принято'),
            self::INVITE_DECLINED => __('Приглашение отклонено'),
            self::APPLICATION_SUBMITTED => __('Подана заявка в клан'),
            self::APPLICATION_ACCEPTED => __('Заявка в клан принята'),
            self::APPLICATION_DECLINED => __('Заявка в клан отклонена'),
            self::APPLICATION_WITHDRAWN => __('Заявка в клан отозвана'),
            self::PROMOTED => __('Повышен'),
            self::DEMOTED => __('Понижен'),
            self::BANNED => __('Заблокирован'),
            self::UNBANNED => __('Разблокирован'),
            self::CLAN_CREATED => __('Клан создан'),
            self::CLAN_LVL_UP => __('Повышен уровень клана'),
            self::CLAN_DELETED => __('Клан удалён'),
            self::CLAN_RENAMED => __('Клан переименован'),
            self::BONUS_POINTS_EARNED => __('Заработаны бонусные очки'),
            self::SKILL_LEARNED => __('Изучен новый навык'),
            self::SKILL_UPGRADED => __('Изучен уровень навыка'),
            self::QUEST_STARTED => __('Клановый квест начат'),
            self::QUEST_COMPLETED => __('Клановый квест завершён'),
            self::QUEST_CANCELLED => __('Клановый квест отменён'),
            self::TREASURY_DEPOSIT => __('Пополнение казны'),
            self::TREASURY_WITHDRAW => __('Снятие из казны'),
            self::TAX_PAID => __('Оплата налога'),
            self::BOSS_KILLED => __('Повержен клан-босс'),
        };
    }
}
