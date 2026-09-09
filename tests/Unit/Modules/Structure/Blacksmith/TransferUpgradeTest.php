<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Domain\Enums\ShareItemSlot;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Structure\Blacksmith\Application\DTOs\TransferUpgradeDTO;
use App\Modules\Structure\Blacksmith\Application\UseCases\TransferUpgrade;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanTransferUpgrade;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanUpgradeItem;
use App\Modules\Structure\Blacksmith\Domain\Services\UpgradeTransferService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TransferUpgradeTest extends TestCase
{
    public function test_it_transfers_level_and_consumes_one_transgressor(): void
    {
        $user = new User;
        $user->id = 7;
        [$sourceSlot, $source] = $this->equipmentSlot(101, 'Старые сапоги', ShareItemType::ARMOR, ShareItemSlot::SHOES, 8);
        [$targetSlot, $target] = $this->equipmentSlot(202, 'Новые сапоги', ShareItemType::ARMOR, ShareItemSlot::SHOES, 0);
        $source->shouldReceive('save')->once()->andReturnTrue();
        $target->shouldReceive('save')->once()->andReturnTrue();

        $transgressor = $this->transgressorSlot(ItemRarity::HEROIC, 2);
        $transgressor->shouldReceive('save')->once()->andReturnTrue();

        $inventory = Mockery::mock(BlacksmithInventoryRepository::class);
        $inventory->shouldReceive('findOwnedSlotsForUpdate')
            ->once()
            ->with($user, [101, 202])
            ->andReturn(collect([$sourceSlot, $targetSlot]));
        $inventory->shouldReceive('findOwnedSlotByShareItemIdForUpdate')
            ->once()
            ->with($user, 501)
            ->andReturn($transgressor);

        $transaction = Mockery::mock(TransactionManager::class);
        $transaction->shouldReceive('run')->once()->andReturnUsing(fn (callable $callback) => $callback());

        $result = (new TransferUpgrade(
            $inventory,
            $transaction,
            new CanTransferUpgrade(new CanUpgradeItem),
            new UpgradeTransferService,
        ))->execute(new TransferUpgradeDTO($user, 101, 202, 501));

        self::assertTrue($result->ok);
        self::assertSame(0, $source->upgrade_lvl);
        self::assertSame(8, $target->upgrade_lvl);
        self::assertSame(1, $transgressor->count);
    }

    public function test_it_rejects_different_equipment_slots(): void
    {
        $user = new User;
        $user->id = 7;
        [$sourceSlot, $source] = $this->equipmentSlot(101, 'Сапоги', ShareItemType::ARMOR, ShareItemSlot::SHOES, 5);
        [$targetSlot, $target] = $this->equipmentSlot(202, 'Перчатки', ShareItemType::ARMOR, ShareItemSlot::GLOVES, 0);
        $source->shouldNotReceive('save');
        $target->shouldNotReceive('save');

        $inventory = Mockery::mock(BlacksmithInventoryRepository::class);
        $inventory->shouldReceive('findOwnedSlotsForUpdate')->once()->andReturn(collect([$sourceSlot, $targetSlot]));
        $inventory->shouldNotReceive('findOwnedSlotByShareItemIdForUpdate');

        $transaction = Mockery::mock(TransactionManager::class);
        $transaction->shouldReceive('run')->once()->andReturnUsing(fn (callable $callback) => $callback());

        $result = (new TransferUpgrade(
            $inventory,
            $transaction,
            new CanTransferUpgrade(new CanUpgradeItem),
            new UpgradeTransferService,
        ))->execute(new TransferUpgradeDTO($user, 101, 202, 501));

        self::assertFalse($result->ok);
        self::assertStringContainsString('одного типа и слота', $result->message);
    }

    public function test_failed_transfer_loses_source_upgrade_and_consumes_transgressor(): void
    {
        [, $source] = $this->equipmentSlot(101, 'Старый меч', ShareItemType::WEAPON, ShareItemSlot::HAND, 6);
        [, $target] = $this->equipmentSlot(202, 'Новый меч', ShareItemType::WEAPON, ShareItemSlot::HAND, 0);
        $source->shouldReceive('save')->once()->andReturnTrue();
        $target->shouldNotReceive('save');

        $transgressor = $this->transgressorSlot(ItemRarity::COMMON, 2);
        $transgressor->shouldReceive('save')->once()->andReturnTrue();

        $result = (new UpgradeTransferService)->transfer($source, $target, $transgressor, 100);

        self::assertFalse($result->success);
        self::assertSame(30, $result->chance);
        self::assertSame(0, $source->upgrade_lvl);
        self::assertSame(0, $target->upgrade_lvl);
        self::assertSame(1, $transgressor->count);
    }

    public function test_transfer_chance_depends_on_transgressor_rarity(): void
    {
        $service = new UpgradeTransferService;

        self::assertSame(30, $service->successChance(ItemRarity::COMMON));
        self::assertSame(45, $service->successChance(ItemRarity::UNCOMMON));
        self::assertSame(60, $service->successChance(ItemRarity::RARE));
        self::assertSame(75, $service->successChance(ItemRarity::EPIC));
        self::assertSame(90, $service->successChance(ItemRarity::LEGENDARY));
        self::assertSame(100, $service->successChance(ItemRarity::HEROIC));
    }

    /** @return array{Backpack, Item&MockInterface} */
    private function equipmentSlot(
        int $itemId,
        string $name,
        ShareItemType $type,
        ShareItemSlot $slot,
        int $level,
    ): array {
        $itemInfo = new ShareItem;
        $itemInfo->name = $name;
        $itemInfo->type = $type;
        $itemInfo->slot = $slot;

        $item = Mockery::mock(Item::class)->makePartial();
        $item->id = $itemId;
        $item->upgrade_lvl = $level;
        $item->upgrade_pity = 3;
        $item->upgrade_fail_streak = 2;
        $item->setRelation('itemInfo', $itemInfo);

        $backpack = new Backpack;
        $backpack->item_id = $itemId;
        $backpack->setRelation('item', $item);

        return [$backpack, $item];
    }

    /** @return Backpack&MockInterface */
    private function transgressorSlot(ItemRarity $rarity, int $count): Backpack
    {
        $itemInfo = new ShareItem;
        $itemInfo->id = 501;
        $itemInfo->name = UpgradeTransferService::REQUIRED_ITEM_NAME;
        $itemInfo->type = ShareItemType::MISC;
        $itemInfo->rarity = $rarity;

        $item = new Item;
        $item->share_item_id = 501;
        $item->setRelation('itemInfo', $itemInfo);

        $transgressor = Mockery::mock(Backpack::class)->makePartial();
        $transgressor->count = $count;
        $transgressor->setRelation('item', $item);

        return $transgressor;
    }
}
