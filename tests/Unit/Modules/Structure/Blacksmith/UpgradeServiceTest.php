<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use PHPUnit\Framework\TestCase;

class UpgradeServiceTest extends TestCase
{
    public function test_it_returns_failure_for_max_level_item(): void
    {
        $service = new UpgradeService;
        [$user, $itemSlot, $baseScrollSlot] = $this->makeContext(
            money: 10000,
            level: 15,
        );

        $result = $service->upgrade($user, $itemSlot, $baseScrollSlot, null);

        self::assertFalse($result->success);
        self::assertSame(15, $result->newLevel);
        self::assertSame('Предмет уже достиг максимального уровня заточки.', $result->message);
    }

    public function test_it_returns_failure_when_user_has_not_enough_money(): void
    {
        $service = new UpgradeService;
        [$user, $itemSlot, $baseScrollSlot] = $this->makeContext(
            money: 0,
            level: 3,
        );

        $result = $service->upgrade($user, $itemSlot, $baseScrollSlot, null);

        self::assertFalse($result->success);
        self::assertSame(3, $result->newLevel);
        self::assertStringContainsString('Недостаточно монет.', $result->message);
    }

    /**
     * @return array{0: User, 1: Backpack, 2: Backpack}
     */
    private function makeContext(int $money, int $level): array
    {
        $user = new User;
        $user->money = $money;

        $itemInfo = new ShareItem;
        $itemInfo->name = 'Меч';
        $item = new Item;
        $item->upgrade_lvl = $level;
        $item->upgrade_pity = 0;
        $item->upgrade_fail_streak = 0;
        $item->setRelation('itemInfo', $itemInfo);

        $scrollInfo = new ShareItem;
        $scrollInfo->name = 'Свиток';
        $scrollItem = new Item;
        $scrollItem->setRelation('itemInfo', $scrollInfo);

        $itemSlot = new Backpack;
        $itemSlot->setRelation('item', $item);

        $baseScrollSlot = new Backpack;
        $baseScrollSlot->count = 1;
        $baseScrollSlot->setRelation('item', $scrollItem);

        return [$user, $itemSlot, $baseScrollSlot];
    }
}
