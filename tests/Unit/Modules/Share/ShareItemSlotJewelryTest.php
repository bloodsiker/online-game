<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Share;

use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemType;
use PHPUnit\Framework\TestCase;

class ShareItemSlotJewelryTest extends TestCase
{
    public function test_jewelry_slot_counts(): void
    {
        $this->assertCount(5, ShareItemSlot::RING->jewelryColumns());
        $this->assertCount(2, ShareItemSlot::EARRING->jewelryColumns());
        $this->assertCount(2, ShareItemSlot::NECKLACE->jewelryColumns());
        $this->assertFalse(ShareItemSlot::BELT->isJewelry());
    }

    public function test_jewelry_types_are_equipment(): void
    {
        foreach ([ShareItemType::RING, ShareItemType::EARRING, ShareItemType::NECKLACE] as $type) {
            $this->assertTrue($type->isEquipment());
            $this->assertContains($type, ShareItemType::group('main'));
        }
    }
}
