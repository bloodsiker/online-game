<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Backpack;

use App\Modules\Backpack\Application\UseCases\GetBackpack;
use App\Modules\Backpack\Domain\DTO\BackpackDTO;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\BackpackService;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Location\Domain\Contracts\LocationReadRepository;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class GetBackpackTest extends TestCase
{
    public function test_tool_has_its_own_backpack_group(): void
    {
        $tool = $this->backpackItem(itemId: 102, shareItemId: 400);
        $data = new BackpackDTO(
            backpack: new Collection([$tool]),
            items: new Collection(['tool' => new Collection([$tool])]),
            countItems: 1,
            group: 'main',
        );

        $this->assertTrue($data->hasTool());
        $this->assertSame([$tool], $data->getTool()->all());
    }

    public function test_it_marks_only_keys_with_a_teleport_gate_from_current_location_as_usable(): void
    {
        $availableKey = $this->backpackItem(itemId: 100, shareItemId: 324);
        $unavailableKey = $this->backpackItem(itemId: 101, shareItemId: 326, isDroppable: false);
        $data = new BackpackDTO(
            backpack: new Collection([$availableKey, $unavailableKey]),
            items: new Collection,
            countItems: 2,
            group: 'key',
        );

        $backpackService = Mockery::mock(BackpackService::class);
        $backpackService->shouldReceive('getBackpackData')->once()->andReturn($data);

        $collector = Mockery::mock(ItemTooltipCollector::class);
        $collector->shouldReceive('collectFrom')->once()->andReturnSelf();
        $collector->shouldReceive('renderScript')->once()->andReturn('');

        $locationReadRepository = Mockery::mock(LocationReadRepository::class);
        $locationReadRepository->shouldReceive('getTeleportUseShareItemIds')
            ->once()
            ->with(10)
            ->andReturn([324]);

        $player = new Player;
        $player->setRelation('playerEquip', null);

        $user = new User;
        $user->location_id = 10;
        $user->setRelation('player', $player);

        $result = (new GetBackpack($backpackService, $collector, $locationReadRepository))
            ->execute($user, ['group' => 'key']);

        $this->assertSame([100], $result['teleportUseKeyItemIds']);
        $this->assertSame([100], $result['droppableItemIds']);
    }

    private function backpackItem(int $itemId, int $shareItemId, bool $isDroppable = true): Backpack
    {
        $shareItem = new ShareItem;
        $shareItem->id = $shareItemId;
        $shareItem->is_droppable = $isDroppable;

        $item = new Item;
        $item->id = $itemId;
        $item->share_item_id = $shareItemId;
        $item->setRelation('itemInfo', $shareItem);

        $backpack = new Backpack;
        $backpack->item_id = $itemId;
        $backpack->setRelation('item', $item);

        return $backpack;
    }
}
