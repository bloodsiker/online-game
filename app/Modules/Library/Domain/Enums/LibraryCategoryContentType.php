<?php

declare(strict_types=1);

namespace App\Modules\Library\Domain\Enums;

enum LibraryCategoryContentType: string
{
    case ARTICLES = 'articles';
    case MONSTERS = 'monsters';
    case MAPS = 'maps';

    public function label(): string
    {
        return match ($this) {
            self::ARTICLES => 'Статьи',
            self::MONSTERS => 'Бестиарий — каталог монстров',
            self::MAPS => 'Карта мира — дерево карт',
        };
    }
}
