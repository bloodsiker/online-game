<?php

declare(strict_types=1);

namespace App\Modules\Effect\Domain\Enums;

enum ActiveEffectType: string
{
    case STUN = 'stun';
    case PARALYSIS = 'paralysis';
    case POISON = 'poison';
    case BLEED = 'bleed';
    case BURN = 'burn';
    case REGEN = 'regen';

    public function isStun(): bool
    {
        return $this === self::STUN;
    }

    public function isControl(): bool
    {
        return match ($this) {
            self::STUN, self::PARALYSIS => true,
            default => false,
        };
    }

    public function isDoT(): bool
    {
        return match ($this) {
            self::POISON, self::BLEED, self::BURN => true,
            default => false,
        };
    }

    public function isHoT(): bool
    {
        return $this === self::REGEN;
    }

    public function emoji(): string
    {
        return match ($this) {
            self::STUN => '💫',
            self::PARALYSIS => '⚡',
            self::POISON => '☠️',
            self::BLEED => '🩸',
            self::BURN => '🔥',
            self::REGEN => '💚',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::STUN => 'Оглушение',
            self::PARALYSIS => 'Паралич',
            self::POISON => 'Отравление',
            self::BLEED => 'Кровотечение',
            self::BURN => 'Ожог',
            self::REGEN => 'Регенерация',
        };
    }
}
