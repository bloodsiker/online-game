<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Blacksmith\Domain\Services\GemService;
use App\Enums\ShareItemType;
use App\Models\Backpack;
use App\Models\Structure;
use App\Services\ItemTooltip\ItemTooltipCollector;
use App\Services\ItemTooltip\Strategy\BackpackItemTooltipStrategy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GemController extends Controller
{
    public function __construct(
        private readonly GemService $gemService,
        private readonly ItemTooltipCollector $collector,
    ) {}

    public function index(Request $request, int $id): \Illuminate\Contracts\View\View
    {
        $user = Auth::user();
        $blacksmith = Structure::findOrFail($id);

        $socketableTypes = [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
            ShareItemType::ARMOR->value,
            ShareItemType::BELT->value,
        ];

        $items = Backpack::select('backpacks.*')
            ->with(['item', 'item.gems'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', $socketableTypes)
            ->get();

        $gems = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::GEM->value)
            ->get();

        $socketKits = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::SOCKET_KIT->value)
            ->get();

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($gems))
            ->collectFrom(new BackpackItemTooltipStrategy($socketKits))
            ->renderScript();

        return view('blacksmith::gems', compact(
            'blacksmith', 'user', 'items', 'gems', 'socketKits', 'itemTooltipScript'
        ));
    }

    public function insertGem(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id'      => 'required|integer',
            'gem_id'       => 'required|integer',
            'socket_index' => 'required|integer|min:0|max:2',
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

        $gemSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $request->integer('gem_id'))
            ->where('share_items.type', ShareItemType::GEM->value)
            ->first();

        if (! $gemSlot instanceof Backpack) {
            session()->flash('message', 'Камень не найден.');

            return redirect()->back();
        }

        $result = DB::transaction(fn () => $this->gemService->insertGem(
            $user,
            $itemSlot,
            $gemSlot,
            $request->integer('socket_index'),
        ));

        session()->flash('message', $result['message']);
        session()->flash('gem_success', $result['success']);

        return redirect()->route('blacksmith.gems', ['id' => $id]);
    }

    public function removeGem(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id'      => 'required|integer',
            'socket_index' => 'required|integer|min:0|max:2',
        ]);

        $itemSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $request->integer('item_id'))
            ->first();

        if (! $itemSlot instanceof Backpack) {
            session()->flash('message', 'Предмет не найден.');

            return redirect()->back();
        }

        $result = DB::transaction(fn () => $this->gemService->removeGem(
            $user,
            $itemSlot->item,
            $request->integer('socket_index'),
        ));

        session()->flash('message', $result['message']);
        session()->flash('gem_success', $result['success']);

        return redirect()->route('blacksmith.gems', ['id' => $id]);
    }

    public function openSocket(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();

        $request->validate([
            'item_id' => 'required|integer',
            'kit_id'  => 'required|integer',
        ]);

        $itemSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $request->integer('item_id'))
            ->first();

        if (! $itemSlot instanceof Backpack) {
            session()->flash('message', 'Предмет не найден.');

            return redirect()->back();
        }

        $kitSlot = Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.item_id', $request->integer('kit_id'))
            ->where('share_items.type', ShareItemType::SOCKET_KIT->value)
            ->first();

        if (! $kitSlot instanceof Backpack) {
            session()->flash('message', 'Набор для открытия сокета не найден.');

            return redirect()->back();
        }

        $result = DB::transaction(fn () => $this->gemService->openSocket(
            $user,
            $itemSlot->item,
            $kitSlot,
        ));

        session()->flash('message', $result['message']);
        session()->flash('gem_success', $result['success']);

        return redirect()->route('blacksmith.gems', ['id' => $id]);
    }
}