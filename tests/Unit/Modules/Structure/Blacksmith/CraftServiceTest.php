<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanCraftRecipe;
use App\Modules\Structure\Blacksmith\Domain\Services\CraftService;
use PHPUnit\Framework\TestCase;

class CraftServiceTest extends TestCase
{
    public function test_it_returns_not_enough_resources_result(): void
    {
        $service = new CraftService(new CanCraftRecipe);
        $recipe = new ShareRecipe;
        $recipe->percent = 100;

        $ingredient = new ShareItem(['id' => 1]);
        $ingredient->id = 1;
        $ingredient->setRelation('pivot', (object) ['count' => 2]);
        $recipe->setRelation('items', collect([$ingredient]));

        $result = $service->craft($recipe, [['id' => 1, 'count' => 1]]);

        self::assertFalse($result->success);
        self::assertFalse($result->resourcesConsumed);
        self::assertSame('Не достаточно ресурсов для крафта', $result->message);
    }

    public function test_it_returns_success_when_chance_is_one_hundred_percent(): void
    {
        $service = new CraftService(new CanCraftRecipe);
        $recipe = new ShareRecipe;
        $recipe->percent = 100;

        $ingredient = new ShareItem(['id' => 1]);
        $ingredient->id = 1;
        $ingredient->setRelation('pivot', (object) ['count' => 1]);
        $recipe->setRelation('items', collect([$ingredient]));

        $craftedItem = new ShareItem(['id' => 77, 'name' => 'Меч']);
        $craftedItem->id = 77;
        $recipe->setRelation('kraftItem', $craftedItem);

        $result = $service->craft($recipe, [['id' => 1, 'count' => 3]]);

        self::assertTrue($result->success);
        self::assertSame(77, $result->craftedShareItemId);
        self::assertSame('Успешний крафт. Получено Меч', $result->message);
    }

    public function test_it_returns_failure_when_chance_is_zero_percent(): void
    {
        $service = new CraftService(new CanCraftRecipe);
        $recipe = new ShareRecipe;
        $recipe->percent = 0;

        $ingredient = new ShareItem(['id' => 1]);
        $ingredient->id = 1;
        $ingredient->setRelation('pivot', (object) ['count' => 1]);
        $recipe->setRelation('items', collect([$ingredient]));

        $craftedItem = new ShareItem(['id' => 77, 'name' => 'Меч']);
        $craftedItem->id = 77;
        $recipe->setRelation('kraftItem', $craftedItem);

        $result = $service->craft($recipe, [['id' => 1, 'count' => 3]]);

        self::assertFalse($result->success);
        self::assertTrue($result->resourcesConsumed);
        self::assertSame('Не удачный крафт', $result->message);
    }
}
