<?php

namespace App\Http\Controllers;

use App\Models\Backpack;
use App\Models\ShareItem;
use App\Models\Structure;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function index(Request $request, $id)
    {
        $user = Auth::user();
        $warehouse = Structure::find($id);

        if (!$warehouse) {
            abort(404);
        }

        $countInWarehouse = Warehouse::query()
            ->where('user_id', $user->id)
            ->where('structure_id', $warehouse->id)
            ->count();

        if ($request->isMethod('POST')) {
            $checkedItems = $request->input('item');
            $putItems = array_filter($checkedItems, function($product) {
                return isset($product['selected']) && $product['selected'] == 1;
            });

            if (!$putItems || count($putItems) === 0) {
                session()->flash('message', 'Не выбраны предметы для хранения]');
                return redirect()->back();
            }

            $items = Backpack::select('backpacks.*')
                ->with(['item', 'item.itemInfo'])
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                ->where('backpacks.user_id', $user->id)
                ->whereIn('item_id', array_keys($putItems))
                ->where('equipped', 0)
                ->get();

            $data = [];
            $successPutItems = [];
            $isLimit = false;
            $putTotalPrice = 0;
            $countInWarehouseAndPut = $countInWarehouse;
            foreach ($items as $item) {
                if ($countInWarehouseAndPut >= $user->warehouse_count) {
                    $isLimit = true;
                    break;
                }

                $putTotalPrice += 10;

                $countItem = $putItems[$item->item_id];
                if ($countItem['count'] >= $item->count) {
                    $putCount = $item->count;
                    $successPutItems[] = $item->item_id;
                } else {
                    $putCount = $countItem['count'];
                }

                $data[] = [
                    'user_id' => $user->id,
                    'structure_id' => $warehouse->id,
                    'item_id' => $item->item_id,
                    'count' => $putCount,
                    'share_item_id' => $item->item->share_item_id,
                    'type' => $item->item->itemInfo->type,
                    'item' => $item,
                ];

                $countInWarehouseAndPut++;
            }

            $countInWarehouse = $countInWarehouseAndPut;

            if ($user->money < $putTotalPrice) {
                session()->flash('message', sprintf('У вас не достаточно %s монет для хранения вещей', number_format($putTotalPrice, 0, ',', ' ')));
                return redirect()->back();
            }

            foreach ($data as $key => &$itemPutToWarehouse) {
                $hasInWarehouse = Warehouse::select('warehouses.*')->where(['items.share_item_id' => $itemPutToWarehouse['share_item_id']])
                    ->join('items', 'warehouses.item_id', '=', 'items.id')
                    ->first();

                $itemInBackpack = $itemPutToWarehouse['item'];

                if ($hasInWarehouse instanceof Warehouse && $itemPutToWarehouse['type'] === ShareItem::TYPE_RESOURCE) {
                    $hasInWarehouse->count += $itemPutToWarehouse['count'];
                    $hasInWarehouse->save();
                    unset($data[$key]);
                }

                if ($itemInBackpack->count > $itemPutToWarehouse['count']) {
                    $itemInBackpack->count -= $itemPutToWarehouse['count'];
                    $itemInBackpack->save();
                }

                unset($itemPutToWarehouse['type'], $itemPutToWarehouse['share_item_id'], $itemPutToWarehouse['item']);
            }

            $user->money -= $putTotalPrice;
            $user->save();

            if (count($data)) {
                Warehouse::insert($data);
            }

            if (count($successPutItems)) {
                Backpack::query()->whereIn('item_id', $successPutItems)->delete();
            }

            if ($isLimit) {
                session()->flash('message', 'У вас не достаточно свободных мест в хранилище');
            }
        }

        $putItems = Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('equipped', 0)
            ->orderBy('share_items.type', 'desc')->get();

        return view('warehouse.put', compact('warehouse', 'user', 'putItems', 'countInWarehouse'));
    }

    public function takeItem(Request $request, $id)
    {
        $user = Auth::user();
        $warehouse = Structure::find($id);

        if (!$warehouse) {
            abort(404);
        }

        if ($request->isMethod('POST')) {
            $checkedItems = $request->input('item');
            $takeItems = array_filter($checkedItems, function($product) {
                return isset($product['selected']) && $product['selected'] == 1;
            });

            if (!$takeItems || count($takeItems) === 0) {
                session()->flash('message', 'Не выбраны предметы которые хотите забрать');
                return redirect()->back();
            }

            $items = Warehouse::query()
                ->where('structure_id', $warehouse->id)
                ->where('user_id', $user->id)
                ->whereIn('item_id', array_keys($takeItems))
                ->get();

            foreach ($items as $takeItem) {
                $countTake = $takeItems[$takeItem->item_id]['count'];
                if ($countTake >= $takeItem->count) {
                    $countTake = $takeItem->count;
                    Warehouse::where(['item_id' => $takeItem->item_id, 'user_id' => $user->id, 'structure_id' => $warehouse->id])->delete();
                } else {
                    Warehouse::where(['item_id' => $takeItem->item_id, 'user_id' => $user->id, 'structure_id' => $warehouse->id])->update(['count' => $takeItem->count - $countTake]);
                }

                $hasBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $takeItem->item->share_item_id])
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->first();

                if ($hasBackpack instanceof Backpack && $takeItem->item->itemInfo->type === ShareItem::TYPE_RESOURCE) {
                    $hasBackpack->count += $countTake;
                    $hasBackpack->save();
                } else {
                    $backpack = new Backpack();
                    $backpack->user_id = $user->id;
                    $backpack->item_id = $takeItem->item_id;
                    $backpack->count = $countTake;
                    $backpack->save();
                }
            }
        }


        $itemsToTake = Warehouse::query()
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('user_id', $user->id)
            ->where('structure_id', $warehouse->id)
            ->orderBy('share_items.type', 'desc')
            ->get();

        $countInWarehouse = Warehouse::query()
            ->where('user_id', $user->id)
            ->where('structure_id', $warehouse->id)
            ->count();

        return view('warehouse.take', compact('warehouse', 'user', 'itemsToTake', 'countInWarehouse'));
    }
}
