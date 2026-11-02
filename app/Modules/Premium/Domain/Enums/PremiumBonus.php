<?php

declare(strict_types=1);

namespace App\Modules\Premium\Domain\Enums;

enum PremiumBonus: string
{
    case EXPERIENCE = 'experience_percent';
    case MONSTER_MONEY = 'monster_money_percent';
    case GATHERING_SPEED = 'gathering_speed_percent';
    case BONUS_RESOURCE = 'bonus_resource_chance_percent';
    case BAG_SLOTS = 'bag_slots';
    case ANONYMOUS_AUCTION = 'anonymous_auction';
    case ANONYMOUS_EXCHANGE = 'anonymous_exchange';
    case AUTO_LOOT = 'auto_loot';

    public function label(): string
    {
        return match ($this) {
            self::EXPERIENCE => 'Бонус опыта',
            self::MONSTER_MONEY => 'Монеты с монстров',
            self::GATHERING_SPEED => 'Скорость добычи',
            self::BONUS_RESOURCE => 'Шанс дополнительного ресурса',
            self::BAG_SLOTS => 'Дополнительные слоты рюкзака',
            self::ANONYMOUS_AUCTION => 'Анонимная работа с аукционом',
            self::ANONYMOUS_EXCHANGE => 'Анонимная работа с биржей',
            self::AUTO_LOOT => 'Автосбор добычи с монстров',
        };
    }

    public function isBoolean(): bool
    {
        return in_array($this, [self::ANONYMOUS_AUCTION, self::ANONYMOUS_EXCHANGE, self::AUTO_LOOT], true);
    }

    public function max(): int
    {
        return match ($this) {
            self::ANONYMOUS_AUCTION, self::ANONYMOUS_EXCHANGE, self::AUTO_LOOT => 1,
            self::BONUS_RESOURCE => 100,
            default => 1000,
        };
    }

    public function display(int|float $value): string
    {
        if ($this->isBoolean()) {
            return $value > 0 ? __('premium.available') : __('premium.unavailable');
        }

        return '+'.rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.').($this === self::BAG_SLOTS ? '' : '%');
    }
}
