<?php

declare(strict_types=1);

namespace App\Modules\Structure\Blacksmith\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Structure\Blacksmith\Application\DTOs\RuneActionDTO;
use App\Modules\Structure\Blacksmith\Application\UseCases\GetRunesPage;
use App\Modules\Structure\Blacksmith\Application\UseCases\ImbueRune;
use App\Modules\Structure\Blacksmith\Application\UseCases\OpenRuneSlot;
use App\Modules\Structure\Blacksmith\Application\UseCases\RemoveRune;
use App\Modules\Structure\Blacksmith\Application\UseCases\RerollRune;
use App\Modules\User\Infrastructure\Persistence\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RuneController extends Controller
{
    public function __construct(
        private readonly GetRunesPage $getRunesPage,
        private readonly ImbueRune $imbueRune,
        private readonly RemoveRune $removeRune,
        private readonly RerollRune $rerollRune,
        private readonly OpenRuneSlot $openRuneSlot,
    ) {}

    public function index(Request $request, int $id): \Illuminate\Contracts\View\View
    {
        /** @var User $user */
        $user = Auth::user();
        $page = $this->getRunesPage->execute($user, $id);

        return view('blacksmith::runes', [
            'blacksmith' => $page->blacksmith,
            'user' => $user,
            'items' => $page->items,
            'runes' => $page->runes,
            'runeKeys' => $page->runeKeys,
            'itemTooltipScript' => $page->itemTooltipScript,
        ]);
    }

    public function imbue(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id' => 'required|integer',
            'rune_id' => 'required|integer',
            'slot_index' => 'required|integer|min:0|max:2',
            'risk_mode' => 'nullable|boolean',
        ]);

        $result = $this->imbueRune->execute(new RuneActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            runeId: $request->integer('rune_id'),
            slotIndex: $request->integer('slot_index'),
            riskMode: (bool) $request->input('risk_mode', false),
        ));

        session()->flash('message', $result->message);
        session()->flash('rune_success', $result->success);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }

    public function removeRune(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id' => 'required|integer',
            'slot_index' => 'required|integer|min:0|max:2',
        ]);

        $result = $this->removeRune->execute(new RuneActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            slotIndex: $request->integer('slot_index'),
        ));

        session()->flash('message', $result->message);
        session()->flash('rune_success', $result->success);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }

    public function reroll(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id' => 'required|integer',
            'slot_index' => 'required|integer|min:0|max:2',
            'locked_indices' => 'nullable|array',
            'locked_indices.*' => 'integer|min:0|max:4',
        ]);

        $result = $this->rerollRune->execute(new RuneActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            slotIndex: $request->integer('slot_index'),
            lockedIndices: array_map('intval', $request->input('locked_indices', [])),
        ));

        session()->flash('message', $result->message);
        session()->flash('rune_success', $result->success);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }

    public function openSlot(Request $request, int $id): RedirectResponse
    {
        $user = Auth::user();
        $request->validate([
            'item_id' => 'required|integer',
            'key_id' => 'required|integer',
        ]);

        $result = $this->openRuneSlot->execute(new RuneActionDTO(
            user: $user,
            itemId: $request->integer('item_id'),
            keyId: $request->integer('key_id'),
        ));

        session()->flash('message', $result->message);
        session()->flash('rune_success', $result->success);

        return redirect()->route('blacksmith.runes', ['id' => $id]);
    }
}
