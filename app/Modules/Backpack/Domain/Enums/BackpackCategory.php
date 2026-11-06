<?php

declare(strict_types=1);

namespace App\Modules\Backpack\Domain\Enums;

enum BackpackCategory: string
{
    case MAIN = 'main';
    case MISC = 'misc';
    case KEY = 'key';
    case QUEST = 'quest';
    case ARTIFACT = 'artifact';
    case GIFT = 'gift';

    public function label(): string
    {
        return match ($this) {
            self::MAIN => __('Вещи'), self::MISC => __('Разное'), self::KEY => __('Ключи'),
            self::QUEST => __('Квесты'), self::ARTIFACT => __('Артефакты'), self::GIFT => __('Подарки'),
        };
    }
}
