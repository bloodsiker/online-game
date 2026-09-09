<?php

declare(strict_types=1);

namespace App\Modules\Player\Domain\Enums;

enum InjuryBodyPart: string
{
    case HEAD = 'head';
    case SHOULDERS = 'shoulders';
    case FOREARMS = 'forearms';
    case LEFT_HAND = 'left_hand';
    case RIGHT_HAND = 'right_hand';
    case TORSO = 'torso';
    case CHEST = 'chest';
    case LEGS = 'legs';
    case FEET = 'feet';

    public function label(): string
    {
        return match ($this) {
            self::HEAD => 'Голова',
            self::SHOULDERS => 'Плечи',
            self::FOREARMS => 'Предплечья',
            self::LEFT_HAND => 'Левая рука',
            self::RIGHT_HAND => 'Правая рука',
            self::TORSO => 'Туловище',
            self::CHEST => 'Грудь',
            self::LEGS => 'Ноги',
            self::FEET => 'Ступни',
        };
    }

    public function equipmentColumn(): string
    {
        return match ($this) {
            self::HEAD => 'helmet',
            self::SHOULDERS => 'shoulder',
            self::FOREARMS => 'forearm',
            self::LEFT_HAND => 'hand_left',
            self::RIGHT_HAND => 'hand_right',
            self::TORSO => 'armor',
            self::CHEST => 'chain_armor',
            self::LEGS => 'legging',
            self::FEET => 'shoes',
        };
    }

    /** @return list<string> */
    public function affectedStats(): array
    {
        return match ($this) {
            self::HEAD => ['intuition', 'wisdom', 'intelligence'],
            self::SHOULDERS => ['strength', 'endurance'],
            self::FOREARMS, self::LEFT_HAND, self::RIGHT_HAND => ['strength'],
            self::TORSO, self::CHEST => ['endurance'],
            self::LEGS => ['agility', 'endurance'],
            self::FEET => ['agility'],
        };
    }

    public function isHand(): bool
    {
        return $this === self::LEFT_HAND || $this === self::RIGHT_HAND;
    }
}
