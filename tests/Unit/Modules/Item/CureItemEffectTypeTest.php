<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Item;

use App\Modules\Effect\Domain\Enums\ActiveEffectType;
use App\Modules\Effect\Domain\Services\DebuffImmunityService;
use App\Modules\Share\Domain\Enums\ItemEffectType;
use PHPUnit\Framework\TestCase;

class CureItemEffectTypeTest extends TestCase
{
    public function test_cure_types_map_to_debuffs_and_are_instant(): void
    {
        self::assertSame(ActiveEffectType::POISON, ItemEffectType::CURE_POISON->curedEffectType());
        self::assertSame(ActiveEffectType::BLEED, ItemEffectType::CURE_BLEED->curedEffectType());
        self::assertSame(ActiveEffectType::BURN, ItemEffectType::CURE_BURN->curedEffectType());
        self::assertSame(ActiveEffectType::PARALYSIS, ItemEffectType::CURE_PARALYSIS->curedEffectType());
        self::assertNull(ItemEffectType::HEAL_HP->curedEffectType());
        self::assertTrue(ItemEffectType::CURE_POISON->isInstant());
    }

    public function test_immunity_marker_slug(): void
    {
        self::assertSame('immunity_burn', DebuffImmunityService::slugFor(ActiveEffectType::BURN));
    }
}
