<?php

declare(strict_types=1);

namespace App\Modules\Clan\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Clan\Domain\Enums\ClanPermission;
use App\Modules\Clan\Domain\Enums\ClanWarehouseAction;
use App\Modules\Clan\Domain\Models\ClanWarehouse;
use App\Modules\Clan\Domain\Models\ClanWarehouseLog;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\Item\Application\ItemTooltip\Strategy\ClanWarehouseItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\Strategy\WarehouseLogItemTooltipStrategy;
use App\Modules\Share\Domain\Enums\ShareItemType;
use App\Modules\Structure\Infrastructure\Persistence\Models\Structure;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClanWarehouseController extends Controller
{
    public function __construct(
        protected readonly ItemTooltipCollector $collector,
    ) {}

    public function put(Request $request, int $id): mixed
    {
        /** @var User $user */
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

            $stackableItemIds = $items
                ->filter(static fn (Backpack $backpack): bool => in_array(
                    $backpack->item->itemInfo->type,
                    [ShareItemType::RESOURCE, ShareItemType::POTION],
                    true,
                ))
                ->pluck('item_id')
                ->map(static fn (mixed $itemId): int => (int) $itemId)
                ->all();
            $existingWarehouseItems = $stackableItemIds === []
                ? collect()
                : ClanWarehouse::query()
                    ->where('clan_id', $clan->id)
                    ->where('structure_id', $clanWarehouse->id)
                    ->whereIn('item_id', $stackableItemIds)
                    ->get()
                    ->keyBy('item_id');

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

                $isStackable = in_array(
                    $item->item->itemInfo->type,
                    [ShareItemType::RESOURCE, ShareItemType::POTION],
                    true,
                );
                $existing = $isStackable
                    ? $existingWarehouseItems->get((int) $item->item_id)
                    : null;

                if ($existing) {
                    $existing->count += $actualCount;
                    $existing->save();
                } else {
                    $createdWarehouseItem = ClanWarehouse::create([
                        'clan_id' => $clan->id,
                        'structure_id' => $clanWarehouse->id,
                        'depositor_user_id' => $user->id,
                        'item_id' => $item->item_id,
                        'count' => $actualCount,
                    ]);
                    if ($isStackable) {
                        $existingWarehouseItems->put((int) $item->item_id, $createdWarehouseItem);
                    }
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
        /** @var User $user */
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

            $stackableShareItemIds = $items
                ->filter(static fn (ClanWarehouse $warehouseItem): bool => in_array(
                    $warehouseItem->item->itemInfo->type,
                    [ShareItemType::RESOURCE, ShareItemType::POTION],
                    true,
                ))
                ->pluck('item.share_item_id')
                ->map(static fn (mixed $shareItemId): int => (int) $shareItemId)
                ->unique()
                ->values()
                ->all();
            $existingBackpackItems = $stackableShareItemIds === []
                ? collect()
                : Backpack::query()
                    ->select('backpacks.*')
                    ->addSelect('items.share_item_id as stack_share_item_id')
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->where('backpacks.user_id', $user->id)
                    ->whereIn('items.share_item_id', $stackableShareItemIds)
                    ->get()
                    ->keyBy('stack_share_item_id');

            $logData = [];

            foreach ($items as $wItem) {
                $wantCount = (int) ($takeItems[$wItem->id]['count'] ?? $wItem->count);
                $actualCount = min($wantCount, $wItem->count);

                $isStackable = in_array(
                    $wItem->item->itemInfo->type,
                    [ShareItemType::RESOURCE, ShareItemType::POTION],
                    true,
                );
                $shareItemId = (int) $wItem->item->share_item_id;
                $existing = $isStackable
                    ? $existingBackpackItems->get($shareItemId)
                    : null;

                if ($existing) {
                    $existing->count += $actualCount;
                    $existing->save();
                } else {
                    $createdBackpackItem = Backpack::create([
                        'user_id' => $user->id,
                        'item_id' => $wItem->item_id,
                        'count' => $actualCount,
                    ]);
                    if ($isStackable) {
                        $existingBackpackItems->put($shareItemId, $createdBackpackItem);
                    }
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
        /** @var User $user */
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
