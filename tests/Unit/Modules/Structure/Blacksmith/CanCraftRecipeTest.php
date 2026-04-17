<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Models\Share\ShareItem;
use App\Models\Share\ShareRecipe;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanCraftRecipe;
use PHPUnit\Framework\TestCase;

class CanCraftRecipeTest extends TestCase
{
    public function test_it_returns_true_when_all_resources_are_available(): void
    {
        $policy = new CanCraftRecipe;
        $recipe = new ShareRecipe;

        $wood = new ShareItem(['id' => 1]);
        $wood->id = 1;
        $wood->setRelation('pivot', (object) ['count' => 2]);

        $iron = new ShareItem(['id' => 2]);
        $iron->id = 2;
        $iron->setRelation('pivot', (object) ['count' => 1]);

        $recipe->setRelation('items', collect([$wood, $iron]));

        self::assertTrue($policy->check($recipe, [
            ['id' => 1, 'count' => 2],
            ['id' => 2, 'count' => 3],
        ]));
    }

    public function test_it_returns_false_when_any_resource_is_missing(): void
    {
        $policy = new CanCraftRecipe;
        $recipe = new ShareRecipe;

        $wood = new ShareItem(['id' => 1]);
        $wood->id = 1;
        $wood->setRelation('pivot', (object) ['count' => 5]);

        $recipe->setRelation('items', collect([$wood]));

        self::assertFalse($policy->check($recipe, [
            ['id' => 1, 'count' => 3],
        ]));
    }
}
