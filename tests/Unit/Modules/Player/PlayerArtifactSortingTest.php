<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Player;

use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Player\Infrastructure\Persistence\Models\PlayerArtifact;
use App\Modules\Share\Domain\Enums\ItemRarity;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

final class PlayerArtifactSortingTest extends TestCase
{
    public function test_artifacts_are_displayed_from_lowest_to_highest_rarity(): void
    {
        $player = new Player;
        $artifacts = new Collection([
            $this->artifact(1, ItemRarity::HEROIC),
            $this->artifact(2, ItemRarity::EPIC),
            $this->artifact(3, ItemRarity::COMMON),
            $this->artifact(4, ItemRarity::LEGENDARY),
            $this->artifact(5, ItemRarity::UNCOMMON),
            $this->artifact(6, ItemRarity::RARE),
        ]);
        $player->setRelation('artifacts', $artifacts);

        self::assertSame([3, 5, 6, 2, 4, 1], $player->artifactsByRarity()->modelKeys());
        self::assertSame([1, 2, 3, 4, 5, 6], $player->artifacts->modelKeys());
        self::assertSame([0, 1, 2, 3, 4, 5], $player->artifactsByRarity()->keys()->all());
    }

    public function test_same_rarity_has_a_stable_order_and_missing_items_are_last(): void
    {
        $missing = (new PlayerArtifact)->forceFill(['id' => 1]);
        $missing->setRelation('item', null);
        $player = new Player;
        $player->setRelation('artifacts', new Collection([
            $missing,
            $this->artifact(8, ItemRarity::RARE),
            $this->artifact(3, ItemRarity::RARE),
            $this->artifact(2, ItemRarity::COMMON),
        ]));

        self::assertSame([2, 3, 8, 1], $player->artifactsByRarity()->modelKeys());
    }

    public function test_empty_artifacts_are_supported(): void
    {
        $player = new Player;
        $player->setRelation('artifacts', new Collection);

        self::assertTrue($player->artifactsByRarity()->isEmpty());
    }

    private function artifact(int $id, ItemRarity $rarity): PlayerArtifact
    {
        $info = (new ShareItem)->forceFill(['rarity' => $rarity]);
        $item = new Item;
        $item->setRelation('itemInfo', $info);
        $artifact = (new PlayerArtifact)->forceFill(['id' => $id]);
        $artifact->setRelation('item', $item);

        return $artifact;
    }
}
