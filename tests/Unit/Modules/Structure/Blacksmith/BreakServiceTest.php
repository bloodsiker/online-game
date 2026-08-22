<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanBreakItem;
use App\Modules\Structure\Blacksmith\Domain\Services\BreakService;
use PHPUnit\Framework\TestCase;

class BreakServiceTest extends TestCase
{
    public function test_it_returns_success_for_breakable_item(): void
    {
        $service = new BreakService(new CanBreakItem);
        $item = new ShareItem;
        $item->type = ShareItemType::WEAPON;
        $item->break_crystal = 4;

        $result = $service->salvage($item);

        self::assertTrue($result->success);
        self::assertSame(4, $result->crystalCount);
    }

    public function test_it_returns_failure_for_non_breakable_item(): void
    {
        $service = new BreakService(new CanBreakItem);
        $item = new ShareItem;
        $item->type = ShareItemType::RUNE;
        $item->break_crystal = 0;

        $result = $service->salvage($item);

        self::assertFalse($result->success);
        self::assertSame('Предмет нельзя разбить на кристаллы.', $result->message);
    }
}
