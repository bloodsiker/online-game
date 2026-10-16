<?php

declare(strict_types=1);

namespace App\Modules\Clan\Domain\Enums;

enum ClanPermission: string
{
    case INVITE = 'invite';
    case KICK = 'kick';
    case CHAT = 'chat';
    case CHANGE_PERMS = 'change_perms';
    case CHANGE_RANKS = 'change_ranks';
    case CHANGE_NEWS = 'change_news';
    case DEPOSIT = 'deposit';
    case WITHDRAW_MONEY = 'withdraw_money';
    case WITHDRAW_ITEMS = 'withdraw_items';
    case LEARN_SKILL = 'learn_skill';
    case PAY_TAX = 'pay_tax';
    case START_CLAN_BOSS = 'start_clan_boss';

    public function bit(): int
    {
        return match ($this) {
            self::INVITE => 1,
            self::KICK => 2,
            self::CHANGE_PERMS => 32,
            self::CHANGE_RANKS => 64,
            self::CHANGE_NEWS => 128,
            self::DEPOSIT => 512,
            self::WITHDRAW_MONEY => 1024,
            self::CHAT => 2048,
            self::WITHDRAW_ITEMS => 8192,
            self::LEARN_SKILL => 16384,
            self::PAY_TAX => 32768,
            self::START_CLAN_BOSS => 65536,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::INVITE => __('Принимать в клан'),
            self::KICK => __('Исключать из клана'),
            self::CHAT => __('Писать в клан чат'),
            self::CHANGE_PERMS => __('Менять полномочия'),
            self::CHANGE_RANKS => __('Изменение званий'),
            self::CHANGE_NEWS => __('Изменение новостей'),
            self::DEPOSIT => __('Добавление в ячейку'),
            self::WITHDRAW_MONEY => __('Забирать деньги'),
            self::WITHDRAW_ITEMS => __('Забирать вещи'),
            self::LEARN_SKILL => __('Изучать навыки'),
            self::PAY_TAX => __('Платить налог'),
            self::START_CLAN_BOSS => __('Начинать бой с клан-боссом'),
        };
    }

    public function columnTitle(): string
    {
        return match ($this) {
            self::INVITE => __('Принимать') . ' <br>' . __('в клан'),
            self::KICK => __('Исключать') . ' <br>' . __('из клана'),
            self::CHAT => __('Писать в') . ' <br>' . __('клан чат'),
            self::CHANGE_PERMS => __('Менять') . ' <br>' . __('полномочия'),
            self::CHANGE_RANKS => __('Изменение') . '<br>' . __('званий'),
            self::CHANGE_NEWS => __('Изменение') . '<br>' . __('новостей'),
            self::DEPOSIT => __('Добавление') . '<br>' . __('в ячейку'),
            self::WITHDRAW_MONEY => __('Забирать') . '<br>' . __('деньги'),
            self::WITHDRAW_ITEMS => __('Забирать') . '<br>' . __('вещи'),
            self::LEARN_SKILL => __('Изучать') . '<br>' . __('навыки'),
            self::PAY_TAX => __('Платить') . '<br>' . __('налог'),
            self::START_CLAN_BOSS => __('Начинать бой') . '<br>' . __('с клан-боссом'),
        };
    }

    public static function allBits(): int
    {
        return array_reduce(self::cases(), fn (int $carry, self $p) => $carry | $p->bit(), 0);
    }
}
