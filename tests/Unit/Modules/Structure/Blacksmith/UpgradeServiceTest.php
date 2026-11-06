<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Domain\Enums\UpgradeScrollType;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeScrollGrade;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Tests\TestCase;

class UpgradeServiceTest extends TestCase
{
    public function test_it_returns_failure_for_max_level_item(): void
    {
        $service = new UpgradeService;
        [$user, $itemSlot, $baseScrollSlot] = $this->makeContext(
            money: 10000,
            level: UpgradeService::MAX_LEVEL,
        );

        $result = $service->upgrade($user, $itemSlot, $baseScrollSlot, null);

        self::assertFalse($result->success);
        self::assertSame(UpgradeService::MAX_LEVEL, $result->newLevel);
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

    public function test_pity_bonus_applies_only_below_level_fifteen(): void
    {
        $service = new UpgradeService;

        self::assertSame(25.0, $service->getSuccessChance(14, 5, false));
        self::assertSame(12.0, $service->getSuccessChance(15, 5, false));
        self::assertSame(2.0, $service->getSuccessChance(24, 10, false));
        self::assertSame(0.0, $service->getSuccessChance(25, 0, false));
    }

    public function test_scroll_grade_matches_target_level_by_rarity(): void
    {
        self::assertTrue(UpgradeScrollGrade::fits(ItemRarity::COMMON, 0));
        self::assertTrue(UpgradeScrollGrade::fits(ItemRarity::COMMON, 4));
        self::assertFalse(UpgradeScrollGrade::fits(ItemRarity::COMMON, 5));
        self::assertTrue(UpgradeScrollGrade::fits(ItemRarity::UNCOMMON, 5));
        self::assertTrue(UpgradeScrollGrade::fits(ItemRarity::LEGENDARY, 24));
        self::assertFalse(UpgradeScrollGrade::fits(ItemRarity::HEROIC, 0));
        self::assertSame(ItemRarity::EPIC, UpgradeScrollGrade::requiredRarity(15));
        self::assertNull(UpgradeScrollGrade::requiredRarity(25));
    }

    public function test_weapons_need_weapon_scroll_and_armor_or_shield_need_armor_scroll(): void
    {
        self::assertSame(UpgradeScrollType::BASE, UpgradeScrollGrade::requiredScrollType(ShareItemType::WEAPON));
        self::assertSame(UpgradeScrollType::BASE_ARMOR, UpgradeScrollGrade::requiredScrollType(ShareItemType::ARMOR));
        self::assertSame(UpgradeScrollType::BASE_ARMOR, UpgradeScrollGrade::requiredScrollType(ShareItemType::SHIELD));
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
