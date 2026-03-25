<?php

namespace App\Http\Controllers;

use App\Enums\ShareItemSlot;
use App\Enums\ShareItemType;
use App\Models\Backpack;
use App\Services\HotbarService;
use App\Models\Item\Item;
use App\Models\Item\ItemInChest;
use App\Models\Item\ItemOnLocation;
use App\Models\Share\ShareItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ItemController extends Controller
{
    public function __construct(
        private readonly HotbarService $hotbarService,
    ) {}

    public function pickUp(int $id)
    {
        $user = Auth::user();
        $location = $user->currentLocation;

        $itemPickUp = ItemOnLocation::with(['item'])->where(['location_id' => $location->id, 'item_id' => $id])->first();
        $item = Item::find($id);
        if ($itemPickUp instanceof ItemOnLocation && $item instanceof Item) {
            $itemPickUp->delete();

            $hasBackpack = Backpack::select('backpacks.*')->where('user_id', $user->id)->where(['items.share_item_id' => $item->share_item_id])
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->first();

            if ($hasBackpack instanceof Backpack && $item->itemInfo->type === ShareItemType::RESOURCE) {
                $hasBackpack->count += $itemPickUp->count;
                $hasBackpack->save();

                $item->delete();
            } else {
                $backpack = new Backpack();
                $backpack->user_id = $user->id;
                $backpack->item_id = $item->id;
                $backpack->count = $itemPickUp->count;
                $backpack->save();
            }

            $message = sprintf('Вы подняли предмет <b>"%s"</b>...', $item->itemInfo->name);
        } else {
            $message = sprintf('Кто-то уже поднял предмет <b>"%s"</b>...', $item->itemInfo->name);
        }

        $itemsOnLocation = ItemOnLocation::with(['item', 'item.itemInfo'])->where(['location_id' => $location->id])->get();

        return view('item.pickup_items', compact('location', 'message', 'itemsOnLocation'));
    }

    public function dropItem(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        if ($request->integer('c') !== 1) {
            return redirect()->route('backpack');
        }

        $user     = Auth::user();
        $item     = Item::find($id);
        $location = $user->currentLocation;

        if ($item instanceof Item) {
            $backpackItem = Backpack::where(['user_id' => $user->id, 'item_id' => $item->id])->first();

            if ($backpackItem instanceof Backpack) {
                $qty = max(1, min($request->integer('qty', $backpackItem->count), $backpackItem->count));

                if ($qty >= $backpackItem->count) {
                    $backpackItem->delete();
                } else {
                    $backpackItem->count -= $qty;
                    $backpackItem->save();
                }

                $location->itemsOnLocation()->attach($item->id, ['count' => $qty]);
            }
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
            ->where('last_online_at', '>', Carbon::now()->subMinutes(10))
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
            if ($user->location_id === $toUser->location_id) {
                $backpackItem = Backpack::where(['user_id' => $user->id, 'item_id' => $item->id])->first();

                if ($backpackItem instanceof Backpack) {
                    if ($backpackItem->isEquipped()) {
                        session()->flash('message', 'Не возможно передать, предмет надет на персонажа');
                        return redirect()->back();
                    }

                    $backpackItem->delete();

                    $backpack = new Backpack();
                    $backpack->item_id = $item->id;
                    $backpack->user_id = $toUser->id;
                    $backpack->save();

                    $isHandedItem = true;
                }
            } else {
                $isUserMoved = true;
                session()->flash('message', sprintf('Персонаж %s не находиться рядом возле вас', $toUser->name));
                return redirect()->back();
            }
        }

        $onlineOnLocation = User::with(['player'])
            ->where('location_id', $user->location_id)
            ->whereNot('id', $user->id)
            ->where('last_online_at', '>', Carbon::now()->subMinutes(10))
            ->orderByDesc('last_online_at')
            ->get();

        return view('item.hand_over', compact('item', 'isHandedItem', 'isUserMoved', 'toUser', 'onlineOnLocation'));
    }

    /**
     * Одеть вещь
     */
    public function putOn(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $itemInventory = Backpack::where('item_id', $id)->where('user_id', $user->id)->first();

        if (!$itemInventory || $itemInventory->equipped === 1) {
            return redirect()->back();
        }

        $playerEquip = $user->player->playerEquip;
        $typeItem    = $itemInventory->item->itemInfo->type; // ShareItemType enum
        $slot        = $itemInventory->item->itemInfo->slot; // string
        $itemId      = $itemInventory->item->id;

        if ($slot === ShareItemSlot::HAND) {
            if ($typeItem === ShareItemType::WEAPON && $playerEquip->hand_left && $playerEquip->hand_right) {
                session()->flash('message', 'Слот занят');
                return redirect()->back();
            }

            if ($typeItem === ShareItemType::SHIELD && $playerEquip->hand_right) {
                session()->flash('message', 'Слот занят');
                return redirect()->back();
            }

            if (!$playerEquip->hand_left && $typeItem === ShareItemType::WEAPON) {
                $playerEquip->hand_left = $itemId;
                $playerEquip->save();
                $itemInventory->equipped = 1;
                $itemInventory->save();
            } elseif (!$playerEquip->hand_right && $playerEquip->hand_left !== $itemId
                && in_array($typeItem, [ShareItemType::WEAPON, ShareItemType::SHIELD], true)) {
                $playerEquip->hand_right = $itemId;
                $playerEquip->save();
                $itemInventory->equipped = 1;
                $itemInventory->save();
            }

            return redirect()->back();
        }

        if (in_array($slot, ShareItemSlot::armorSlots(), true)) {
            $slotName = $slot->value;
            if ($playerEquip->$slotName) {
                session()->flash('message', 'Слот занят');
                return redirect()->back();
            }

            $playerEquip->$slotName = $itemId;
            $playerEquip->save();
            $itemInventory->equipped = 1;
            $itemInventory->save();

            return redirect()->back();
        }

        if ($slot === ShareItemSlot::BELT && $typeItem === ShareItemType::BELT) {
            if (!$playerEquip->belt_first) {
                $playerEquip->belt_first = $itemId;
            } elseif (!$playerEquip->belt_second) {
                $playerEquip->belt_second = $itemId;
            } else {
                session()->flash('message', 'Слоты для пояса заняты');
                return redirect()->back();
            }

            $playerEquip->save();
            $itemInventory->equipped = 1;
            $itemInventory->save();

            session()->flash('hotbar_refresh', true);
            return redirect()->back();
        }

        if ($slot === ShareItemSlot::BAG && $typeItem === ShareItemType::BAG) {
            if (!$playerEquip->bag_first) {
                $playerEquip->bag_first = $itemId;
            } elseif (!$playerEquip->bag_second) {
                $playerEquip->bag_second = $itemId;
            } else {
                session()->flash('message', 'Слоты для сумки заняты');
                return redirect()->back();
            }

            $playerEquip->save();
            $itemInventory->equipped = 1;
            $itemInventory->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    /**
     * Снять вещь
     */
    public function putOff(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        $itemInventory = Backpack::where('item_id', $id)->where('user_id', $user->id)->first();

        if (!$itemInventory || $itemInventory->equipped === 0) {
            return redirect()->back();
        }

        $playerEquip = $user->player->playerEquip;
        $slot        = $itemInventory->item->itemInfo->slot;
        $itemId      = $itemInventory->item->id;

        if ($slot === ShareItemSlot::HAND) {
            if ($playerEquip->hand_left === $itemId) {
                $playerEquip->hand_left = null;
                $playerEquip->save();
                $itemInventory->equipped = 0;
                $itemInventory->save();
            } elseif ($playerEquip->hand_right === $itemId) {
                $playerEquip->hand_right = null;
                $playerEquip->save();
                $itemInventory->equipped = 0;
                $itemInventory->save();
            }

            return redirect()->back();
        }

        if (in_array($slot, ShareItemSlot::armorSlots(), true)) {
            $slotName = $slot->value;
            if ($playerEquip->$slotName === $itemId) {
                $playerEquip->$slotName = null;
                $playerEquip->save();
                $itemInventory->equipped = 0;
                $itemInventory->save();
            }

            return redirect()->back();
        }

        if ($slot === ShareItemSlot::BELT) {
            if ($playerEquip->belt_first === $itemId) {
                $playerEquip->belt_first = null;
            } elseif ($playerEquip->belt_second === $itemId) {
                $playerEquip->belt_second = null;
            }
            $playerEquip->save();
            $itemInventory->equipped = 0;
            $itemInventory->save();

            // Удаляем слоты хотбара, которые вышли за новый лимит
            $this->hotbarService->trimExcessSlots($user->player);

            session()->flash('hotbar_refresh', true);
            return redirect()->back();
        }

        if ($slot === ShareItemSlot::BAG) {
            if ($playerEquip->bag_first === $itemId) {
                $playerEquip->bag_first = null;
            } elseif ($playerEquip->bag_second === $itemId) {
                $playerEquip->bag_second = null;
            }
            $playerEquip->save();
            $itemInventory->equipped = 0;
            $itemInventory->save();

            return redirect()->back();
        }

        return redirect()->back();
    }

    public function openChest(Request $request, int $id)
    {
        $user = Auth::user();
        $location = $user->currentLocation;

        $item = Item::find($id);

        if ($item->itemInfo->type === ShareItemType::CHEST) {
            foreach ($item->itemInfo->itemHasItems as $hasItem) {
                $randomChance = mt_rand(0, 100000) / 1000;  // деление на 1000 для преобразования в проценты с тремя десятичными
                if ($randomChance <= $hasItem->pivot->drop_chance) {
                    $itemInChest = new Item();
                    $itemInChest->share_item_id = $hasItem->id;
                    $itemInChest->save();

                    $count = mt_rand($hasItem->pivot->min_count, $hasItem->pivot->max_count);

                    $item->itemsInChest()->attach($itemInChest->id, ['count' => $count]);
                }
            }

            $item->is_open = 1;
            $item->save();
        }

        return redirect()->route('items.view_chest', ['id' => $item->id]);
    }

    public function viewChest(Request $request, int $id)
    {
        $chest = Item::with('itemsInChest')->find($id);

        if ($chest->itemInfo->type === ShareItemType::CHEST) {

            return view('item.chest_items', compact('chest'));
        }

        return redirect()->route('take_items');
    }

    public function pickUpInChest(int $chest, int $id)
    {
        $user = Auth::user();

        $item = Item::find($id);
        $chest = Item::find($chest);
        $message = '';

        if ($chest instanceof Item && $chest->itemsInChest()->count()) {
            $itemPickUp = ItemInChest::with(['item'])->where(['item_id' => $id])->first();
            if ($itemPickUp instanceof ItemInChest && $item instanceof Item) {
                $itemPickUp->delete();

                $hasBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $item->share_item_id])
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->first();

                if ($hasBackpack instanceof Backpack && $item->itemInfo->type === ShareItemType::RESOURCE) {
                    $hasBackpack->count += $itemPickUp->count;
                    $hasBackpack->save();

                    $item->delete();
                } else {
                    $backpack = new Backpack();
                    $backpack->user_id = $user->id;
                    $backpack->item_id = $item->id;
                    $backpack->count = $itemPickUp->count;
                    $backpack->save();
                }

                $message = sprintf('Вы подняли предмет <b>"%s"</b>...', $item->itemInfo->name);
            } else {
                $message = sprintf('Кто-то уже поднял предмет <b>"%s"</b>...', $item->itemInfo->name);
            }
        }

        if (!$chest->itemsInChest()->count()) {
            $chest->delete();
        }

        return view('item.chest_items', compact('chest', 'message'));
    }

    public function info(int $id)
    {
        $item = Item::find($id);

        return view('item.info', compact('item'));
    }
}
