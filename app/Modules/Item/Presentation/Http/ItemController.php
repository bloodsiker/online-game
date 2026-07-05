<?php

declare(strict_types=1);

namespace App\Modules\Item\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Item\Application\UseCases\DropItem;
use App\Modules\Item\Application\UseCases\EquipItem;
use App\Modules\Item\Application\UseCases\GetChestPage;
use App\Modules\Item\Application\UseCases\GetHandOverPage;
use App\Modules\Item\Application\UseCases\GetItemInfoPage;
use App\Modules\Item\Application\UseCases\GetPickupItemsPage;
use App\Modules\Item\Application\UseCases\HandOverToUser;
use App\Modules\Item\Application\UseCases\OpenChest;
use App\Modules\Item\Application\UseCases\PickUpInChest;
use App\Modules\Item\Application\UseCases\UnequipItem;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct(
        private readonly GetPickupItemsPage $getPickupItemsPage,
        private readonly DropItem $dropItem,
        private readonly GetHandOverPage $getHandOverPage,
        private readonly HandOverToUser $handOverToUser,
        private readonly EquipItem $equipItem,
        private readonly UnequipItem $unequipItem,
        private readonly OpenChest $openChest,
        private readonly GetChestPage $getChestPage,
        private readonly PickUpInChest $pickUpInChest,
        private readonly GetItemInfoPage $getItemInfoPage,
    ) {}

    public function pickUp(int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::pickup_items', [
            'page' => $this->getPickupItemsPage->execute($user, $id),
        ]);
    }

    public function dropItem(Request $request, int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->dropItem->execute(
            $user,
            $id,
            $request->integer('c'),
            $request->integer('qty', 0),
        );

        return redirect()->route('backpack');
    }

    public function handOver(int $id): mixed
    {
        return view('item::hand_over', [
            'page' => $this->getHandOverPage->execute($id),
        ]);
    }

    public function handOverToUser(Request $request, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::hand_over', [
            'page' => $this->handOverToUser->execute($user, $id, $request->integer('uid') ?: null),
        ]);
    }

    public function putOn(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->equipItem->execute($user, $id);

        if ($result->message !== '') {
            session()->flash('message', $result->message);
        } else {
            session()->flash('equip_changed', true);
        }
        if ($result->hotbarRefresh) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function putOff(int $id): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $result = $this->unequipItem->execute($user, $id);

        session()->flash('equip_changed', true);
        if ($result->hotbarRefresh) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function openChest(int $id): RedirectResponse
    {
        $itemId = $this->openChest->execute($id);
        abort_if($itemId === null, 404);

        return redirect()->route('items.view_chest', ['id' => $itemId]);
    }

    public function viewChest(int $id): mixed
    {
        return view('item::chest_items', [
            'page' => $this->getChestPage->execute($id),
        ]);
    }

    public function pickUpInChest(int $chest, int $id): mixed
    {
        /** @var User $user */
        $user = Auth::user();

        return view('item::chest_items', [
            'page' => $this->pickUpInChest->execute($user, $chest, $id),
        ]);
    }

    public function info(int $id): mixed
    {
        return view('item::info', [
            'page' => $this->getItemInfoPage->execute($id),
        ]);
    }
}
