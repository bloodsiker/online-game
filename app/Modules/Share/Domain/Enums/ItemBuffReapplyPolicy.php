<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ItemBuffReapplyPolicy: string
{
    case REFRESH = 'refresh';
    case BLOCK = 'block';

    public function label(): string
    {
        return match ($this) {
            self::REFRESH => 'Обновить длительность',
            self::BLOCK => 'Запретить, пока эффект активен',
        };
    }
}
