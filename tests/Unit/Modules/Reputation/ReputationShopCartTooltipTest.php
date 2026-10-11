<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\Reputation;

use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Player\Infrastructure\Persistence\Models\Player;
use App\Modules\Reputation\Application\DTOs\ReputationShopCartDTO;
use App\Modules\Reputation\Application\Services\ReputationService;
use App\Modules\Reputation\Application\Services\ReputationShopCartService;
use App\Modules\Reputation\Application\UseCases\GetReputationShopPage;
use App\Modules\Reputation\Domain\Contracts\ReputationReadRepository;
use App\Modules\Reputation\Infrastructure\Persistence\Models\PlayerReputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\Reputation;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopCart;
use App\Modules\Reputation\Infrastructure\Persistence\Models\ReputationShopItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use ReflectionProperty;
use Tests\TestCase;

class ReputationShopCartTooltipTest extends TestCase
{
    public function test_tooltips_include_cart_items_outside_the_selected_category(): void
    {
        $user = (new User)->forceFill(['id' => 1]);
        $user->setRelation('player', new Player);
        $reputation = (new Reputation)->forceFill(['id' => 2]);
        $reputation->setRelation('npc', null);
        $reputation->setRelation('categories', new Collection);
        $visible = (new ReputationShopItem)->forceFill(['id' => 10]);
        $otherCategory = (new ReputationShopItem)->forceFill(['id' => 20]);
        $cartItem = new ReputationShopCart;
        $cartItem->setRelation('shopItem', $otherCategory);
        $cart = new ReputationShopCartDTO(new Collection([$cartItem]), 0, 0);

        $repository = Mockery::mock(ReputationReadRepository::class);
        $repository->shouldReceive('findReputationForShopOrFail')->once()->with(2)->andReturn($reputation);
        $repository->shouldReceive('getShopItemsByCategory')->once()->with(2, 1)->andReturn(new Collection([$visible]));
        $cartService = Mockery::mock(ReputationShopCartService::class);
        $cartService->shouldReceive('getCart')->once()->with($user, 2)->andReturn($cart);
        $reputationService = Mockery::mock(ReputationService::class);
        $reputationService->shouldReceive('getOrCreate')->once()->with($user->player, $reputation)->andReturn(new PlayerReputation);
        $collector = Mockery::mock(ItemTooltipCollector::class);
        $collectedIds = [];
        $collector->shouldReceive('collectFrom')->twice()->andReturnUsing(function ($strategy) use (&$collectedIds, $collector) {
            $items = (new ReflectionProperty($strategy, 'items'))->getValue($strategy);
            foreach ($items as $item) {
                $collectedIds[] = $item->id;
            }

            return $collector;
        });
        $collector->shouldReceive('renderScript')->once()->andReturn('<script>tooltips</script>');

        $page = (new GetReputationShopPage($repository, $reputationService, $cartService, $collector))->execute($user, 2, 1);

        $this->assertSame([10, 20], $collectedIds);
        $this->assertSame($cart, $page->cart);
        $this->assertSame('<script>tooltips</script>', $page->itemTooltipScript);
    }
}
