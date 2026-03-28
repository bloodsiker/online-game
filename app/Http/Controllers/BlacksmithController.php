<?php

namespace App\Http\Controllers;

use App\Enums\ShareItemType;
use App\Models\Backpack;
use App\Models\Item\Item;
use App\Models\Share\ShareItem;
use App\Models\Structure;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\BackpackItemTooltipStrategy;
use App\Services\ItemTooltip\Strategy\ShareItemTooltipStrategy;
use App\Services\PlayerStatService;
use App\Services\UpgradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BlacksmithController extends Controller
{
    public function __construct(
        private readonly ItemTooltipCollector $collector,
        private readonly UpgradeService $upgradeService,
        private readonly PlayerStatService $statService,
    ) {}

    public function index(Request $request, $id)
    {
        $user = Auth::user();
        $blacksmith = Structure::find($id);

        if (! $blacksmith) {
            abort(404);
        }

        $recipes = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('share_items.type', ShareItemType::RECIPE->value)
            ->get();

        $resources = DB::table('share_items')
            ->select(['share_items.id', 'backpacks.count'])
            ->join('items', 'items.share_item_id', '=', 'share_items.id')
            ->join('backpacks', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RESOURCE)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'count' => $item->count,
                ];
            })->toArray();

        $ingredientItems = $recipes->flatMap(fn ($r) => $r->item->itemInfo->recipe?->items ?? collect());

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($recipes))
            ->collectFrom(new ShareItemTooltipStrategy($ingredientItems))
            ->renderScript();

        return view('blacksmith.kraft', compact('blacksmith', 'user', 'recipes', 'resources', 'itemTooltipScript'));
    }

    public function kraftItem(Request $request, $id)
    {
        $user = Auth::user();

        $recipeItem = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $id)
            ->first();

        if (! $recipeItem instanceof Backpack) {
            abort(404);
        }

        $resources = DB::table('share_items')
            ->select(['share_items.id', 'backpacks.count'])
            ->join('items', 'items.share_item_id', '=', 'share_items.id')
            ->join('backpacks', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RESOURCE)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'count' => $item->count,
                ];
            })->toArray();

        $recipe = $recipeItem->item->itemInfo->recipe;
        $itemsNeedKraft = $recipe->items;

        $allowKraft = true;
        foreach ($itemsNeedKraft as $itemsKraft) {
            foreach ($resources as $resource) {
                if ($itemsKraft->id === $resource['id']) {
                    if ($resource['count'] < $itemsKraft->pivot->count) {
                        $allowKraft = false;
                    }
                }
            }
        }

        if ($allowKraft) {
            $percentKraft = mt_rand(0, 100);
            if ($percentKraft <= $recipe->percent) {

                $successKraftItem = new Item;
                $successKraftItem->share_item_id = $recipe->kraftItem->id;
                $successKraftItem->save();

                $user->backpack()->attach($successKraftItem->id, ['equipped' => 0, 'count' => 1]);

                session()->flash('message', sprintf('Успешний крафт. Получено %s', $recipe->kraftItem->name));
            } else {
                session()->flash('message', 'Не удачный крафт');
            }

            $recipeItem->item->delete();
            $recipeItem->delete();

            foreach ($itemsNeedKraft as $itemDelete) {
                $itemBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $itemDelete->id])
                    ->join('items', 'backpacks.item_id', '=', 'items.id')
                    ->first();

                if ($itemBackpack->count > $itemDelete->pivot->count) {
                    $itemBackpack->count -= $itemDelete->pivot->count;
                    $itemBackpack->save();
                } else {
                    $itemBackpack->delete();
                    Item::where('id', $itemBackpack->item_id)->delete();
                }
            }

        } else {
            session()->flash('message', 'Не достаточно ресурсов для крафта');
        }

        return redirect()->back();
    }

    public function breakItem($id, Request $request)
    {
        $user = Auth::user();
        $blacksmith = Structure::find($id);

        if (! $blacksmith) {
            abort(404);
        }

        $crystal = ShareItem::find(23);

        if ($request->has('iid')) {
            $itemId = $request->get('iid');
            $item = Backpack::with(['item', 'item.itemInfo'])->where(['item_id' => $itemId])->first();
            if (! $item instanceof Backpack) {
                session()->flash('message', 'Не найден предмет для кристализации');

                return redirect()->back();
            }

            $hasBackpack = Backpack::select('backpacks.*')->where(['items.share_item_id' => $crystal->id])
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->first();

            $countCrystal = $item->item->itemInfo->break_crystal;

            if ($hasBackpack instanceof Backpack && $crystal->type === ShareItemType::RESOURCE) {
                $hasBackpack->count += $countCrystal;
                $hasBackpack->save();

                $item->delete();
            } else {
                $newItem = new Item;
                $newItem->share_item_id = $crystal->id;
                $newItem->save();

                $backpack = new Backpack;
                $backpack->user_id = $user->id;
                $backpack->item_id = $newItem->id;
                $backpack->count = $countCrystal;
                $backpack->save();
            }

            $item->delete();
            $item->item->delete();

            session()->flash('message', sprintf('Вы получили кристаллов в количестве %s шт', $countCrystal));

            return redirect()->back();
        }

        $items = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', [ShareItemType::WEAPON->value, ShareItemType::SHIELD->value, ShareItemType::ARMOR->value])
            ->get();

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->renderScript();

        return view('blacksmith.break', compact('blacksmith', 'user', 'items', 'crystal', 'itemTooltipScript'));
    }

    public function upgrade(Request $request, int $id): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();
        $blacksmith = Structure::findOrFail($id);

        $upgradeableTypes = [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
            ShareItemType::ARMOR->value,
            ShareItemType::BELT->value,
        ];

        $items = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', $upgradeableTypes)
            ->get();

        $baseScrolls = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::SCROLL->value)
            ->where('share_items.upgrade_scroll_type', \App\Enums\UpgradeScrollType::BASE->value)
            ->get();

        $bonusScrolls = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::SCROLL->value)
            ->whereIn('share_items.upgrade_scroll_type', [
                \App\Enums\UpgradeScrollType::PROTECTION->value,
                \App\Enums\UpgradeScrollType::STABILIZER->value,
                \App\Enums\UpgradeScrollType::LUCKY->value,
            ])
            ->get();

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($baseScrolls))
            ->collectFrom(new BackpackItemTooltipStrategy($bonusScrolls))
            ->renderScript();

        $player = $user->player;
        $playerDecorator = $this->statService->resolve($player);

        return view('blacksmith.upgrade', compact('blacksmith', 'user', 'player', 'playerDecorator', 'items', 'baseScrolls', 'bonusScrolls', 'itemTooltipScript'));
    }

    public function upgradeProcess(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id' => 'required|integer',
            'base_scroll_id' => 'required|integer',
            'bonus_scroll_id' => 'nullable|integer',
        ]);

        $itemSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $request->integer('item_id'))
            ->whereIn('share_items.type', [
                ShareItemType::WEAPON->value,
                ShareItemType::SHIELD->value,
                ShareItemType::ARMOR->value,
                ShareItemType::BELT->value,
            ])
            ->first();

        if (! $itemSlot instanceof Backpack) {
            session()->flash('message', 'Предмет не найден.');

            return redirect()->back();
        }

        $baseScrollSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $request->integer('base_scroll_id'))
            ->where('share_items.upgrade_scroll_type', \App\Enums\UpgradeScrollType::BASE->value)
            ->first();

        if (! $baseScrollSlot instanceof Backpack) {
            session()->flash('message', 'Свиток заточки не найден. Для заточки необходим свиток заточки.');

            return redirect()->back();
        }

        $bonusScrollSlot = null;
        if ($request->filled('bonus_scroll_id')) {
            $bonusScrollSlot = Backpack::select('backpacks.*')
                ->join('items', 'backpacks.item_id', '=', 'items.id')
                ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
                ->where('backpacks.user_id', $user->id)
                ->where('backpacks.item_id', $request->integer('bonus_scroll_id'))
                ->where('share_items.type', ShareItemType::SCROLL->value)
                ->whereIn('share_items.upgrade_scroll_type', [
                    \App\Enums\UpgradeScrollType::PROTECTION->value,
                    \App\Enums\UpgradeScrollType::STABILIZER->value,
                    \App\Enums\UpgradeScrollType::LUCKY->value,
                ])
                ->first();
        }

        DB::transaction(function () use ($user, $itemSlot, $baseScrollSlot, $bonusScrollSlot, &$result) {
            $result = $this->upgradeService->upgrade($user, $itemSlot, $baseScrollSlot, $bonusScrollSlot);
        });

        session()->flash('message', $result['message']);
        session()->flash('upgrade_success', $result['success']);
        session()->flash('upgrade_destroyed', $result['destroyed'] ?? false);

        return redirect()->route('blacksmith.upgrade', ['id' => $id]);
    }
}
