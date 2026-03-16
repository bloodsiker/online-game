<?php

namespace App\Enums;

enum ClanPermission: string
{
    case INVITE          = 'invite';
    case KICK            = 'kick';
    case CHAT            = 'chat';
    case CHANGE_PERMS    = 'change_perms';
    case CHANGE_RANKS    = 'change_ranks';
    case CHANGE_NEWS     = 'change_news';
    case DEPOSIT         = 'deposit';
    case WITHDRAW_MONEY  = 'withdraw_money';
    case WITHDRAW_ITEMS  = 'withdraw_items';

    public function bit(): int
    {
        return match ($this) {
            self::INVITE         => 1,
            self::KICK           => 2,
            self::CHANGE_PERMS   => 32,
            self::CHANGE_RANKS   => 64,
            self::CHANGE_NEWS    => 128,
            self::DEPOSIT        => 512,
            self::WITHDRAW_MONEY => 1024,
            self::CHAT           => 2048,
            self::WITHDRAW_ITEMS => 8192,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::INVITE         => 'Принимать в клан',
            self::KICK           => 'Исключать из клана',
            self::CHAT           => 'Писать в клан чат',
            self::CHANGE_PERMS   => 'Менять полномочия',
            self::CHANGE_RANKS   => 'Изменение званий',
            self::CHANGE_NEWS    => 'Изменение новостей',
            self::DEPOSIT        => 'Добавление в ячейку',
            self::WITHDRAW_MONEY => 'Забирать деньги',
            self::WITHDRAW_ITEMS => 'Забирать вещи',
        };
    }

    public function columnTitle(): string
    {
        return match ($this) {
            self::INVITE         => 'Принимать <br>в клан',
            self::KICK           => 'Исключать <br>из клана',
            self::CHAT           => 'Писать в <br>клан чат',
            self::CHANGE_PERMS   => 'Менять <br>полномочия',
            self::CHANGE_RANKS   => 'Изменение<br>званий',
            self::CHANGE_NEWS    => 'Изменение<br>новостей',
            self::DEPOSIT        => 'Добавление<br>в ячейку',
            self::WITHDRAW_MONEY => 'Забирать<br>деньги',
            self::WITHDRAW_ITEMS => 'Забирать<br>вещи',
        };
    }

    public static function allBits(): int
    {
        return array_reduce(self::cases(), fn (int $carry, self $p) => $carry | $p->bit(), 0);
    }
}
