<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Structure\Blacksmith;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Item\Infrastructure\Persistence\Models\Item;
use App\Modules\Share\Domain\Enums\RecipeUnlockType;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareItem;
use App\Modules\Share\Infrastructure\Persistence\Models\ShareRecipe;
use App\Modules\Structure\Blacksmith\Application\DTOs\CraftItemDTO;
use App\Modules\Structure\Blacksmith\Application\UseCases\CraftItem;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithInventoryRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\BlacksmithReadRepository;
use App\Modules\Structure\Blacksmith\Domain\Contracts\TransactionManager;
use App\Modules\Structure\Blacksmith\Domain\Policies\CanCraftRecipe;
use App\Modules\Structure\Blacksmith\Domain\Services\CraftService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Mockery;
use Tests\TestCase;

class CraftItemTest extends TestCase
{
    public function test_learnable_recipe_cannot_be_consumed_by_blacksmith_craft(): void
    {
        $user = new User;
        $user->id = 7;

        $recipe = new ShareRecipe;
        $recipe->unlock_type = RecipeUnlockType::LEARNABLE;

        $shareItem = new ShareItem;
        $shareItem->setRelation('recipe', $recipe);

        $item = new Item;
        $item->setRelation('itemInfo', $shareItem);

        $recipeSlot = new Backpack;
        $recipeSlot->setRelation('item', $item);

        $inventoryRepository = Mockery::mock(BlacksmithInventoryRepository::class);
        $inventoryRepository->shouldReceive('findRecipeSlot')->once()->with($user, 55)->andReturn($recipeSlot);

        $readRepository = Mockery::mock(BlacksmithReadRepository::class);
        $readRepository->shouldNotReceive('getResourceCounts');

        $transactionManager = Mockery::mock(TransactionManager::class);
        $transactionManager->shouldNotReceive('run');

        $craftService = new CraftService(new CanCraftRecipe);

        $result = (new CraftItem(
            $inventoryRepository,
            $readRepository,
            $transactionManager,
            $craftService,
        ))->execute(new CraftItemDTO($user, 55));

        self::assertFalse($result->ok);
        self::assertSame(
            'Изучаемый рецепт нужно сначала изучить, а затем использовать в мастерской.',
            $result->message,
        );
    }
}
