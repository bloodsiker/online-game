<?php

declare(strict_types=1);

namespace App\Modules\Share\Domain\Enums;

enum ShareItemStatType: string
{
    case ATTACK_MIN = 'attack_min';
    case ATTACK_MAX = 'attack_max';
    case ARMOR = 'armor';
    case BAG_SLOT = 'bag_slot';
    case BELT_SLOT = 'belt_slot';
    case AGILITY = 'agility';
    case INTUITION = 'intuition';
    case WISDOM = 'wisdom';
    case INTELLIGENCE = 'intelligence';
    case DODGE = 'dodge';
    case CRITICAL = 'critical';
    case MAGIC_ATTACK = 'magic_attack';
    case MAGIC_RESISTANCE = 'magic_resistance';
    case MAGIC_CRITICAL = 'magic_critical';
    case HP_MAX = 'hp_max';
    case CRIT_DAMAGE = 'crit_damage';
    case MAGIC_CRIT_DAMAGE = 'magic_crit_damage';
    case ENDURANCE = 'endurance';
    case BLOCK_CHANCE = 'block_chance';
    case BLOCK_FLAT = 'block_flat';
    case BLOCK_PERCENT = 'block_percent';

    public function label(): string
    {
        return match ($this) {
            self::ATTACK_MIN => __('Атака мин.'),
            self::ATTACK_MAX => __('Атака макс.'),
            self::ARMOR => __('Броня'),
            self::BAG_SLOT => __('Слот сумки'),
            self::BELT_SLOT => __('Слот пояса'),
            self::AGILITY => __('Ловкость'),
            self::INTUITION => __('Интуиция'),
            self::WISDOM => __('Мудрость'),
            self::INTELLIGENCE => __('Интеллект'),
            self::DODGE => __('Уворот'),
            self::CRITICAL => __('Критический удар'),
            self::MAGIC_ATTACK => __('Магическая атака'),
            self::MAGIC_RESISTANCE => __('Магическое сопротивление'),
            self::MAGIC_CRITICAL => __('Шанс магического крита, %'),
            self::HP_MAX => __('Уровень жизни'),
            self::CRIT_DAMAGE => __('Сила крит. удара'),
            self::MAGIC_CRIT_DAMAGE => __('Сила магического крит. удара'),
            self::ENDURANCE => __('Выносливость'),
            self::BLOCK_CHANCE => __('Шанс блока щитом, %'),
            self::BLOCK_FLAT => __('Блок щитом (фикс.)'),
            self::BLOCK_PERCENT => __('Блок щитом, %'),
        };
    }
}
