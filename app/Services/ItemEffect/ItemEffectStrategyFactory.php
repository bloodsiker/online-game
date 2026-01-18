<?php

namespace App\Services\ItemEffect;

use App\Enums\ItemEffectType;
use App\Services\ItemEffect\Strategies\BuffAttackStrategy;
use App\Services\ItemEffect\Strategies\HealHpStrategy;
use App\Services\ItemEffect\Strategies\HealMpStrategy;
use app\Services\ItemEffect\Strategies\ItemEffectStrategyInterface;

class ItemEffectStrategyFactory
{
    /** @var array<ItemEffectType, class-string<ItemEffectStrategyInterface>> */
    private static array $strategies = [
        ItemEffectType::HEAL_HP->value => HealHpStrategy::class,
        ItemEffectType::HEAL_MP->value => HealMpStrategy::class,
        ItemEffectType::BUFF_ATTACK->value => BuffAttackStrategy::class,
    ];

    public static function make(ItemEffectType $type): ItemEffectStrategyInterface
    {
        $strategyClass = self::$strategies[$type->value] ?? null;

        if (null === $strategyClass) {
            throw new \InvalidArgumentException("No strategy found for effect type: {$type->value}");
        }

        return new $strategyClass();
    }
}
