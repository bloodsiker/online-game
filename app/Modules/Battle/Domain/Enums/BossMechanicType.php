<?php

namespace App\Modules\Battle\Domain\Enums;

use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\AoeAttackMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\BerserkMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\BuffSelfMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\DamageToHealMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\DeathExplosionMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\DebuffPlayerMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\EnrageMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\ImmunityMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\LifeDrainMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\MirrorImageMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\ReflectDamageMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\RegenerationMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\ShieldMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\SummonMinionsMechanic;
use App\Modules\Battle\Application\Services\Combat\Boss\Mechanics\TeleportMechanic;

enum BossMechanicType: string
{
    case ENRAGE = 'enrage';
    case REGENERATION = 'regeneration';
    case SUMMON_MINIONS = 'summon_minions';
    case AOE_ATTACK = 'aoe_attack';
    case SHIELD = 'shield';
    case DEATH_EXPLOSION = 'death_explosion';
    case TELEPORT = 'teleport';
    case BUFF_SELF = 'buff_self';
    case DEBUFF_PLAYER = 'debuff_player';
    case LIFE_DRAIN = 'life_drain';
    case REFLECT_DAMAGE = 'reflect_damage';
    case IMMUNITY = 'immunity';
    case BERSERK = 'berserk';
    case MIRROR_IMAGE = 'mirror_image';
    case DAMAGE_TO_HEAL = 'damage_to_heal';

    /**
     * Отримати клас механіки
     */
    public function getClass(): string
    {
        return match ($this) {
            self::ENRAGE => EnrageMechanic::class,
            self::REGENERATION => RegenerationMechanic::class,
            self::SUMMON_MINIONS => SummonMinionsMechanic::class,
            self::AOE_ATTACK => AoeAttackMechanic::class,
            self::SHIELD => ShieldMechanic::class,
            self::DEATH_EXPLOSION => DeathExplosionMechanic::class,
            self::TELEPORT => TeleportMechanic::class,
            self::BUFF_SELF => BuffSelfMechanic::class,
            self::DEBUFF_PLAYER => DebuffPlayerMechanic::class,
            self::LIFE_DRAIN => LifeDrainMechanic::class,
            self::REFLECT_DAMAGE => ReflectDamageMechanic::class,
            self::IMMUNITY => ImmunityMechanic::class,
            self::BERSERK => BerserkMechanic::class,
            self::MIRROR_IMAGE => MirrorImageMechanic::class,
            self::DAMAGE_TO_HEAL => DamageToHealMechanic::class,
        };
    }

    /**
     * Отримати назву механіки
     */
    public function getLabel(): string
    {
        return match ($this) {
            self::ENRAGE => 'Ярость',
            self::REGENERATION => 'Регенерация',
            self::SUMMON_MINIONS => 'Вызов миньонов',
            self::AOE_ATTACK => 'Массовая атака',
            self::SHIELD => 'Щит',
            self::DEATH_EXPLOSION => 'Взрыв при смерти',
            self::TELEPORT => 'Телепортация',
            self::BUFF_SELF => 'Усиление',
            self::DEBUFF_PLAYER => 'Ослабление игрока',
            self::LIFE_DRAIN => 'Вампиризм',
            self::REFLECT_DAMAGE => 'Отражение урона',
            self::IMMUNITY => 'Иммунитет',
            self::BERSERK => 'Берсерк',
            self::MIRROR_IMAGE => 'Зеркальное отражение',
            self::DAMAGE_TO_HEAL => 'Конвертация урона в лечение',
        };
    }

    /**
     * Отримати іконку/емодзі
     */
    public function getIcon(): string
    {
        return match ($this) {
            self::ENRAGE => '💢',
            self::REGENERATION => '💚',
            self::SUMMON_MINIONS => '👥',
            self::AOE_ATTACK => '💥',
            self::SHIELD => '🛡️',
            self::DEATH_EXPLOSION => '💀',
            self::TELEPORT => '🌀',
            self::BUFF_SELF => '⬆️',
            self::DEBUFF_PLAYER => '⬇️',
            self::LIFE_DRAIN => '🩸',
            self::REFLECT_DAMAGE => '🔁',
            self::IMMUNITY => '✨',
            self::BERSERK => '😡',
            self::MIRROR_IMAGE => '👯',
            self::DAMAGE_TO_HEAL => '💉',
        };
    }

    /**
     * Перевірка чи існує алиас
     */
    public static function tryFromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    /**
     * Отримати всі доступні механіки для UI
     */
    public static function getAllForSelect(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type) => [
                $type->value => [
                    'label' => $type->getLabel(),
                    'icon' => $type->getIcon(),
                    'value' => $type->value,
                ],
            ])
            ->toArray();
    }
}
