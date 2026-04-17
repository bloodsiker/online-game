<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item\Item;
use App\Models\Item\ItemOnLocation;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use App\Services\ItemService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemService $itemService,
    ) {}

    public function pickUp(int $id)
    {
        $user = Auth::user();
        $location = $user->currentLocation;

        $message = $this->itemService->pickUpFromLocation($user, $id);

        $itemsOnLocation = ItemOnLocation::with(['item', 'item.itemInfo'])
            ->where('location_id', $location->id)
            ->get();

        return view('item.pickup_items', compact('location', 'message', 'itemsOnLocation'));
    }

    public function dropItem(Request $request, int $id): RedirectResponse
    {
        if ($request->integer('c') !== 1) {
            return redirect()->route('backpack');
        }

        $user = Auth::user();
        $item = Item::find($id);

        if ($item instanceof Item) {
            $qty = $request->integer('qty', 0) ?: null;
            $this->itemService->drop($user, $item->id, $qty ?? PHP_INT_MAX);
        }

        return redirect()->route('backpack');
    }

    public function handOver(int $id)
    {
        $user = Auth::user();
        $item = Item::find($id);
        $isHandedItem = false;
        $isUserMoved = false;

        $onlineOnLocation = User::with(['player'])
            ->where('location_id', $user->location_id)
            ->whereNot('id', $user->id)
            ->where('last_online_at', '>', now()->subMinutes(10))
            ->orderByDesc('last_online_at')
            ->get();

        return view('item.hand_over', compact('item', 'isHandedItem', 'isUserMoved', 'onlineOnLocation'));
    }

    public function handOverToUser(Request $request, int $id)
    {
        $user = Auth::user();
        $item = Item::find($id);
        $isHandedItem = false;
        $toUser = null;
        $isUserMoved = false;

        if ($item instanceof Item && $request->has('uid')) {
            $toUser = User::find($request->get('uid'));
            $error = $this->itemService->handOver($user, $item, $toUser);

            if ($error) {
                $isUserMoved = str_contains($error, 'рядом');
                session()->flash('message', $error);

                return redirect()->back();
            }

            $isHandedItem = true;
        }

        $onlineOnLocation = User::with(['player'])
            ->where('location_id', $user->location_id)
            ->whereNot('id', $user->id)
            ->where('last_online_at', '>', now()->subMinutes(10))
            ->orderByDesc('last_online_at')
            ->get();

        return view('item.hand_over', compact('item', 'isHandedItem', 'isUserMoved', 'toUser', 'onlineOnLocation'));
    }

    public function putOn(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $error = $this->itemService->equip($user, $id);

        if ($error) {
            session()->flash('message', $error);
        }

        if ($this->needsHotbarRefresh($user, $id)) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function putOff(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $this->itemService->unequip($user, $id);

        if ($this->needsHotbarRefresh($user, $id)) {
            session()->flash('hotbar_refresh', true);
        }

        return redirect()->back();
    }

    public function openChest(Request $request, int $id): RedirectResponse
    {
        $item = Item::find($id);
        $this->itemService->openChest($item);

        return redirect()->route('items.view_chest', ['id' => $item->id]);
    }

    public function viewChest(Request $request, int $id)
    {
        $chest = Item::with('itemsInChest')->find($id);

        return view('item.chest_items', compact('chest'));
    }

    public function pickUpInChest(int $chest, int $id)
    {
        $user = Auth::user();
        $chestItem = Item::with('itemsInChest')->find($chest);

        $message = $this->itemService->pickUpFromChest($user, $chestItem, $id);

        return view('item.chest_items', ['chest' => $chestItem, 'message' => $message]);
    }

    public function info(int $id)
    {
        $item = Item::find($id);

        return view('item.info', compact('item'));
    }

    private function needsHotbarRefresh(User $user, int $itemId): bool
    {
        $item = Item::find($itemId);

        return $item && $item->itemInfo?->slot?->value === 'belt';
    }
}
