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
    case HP_MAX = 'hp_max';
    case CRIT_DAMAGE = 'crit_damage';
    case ENDURANCE = 'endurance';
    case BLOCK_CHANCE = 'block_chance';
    case BLOCK_FLAT = 'block_flat';
    case BLOCK_PERCENT = 'block_percent';

    public function label(): string
    {
        return match ($this) {
            self::ATTACK_MIN => 'Атака мин.',
            self::ATTACK_MAX => 'Атака макс.',
            self::ARMOR => 'Броня',
            self::BAG_SLOT => 'Слот сумки',
            self::BELT_SLOT => 'Слот пояса',
            self::AGILITY => 'Ловкость',
            self::INTUITION => 'Интуиция',
            self::WISDOM => 'Мудрость',
            self::INTELLIGENCE => 'Интеллект',
            self::DODGE => 'Уворот',
            self::CRITICAL => 'Критический удар',
            self::MAGIC_ATTACK => 'Магическая атака',
            self::MAGIC_RESISTANCE => 'Магическое сопротивление',
            self::HP_MAX => 'Уровень жизни',
            self::CRIT_DAMAGE => 'Сила крит. удара',
            self::ENDURANCE => 'Выносливость',
            self::BLOCK_CHANCE => 'Шанс блока щитом, %',
            self::BLOCK_FLAT => 'Блок щитом (фикс.)',
            self::BLOCK_PERCENT => 'Блок щитом, %',
        };
    }
}
