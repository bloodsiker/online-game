<?php

namespace App\Modules\Item\Application\ItemEffect;

use App\Modules\Item\Application\ItemEffect\Strategies\BuffAttackStrategy;
use App\Modules\Item\Application\ItemEffect\Strategies\BuffDefenseStrategy;
use App\Modules\Item\Application\ItemEffect\Strategies\HealHpStrategy;
use App\Modules\Item\Application\ItemEffect\Strategies\HealMpStrategy;
use App\Modules\Item\Application\ItemEffect\Strategies\ItemEffectStrategyInterface;
use App\Modules\Share\Domain\Enums\ItemEffectType;

class ItemEffectStrategyFactory
{
    /** @var array<string, class-string<ItemEffectStrategyInterface>> */
    private static array $strategies = [
        ItemEffectType::HEAL_HP->value => HealHpStrategy::class,
        ItemEffectType::HEAL_MP->value => HealMpStrategy::class,
        ItemEffectType::BUFF_ATTACK->value => BuffAttackStrategy::class,
        ItemEffectType::BUFF_DEFENSE->value => BuffDefenseStrategy::class,
    ];

    public static function make(ItemEffectType $type): ItemEffectStrategyInterface
    {
        $strategyClass = self::$strategies[$type->value] ?? null;

        if ($strategyClass === null) {
            throw new \InvalidArgumentException("No strategy found for effect type: {$type->value}");
        }

        return app($strategyClass);
    }
}
