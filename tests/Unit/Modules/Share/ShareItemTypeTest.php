<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Share;

use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use PHPUnit\Framework\TestCase;

class ShareItemTypeTest extends TestCase
{
    public function test_tool_is_a_main_equipment_type(): void
    {
        $this->assertSame('Инструмент', ShareItemType::TOOL->label());
        $this->assertTrue(ShareItemType::TOOL->isEquipment());
        $this->assertContains(ShareItemType::TOOL, ShareItemType::group('main'));
        $this->assertSame('Рыба', ShareItemType::FISH->label());
        $this->assertSame('Драгоценные камни', ShareItemType::PRECIOUS_GEM->label());
        $this->assertTrue(ShareItemType::PLANT->isGatheringResource());
        $this->assertTrue(ShareItemType::WOOD->isGatheringResource());
        $this->assertTrue(ShareItemType::FISH->isGatheringResource());
        $this->assertTrue(ShareItemType::PRECIOUS_GEM->isGatheringResource());
    }

    public function test_gathering_profile_scales_with_resource_rarity(): void
    {
        $this->assertSame(1, ItemRarity::COMMON->gatheringRequiredSkillLevel());
        $this->assertSame(2, ItemRarity::COMMON->gatheringExperience());
        $this->assertSame(8, ItemRarity::COMMON->defaultGatheringSeconds());

        $this->assertSame(300, ItemRarity::HEROIC->gatheringRequiredSkillLevel());
        $this->assertSame(17, ItemRarity::HEROIC->gatheringExperience());
        $this->assertSame(60, ItemRarity::HEROIC->defaultGatheringSeconds());
    }
}
