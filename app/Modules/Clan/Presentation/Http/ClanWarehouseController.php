<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Enums\ClanWarehouseAction;
use App\Modules\Clan\Domain\Models\ClanWarehouse;
use App\Modules\Clan\Domain\Models\ClanWarehouseLog;
use App\Enums\ShareItemType;
use App\Models\Backpack;
use App\Models\Structure;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\BackpackItemTooltipStrategy;
use App\Services\ItemTooltip\Strategy\ClanWarehouseItemTooltipStrategy;
use App\Services\ItemTooltip\Strategy\WarehouseLogItemTooltipStrategy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClanWarehouseController extends Controller
{
    public function __construct(
        protected readonly ItemTooltipCollector $collector,
    ) {}

    public function put(Request $request, int $id): mixed
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clanWarehouse = Structure::findOrFail($id);

        if (! $clanWarehouse->isClanWarehouse()) {
            abort(404);
        }

        $membership = $user->clanMembership;

        if ($membership === null) {
            session()->flash('message', 'Вы не состоите в клане.');

            return redirect()->route('clan');
        }

        if (! $membership->role->hasPermission(ClanPermission::DEPOSIT)) {
            session()->flash('message', 'У вас нет прав класть предметы в хранилище клана.');

            return redirect()->route('location');
        }

        $clan = $membership->clan;
        $countInWarehouse = ClanWarehouse::where('clan_id', $clan->id)
            ->where('structure_id', $clanWarehouse->id)
            ->count();

        if ($request->isMethod('POST')) {
            $checkedItems = $request->input('item', []);
            $putItems = array_filter($checkedItems, fn ($p) => isset($p['selected']) && $p['selected'] == 1);

            if (! $putItems) {
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

            $countLeft = $clan->warehouse_capacity - $countInWarehouse;
            $logData = [];

            foreach ($items as $item) {
                if ($countLeft <= 0) {
                    session()->flash('message', 'В хранилище клана нет свободных мест.');
                    break;
                }

                $putCount = $putItems[$item->item_id];
                $wantCount = (int) ($putCount['count'] ?? $item->count);
                $actualCount = min($wantCount, $item->count);

                $existing = null;
                if ($item->item->itemInfo->type === ShareItemType::RESOURCE || $item->item->itemInfo->type === ShareItemType::POTION) {
                    $existing = ClanWarehouse::where('clan_id', $clan->id)
                        ->where('structure_id', $clanWarehouse->id)
                        ->where('item_id', $item->item_id)
                        ->first();
                }

                if ($existing) {
                    $existing->count += $actualCount;
                    $existing->save();
                } else {
                    ClanWarehouse::create([
                        'clan_id' => $clan->id,
                        'structure_id' => $clanWarehouse->id,
                        'depositor_user_id' => $user->id,
                        'item_id' => $item->item_id,
                        'count' => $actualCount,
                    ]);
                    $countLeft--;
                    $countInWarehouse++;
                }

                if ($item->count <= $actualCount) {
                    $item->delete();
                } else {
                    $item->count -= $actualCount;
                    $item->save();
                }

                $logData[] = [
                    'clan_id' => $clan->id,
                    'user_id' => $user->id,
                    'structure_id' => $clanWarehouse->id,
                    'item_id' => $item->item_id,
                    'action' => ClanWarehouseAction::PUT,
                    'count' => $actualCount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($logData) {
                ClanWarehouseLog::insert($logData);
            }
        }

        $backpackItems = Backpack::select('backpacks.*')
            ->with(['item', 'item.itemInfo'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('equipped', 0)
            ->orderBy('share_items.type', 'desc')
            ->get();

        $this->collector->collectFrom(new BackpackItemTooltipStrategy($backpackItems));

        $itemTooltipScript = $this->collector->renderScript();

        return view('clan::warehouse.put', compact('clanWarehouse', 'clan', 'membership', 'backpackItems', 'countInWarehouse', 'itemTooltipScript'));
    }

    public function take(Request $request, int $id): mixed
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clanWarehouse = Structure::findOrFail($id);

        if (! $clanWarehouse->isClanWarehouse()) {
            abort(404);
        }

        $membership = $user->clanMembership;

        if ($membership === null) {
            session()->flash('message', 'Вы не состоите в клане.');

            return redirect()->route('clan');
        }

        if (! $membership->role->hasPermission(ClanPermission::WITHDRAW_ITEMS)) {
            session()->flash('message', 'У вас нет прав забирать предметы из хранилища клана.');

            return redirect()->route('location');
        }

        $clan = $membership->clan;
        $countInWarehouse = ClanWarehouse::where('clan_id', $clan->id)
            ->where('structure_id', $clanWarehouse->id)
            ->count();

        if ($request->isMethod('POST')) {
            $checkedItems = $request->input('item', []);
            $takeItems = array_filter($checkedItems, fn ($p) => isset($p['selected']) && $p['selected'] == 1);

            if (! $takeItems) {
                session()->flash('message', 'Не выбраны предметы для получения.');

                return redirect()->back();
            }

            $items = ClanWarehouse::with(['item', 'item.itemInfo'])
                ->where('clan_id', $clan->id)
                ->where('structure_id', $clanWarehouse->id)
                ->whereIn('id', array_keys($takeItems))
                ->get();

            $logData = [];

            foreach ($items as $wItem) {
                $wantCount = (int) ($takeItems[$wItem->id]['count'] ?? $wItem->count);
                $actualCount = min($wantCount, $wItem->count);

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
                        'count' => $actualCount,
                    ]);
                }

                if ($wItem->count <= $actualCount) {
                    $wItem->delete();
                } else {
                    $wItem->count -= $actualCount;
                    $wItem->save();
                }

                $logData[] = [
                    'clan_id' => $clan->id,
                    'user_id' => $user->id,
                    'structure_id' => $clanWarehouse->id,
                    'item_id' => $wItem->item_id,
                    'action' => ClanWarehouseAction::TAKE,
                    'count' => $actualCount,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($logData) {
                ClanWarehouseLog::insert($logData);
            }
        }

        $warehouseItems = ClanWarehouse::with(['item', 'item.itemInfo', 'depositor'])
            ->join('items', 'clan_warehouses.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('clan_id', $clan->id)
            ->where('structure_id', $clanWarehouse->id)
            ->orderBy('share_items.type', 'desc')
            ->select('clan_warehouses.*')
            ->get();

        $this->collector->collectFrom(new ClanWarehouseItemTooltipStrategy($warehouseItems));

        $itemTooltipScript = $this->collector->renderScript();

        return view('clan::warehouse.take', compact('clanWarehouse', 'clan', 'membership', 'warehouseItems', 'countInWarehouse', 'itemTooltipScript'));
    }

    public function logs(int $id): mixed
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $clanWarehouse = Structure::findOrFail($id);

        if (! $clanWarehouse->isClanWarehouse()) {
            abort(404);
        }

        $membership = $user->clanMembership;

        if ($membership === null) {
            session()->flash('message', 'Вы не состоите в клане.');

            return redirect()->route('clan');
        }

        $clan = $membership->clan;

        $logs = ClanWarehouseLog::with(['user', 'item.itemInfo'])
            ->where('clan_id', $clan->id)
            ->where('structure_id', $clanWarehouse->id)
            ->orderByDesc('created_at')
            ->paginate(50);

        $this->collector->collectFrom(new WarehouseLogItemTooltipStrategy($logs));

        $itemTooltipScript = $this->collector->renderScript();

        return view('clan::warehouse.logs', compact('clanWarehouse', 'clan', 'membership', 'logs', 'itemTooltipScript'));
    }
}