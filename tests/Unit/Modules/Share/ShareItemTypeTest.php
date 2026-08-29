<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Share;

use App\Modules\Share\Domain\Enums\ShareItemType;
use PHPUnit\Framework\TestCase;

class ShareItemTypeTest extends TestCase
{
    public function test_tool_is_a_main_equipment_type(): void
    {
        $this->assertSame('Инструмент', ShareItemType::TOOL->label());
        $this->assertTrue(ShareItemType::TOOL->isEquipment());
        $this->assertContains(ShareItemType::TOOL, ShareItemType::group('main'));
    }
}
