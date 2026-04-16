<?php

declare(strict_types=1);

namespace App\Modules\Structure\Warehouse\Presentation\Http;

use App\Enums\ShareItemType;
use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Models\Structure;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WarehouseController extends Controller
{
    public function index(Request $request, int $id): mixed
    {
        $user      = Auth::user();
        $warehouse = Structure::findOrFail($id);

        $countInWarehouse = Warehouse::where('user_id', $user->id)
            ->where('structure_id', $warehouse->id)
            ->count();

        if ($request->isMethod('POST')) {
            $redirect = $this->handlePut($request, $user, $warehouse, $countInWarehouse);
            if ($redirect) {
                return $redirect;
            }
        }

        $putItems = Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('equipped', 0)
            ->orderBy('share_items.type', 'desc')
            ->get();

        return view('warehouse::put', compact('warehouse', 'user', 'putItems', 'countInWarehouse'));
    }

    public function takeItem(Request $request, int $id): mixed
    {
        $user      = Auth::user();
        $warehouse = Structure::findOrFail($id);

        if ($request->isMethod('POST')) {
            $redirect = $this->handleTake($request, $user, $warehouse);
            if ($redirect) {
                return $redirect;
            }
        }

        $itemsToTake = Warehouse::with(['item', 'item.itemInfo'])
            ->join('items', 'warehouses.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('user_id', $user->id)
            ->where('structure_id', $warehouse->id)
            ->orderBy('share_items.type', 'desc')
            ->select('warehouses.*')
            ->get();

        $countInWarehouse = Warehouse::where('user_id', $user->id)
            ->where('structure_id', $warehouse->id)
            ->count();

        return view('warehouse::take', compact('warehouse', 'user', 'itemsToTake', 'countInWarehouse'));
    }

    private function handlePut(Request $request, mixed $user, Structure $warehouse, int $countInWarehouse): mixed
    {
        $checkedItems = $request->input('item', []);
        $putItems     = array_filter($checkedItems, fn ($p) => isset($p['selected']) && $p['selected'] == 1);

        if (!$putItems) {
            session()->flash('message', 'Не выбраны предметы для хранения.');
            return redirect()->back();
        }

        $items = Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->whereIn('backpacks.item_id', array_keys($putItems))
            ->where('equipped', 0)
            ->get();

        $toInsert        = [];
        $toStack         = [];
        $toSubtract      = [];
        $toDeleteItemIds = [];
        $isLimit         = false;
        $totalCost       = 0;
        $newSlots        = 0;

        foreach ($items as $item) {
            if ($countInWarehouse + $newSlots >= $user->warehouse_count) {
                $isLimit = true;
                break;
            }

            $wantCount   = (int) ($putItems[$item->item_id]['count'] ?? $item->count);
            $actualCount = min($wantCount, $item->count);
            $totalCost  += 10;

            $stackTarget = null;
            if ($item->item->itemInfo->type === ShareItemType::RESOURCE || $item->item->itemInfo->type === ShareItemType::POTION) {
                $stackTarget = Warehouse::select('warehouses.*')
                    ->join('items', 'warehouses.item_id', '=', 'items.id')
                    ->where('items.share_item_id', $item->item->share_item_id)
                    ->where('warehouses.user_id', $user->id)
                    ->where('warehouses.structure_id', $warehouse->id)
                    ->first();
            }

            if ($stackTarget) {
                $toStack[] = ['warehouse' => $stackTarget, 'add' => $actualCount];
            } else {
                $toInsert[] = [
                    'user_id'      => $user->id,
                    'structure_id' => $warehouse->id,
                    'item_id'      => $item->item_id,
                    'count'        => $actualCount,
                ];
                $newSlots++;
            }

            if ($item->count <= $actualCount) {
                $toDeleteItemIds[] = $item->item_id;
            } else {
                $toSubtract[] = ['item' => $item, 'count' => $actualCount];
            }
        }

        if ($user->money < $totalCost) {
            session()->flash('message', sprintf(
                'У вас не достаточно %s монет для хранения вещей',
                number_format($totalCost, 0, ',', ' ')
            ));
            return redirect()->back();
        }

        foreach ($toStack as $stack) {
            $stack['warehouse']->count += $stack['add'];
            $stack['warehouse']->save();
        }

        if ($toInsert) {
            Warehouse::insert($toInsert);
        }

        foreach ($toSubtract as $sub) {
            $sub['item']->count -= $sub['count'];
            $sub['item']->save();
        }

        if ($toDeleteItemIds) {
            Backpack::whereIn('item_id', $toDeleteItemIds)->where('user_id', $user->id)->delete();
        }

        $user->money -= $totalCost;
        $user->save();

        if ($isLimit) {
            session()->flash('message', 'У вас не достаточно свободных мест в хранилище.');
        }

        return null;
    }

    private function handleTake(Request $request, mixed $user, Structure $warehouse): mixed
    {
        $checkedItems = $request->input('item', []);
        $takeItems    = array_filter($checkedItems, fn ($p) => isset($p['selected']) && $p['selected'] == 1);

        if (!$takeItems) {
            session()->flash('message', 'Не выбраны предметы которые хотите забрать.');
            return redirect()->back();
        }

        $items = Warehouse::with(['item', 'item.itemInfo'])
            ->where('structure_id', $warehouse->id)
            ->where('user_id', $user->id)
            ->whereIn('item_id', array_keys($takeItems))
            ->get();

        foreach ($items as $wItem) {
            $wantCount   = (int) ($takeItems[$wItem->item_id]['count'] ?? $wItem->count);
            $actualCount = min($wantCount, $wItem->count);

            if ($wItem->count <= $actualCount) {
                $wItem->delete();
            } else {
                $wItem->count -= $actualCount;
                $wItem->save();
            }

            $existing = null;
            if ($wItem->item->itemInfo->type === ShareItemType::RESOURCE || $wItem->item->itemInfo->type === ShareItemType::POTION) {
                $existing = Backpack::select('backpacks.*')
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->where('items.share_item_id', $wItem->item->share_item_id)
                    ->where('backpacks.user_id', $user->id)
                    ->first();
            }

            if ($existing) {
                $existing->count += $actualCount;
                $existing->save();
            } else {
                Backpack::create([
                    'user_id' => $user->id,
                    'item_id' => $wItem->item_id,
                    'count'   => $actualCount,
                ]);
            }
        }

        return null;
    }
}