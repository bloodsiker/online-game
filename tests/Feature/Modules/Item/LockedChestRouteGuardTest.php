<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\Item;

use App\Modules\Item\Application\Services\LockpickingService;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Mockery;
use Tests\TestCase;

class LockedChestRouteGuardTest extends TestCase
{
    public function test_all_legacy_chest_routes_redirect_locked_chest_to_lockpicking(): void
    {
        $chestId = 321;
        $lockpicking = Mockery::mock(LockpickingService::class);
        $lockpicking->shouldReceive('isLockedChest')->times(3)->with($chestId)->andReturnTrue();
        $this->app->instance(LockpickingService::class, $lockpicking);

        $user = new User;
        $user->id = 1;
        $this->actingAs($user);

        $expected = route('items.lockpick.show', ['id' => $chestId]);

        $this->post(route('items.open_chest', ['id' => $chestId]))->assertRedirect($expected);
        $this->get(route('items.view_chest', ['id' => $chestId]))->assertRedirect($expected);
        $this->post(route('items.pickup_chest', ['chest' => $chestId, 'id' => 999]))->assertRedirect($expected);
    }
}
