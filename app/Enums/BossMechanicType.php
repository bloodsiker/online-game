<?php

namespace App\Enums;

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
        return match($this) {
            self::ENRAGE => \App\Services\Combat\Boss\Mechanics\EnrageMechanic::class,
            self::REGENERATION => \App\Services\Combat\Boss\Mechanics\RegenerationMechanic::class,
            self::SUMMON_MINIONS => \App\Services\Combat\Boss\Mechanics\SummonMinionsMechanic::class,
            self::AOE_ATTACK => \App\Services\Combat\Boss\Mechanics\AoeAttackMechanic::class,
            self::SHIELD => \App\Services\Combat\Boss\Mechanics\ShieldMechanic::class,
            self::DEATH_EXPLOSION => \App\Services\Combat\Boss\Mechanics\DeathExplosionMechanic::class,
            self::TELEPORT => \App\Services\Combat\Boss\Mechanics\TeleportMechanic::class,
            self::BUFF_SELF => \App\Services\Combat\Boss\Mechanics\BuffSelfMechanic::class,
            self::DEBUFF_PLAYER => \App\Services\Combat\Boss\Mechanics\DebuffPlayerMechanic::class,
            self::LIFE_DRAIN => \App\Services\Combat\Boss\Mechanics\LifeDrainMechanic::class,
            self::REFLECT_DAMAGE => \App\Services\Combat\Boss\Mechanics\ReflectDamageMechanic::class,
            self::IMMUNITY => \App\Services\Combat\Boss\Mechanics\ImmunityMechanic::class,
            self::BERSERK => \App\Services\Combat\Boss\Mechanics\BerserkMechanic::class,
            self::MIRROR_IMAGE => \App\Services\Combat\Boss\Mechanics\MirrorImageMechanic::class,
            self::DAMAGE_TO_HEAL => \App\Services\Combat\Boss\Mechanics\DamageToHealMechanic::class,
        };
    }

    /**
     * Отримати назву механіки
     */
    public function getLabel(): string
    {
        return match($this) {
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
        return match($this) {
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
            ->mapWithKeys(fn(self $type) => [
                $type->value => [
                    'label' => $type->getLabel(),
                    'icon' => $type->getIcon(),
                    'value' => $type->value,
                ]
            ])
            ->toArray();
    }
}
