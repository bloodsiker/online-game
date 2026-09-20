<?php

declare(strict_types=1);

namespace App\Modules\Moderation\Domain\Enums;

enum CommunicationScope: string
{
    case Forum = 'forum';
    case Chat = 'chat';

    public function label(): string
    {
        return match ($this) {
            self::Forum => 'Форум',
            self::Chat => 'Игровой чат',
        };
    }

    public function restrictionLabel(): string
    {
        return match ($this) {
            self::Forum => 'на форуме',
            self::Chat => 'в игровом чате',
        };
    }
}
