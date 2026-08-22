<?php

declare(strict_types=1);

namespace App\Modules\Dungeon\Domain\Enums;

enum DungeonDeathBehavior: string
{
    case EXIT = 'exit';
    case RETURN_TO_START = 'return_to_start';
    case KICK_CAN_REENTER = 'kick_can_reenter';

    public function label(): string
    {
        return match ($this) {
            self::EXIT => 'Смерть завершает поход',
            self::RETURN_TO_START => 'Возврат к началу данжа',
            self::KICK_CAN_REENTER => 'Выход наружу с возможностью вернуться',
        };
    }
}
