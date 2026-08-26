<?php

declare(strict_types=1);

namespace App\Modules\Chat\Domain\Enums;

enum ChatChannel: string
{
    case Main = 'main';
    case Location = 'location';
    case Trade = 'trade';
    case Clan = 'clan';
    case Party = 'party';
    case Private = 'private';
    case System = 'system';

    public function label(): string
    {
        return match ($this) {
            self::Main => 'Основной',
            self::Location => 'Местность',
            self::Trade => 'Торговый',
            self::Clan => 'Клан',
            self::Party => 'Группа',
            self::Private => 'Личные',
            self::System => 'Системные',
        };
    }
}
