<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Blacksmith\Domain\Services\RuneService;
use App\Enums\ShareItemType;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Models\Structure;
use App\Services\ItemTooltip\ItemTooltipCollector;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RuneController extends Controller
{
    public function __construct(
        private readonly RuneService $runeService,
        private readonly ItemTooltipCollector $collector,
    ) {}

    public function index(Request $request, int $id): \Illuminate\Contracts\View\View
    {
        $user       = Auth::user();
        $blacksmith = Structure::findOrFail($id);

        $imbueableTypes = [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
        ];

        $items = Backpack::select('backpacks.*')
            ->with(['item', 'item.runes'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->whereIn('share_items.type', $imbueableTypes)
            ->get();

        $runes = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RUNE->value)
            ->get();

        $runeKeys = Backpack::select('backpacks.*')
            ->with(['item'])
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $user->id)
            ->where('backpacks.equipped', 0)
            ->where('share_items.type', ShareItemType::RUNE_KEY->value)
            ->get();

        $itemTooltipScript = $this->collector
            ->collectFrom(new BackpackItemTooltipStrategy($items))
            ->collectFrom(new BackpackItemTooltipStrategy($runes))
            ->collectFrom(new BackpackItemTooltipStrategy($runeKeys))
            ->renderScript();

        return view('blacksmith::runes', compact(
            'blacksmith', 'user', 'items', 'runes', 'runeKeys', 'itemTooltipScript'
        ));
    }

    public function imbue(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id'    => 'required|integer',
            'rune_id'    => 'required|integer',
            'slot_index' => 'required|integer|min:0|max:2',
            'risk_mode'  => 'nullable|boolean',
        ]);

        $itemSlot = $this->findItemSlot($user->id, $request->integer('item_id'), [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
        ]);

        if (! $itemSlot) {
            return $this->back('Предмет не найден.');
        }

        $runeSlot = $this->findItemSlot($user->id, $request->integer('rune_id'), [
            ShareItemType::RUNE->value,
        ]);

        if (! $runeSlot) {
            return $this->back('Руна не найдена.');
        }

        $result = DB::transaction(fn () => $this->runeService->imbue(
            $user,
            $itemSlot->item,
            $runeSlot,
            $request->integer('slot_index'),
            (bool) $request->input('risk_mode', false),
        ));

        session()->flash('message', $result['message']);
        session()->flash('rune_success', $result['success']);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }

    public function removeRune(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id'    => 'required|integer',
            'slot_index' => 'required|integer|min:0|max:2',
        ]);

        $itemSlot = $this->findItemSlot($user->id, $request->integer('item_id'), [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
        ]);

        if (! $itemSlot) {
            return $this->back('Предмет не найден.');
        }

        $result = DB::transaction(fn () => $this->runeService->removeRune(
            $itemSlot->item,
            $request->integer('slot_index'),
        ));

        session()->flash('message', $result['message']);
        session()->flash('rune_success', $result['success']);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }

    public function reroll(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id'          => 'required|integer',
            'slot_index'       => 'required|integer|min:0|max:2',
            'locked_indices'   => 'nullable|array',
            'locked_indices.*' => 'integer|min:0|max:4',
        ]);

        $itemSlot = $this->findItemSlot($user->id, $request->integer('item_id'), [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
        ]);

        if (! $itemSlot) {
            return $this->back('Предмет не найден.');
        }

        $lockedIndices = array_map('intval', $request->input('locked_indices', []));

        $result = DB::transaction(fn () => $this->runeService->reroll(
            $user,
            $itemSlot->item,
            $request->integer('slot_index'),
            $lockedIndices,
        ));

        session()->flash('message', $result['message']);
        session()->flash('rune_success', $result['success']);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }

    public function openSlot(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id' => 'required|integer',
            'key_id'  => 'required|integer',
        ]);

        $itemSlot = $this->findItemSlot($user->id, $request->integer('item_id'), [
            ShareItemType::WEAPON->value,
            ShareItemType::SHIELD->value,
        ]);

        if (! $itemSlot) {
            return $this->back('Предмет не найден.');
        }

        $keySlot = $this->findItemSlot($user->id, $request->integer('key_id'), [
            ShareItemType::RUNE_KEY->value,
        ]);

        if (! $keySlot) {
            return $this->back('Рунный ключ не найден.');
        }

        $result = DB::transaction(fn () => $this->runeService->openSlot(
            $itemSlot->item,
            $keySlot,
        ));

        session()->flash('message', $result['message']);
        session()->flash('rune_success', $result['success']);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }

    private function findItemSlot(int $userId, int $itemId, array $types): ?Backpack
    {
        return Backpack::select('backpacks.*')
            ->join('items', 'backpacks.item_id', '=', 'items.id')
            ->join('share_items', 'items.share_item_id', '=', 'share_items.id')
            ->where('backpacks.user_id', $userId)
            ->where('backpacks.item_id', $itemId)
            ->whereIn('share_items.type', $types)
            ->first();
    }

    private function back(string $message): RedirectResponse
    {
        session()->flash('message', $message);

        return redirect()->back();
    }
}