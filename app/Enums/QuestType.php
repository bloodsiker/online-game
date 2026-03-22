<?php

namespace App\Enums;

enum QuestType: string
{
    case ONE_TIME = 'one_time';
    case REPEATABLE = 'repeatable';
    case MAIN = 'main';
    case CLAN = 'clan';
    case REPUTATION = 'reputation';

    public function label(): string
    {
        return match($this) {
            self::ONE_TIME   => 'Одноразовый',
            self::REPEATABLE => 'Повторяемый',
            self::MAIN       => 'Главный',
            self::CLAN       => 'Клановый',
            self::REPUTATION => 'Репутационный',
        };
    }

    public function isRepeatable(): bool
    {
        return $this === self::REPEATABLE;
    }

    public function isMain(): bool
    {
        return $this === self::MAIN;
    }

    public function isOneTime(): bool
    {
        return $this === self::ONE_TIME;
    }

    public function isClan(): bool
    {
        return $this === self::CLAN;
    }

    public function isReputation(): bool
    {
        return $this === self::REPUTATION;
    }
}
