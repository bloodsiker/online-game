<?php

declare(strict_types=1);

namespace App\Modules\Player\Presentation\Http;

use App\Http\Controllers\Controller;
use App\Modules\Backpack\Domain\Models\Backpack;
use App\Modules\Backpack\Domain\Services\ItemTooltip\BackpackItemTooltipStrategy;
use App\Modules\Item\Application\ItemTooltip\ItemTooltipCollector;
use App\Modules\MagicSkill\Application\UseCases\UpdateEquippedMagicSkills;
use App\Modules\Player\Application\Services\HotbarService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SlotsController extends Controller
{
    public function __construct(
        private readonly HotbarService $hotbarService,
        private readonly ItemTooltipCollector $itemTooltipCollector,
        private readonly UpdateEquippedMagicSkills $updateEquippedMagicSkills,
    ) {}

    public function index(Request $request): View
    {
        $user = Auth::user();
        $player = $user->player;
        $group = $request->get('group', 'slots');

        [$passiveSkills, $activeSkills] = $player->magicSkills->partition(
            fn ($skill) => $skill->is_passive,
        );

        $hotbarData = $this->hotbarService->getSlotsData($player);
        $usableItems = Backpack::where('user_id', $user->id)
            ->with('item.itemInfo.effects')
            ->get()
            ->filter(fn ($backpack) => $backpack->item?->itemInfo?->is_slot_usable)
            ->values();

        $itemTooltipScript = $this->itemTooltipCollector
            ->collectFrom(new BackpackItemTooltipStrategy($usableItems))
            ->renderScript();

        return view('slots.index', compact(
            'user',
            'player',
            'group',
            'passiveSkills',
            'activeSkills',
            'hotbarData',
            'usableItems',
            'itemTooltipScript',
        ));
    }

    public function update(Request $request): JsonResponse
    {
        $result = $this->updateEquippedMagicSkills->execute(
            Auth::user(),
            $request->input('skills', []),
        );

        return response()->json($result->toArray(), $result->httpCode);
    }
}
