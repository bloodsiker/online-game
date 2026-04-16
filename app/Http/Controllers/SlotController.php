<?php

namespace App\Http\Controllers;

use App\Modules\Backpack\Domain\Models\Backpack;
use App\Services\HotbarService;
use App\Modules\Player\Domain\Services\PlayerStatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotController extends Controller
{
    public function __construct(
        private PlayerStatService $statService,
        private HotbarService $hotbarService,
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;

        $group = $request->get('group', 'slots');

        [$passiveSkills, $activeSkills] = $player->magicSkills->partition(function ($skill) {
            return $skill->is_passive;
        });

        $hotbarData = $this->hotbarService->getSlotsData($player);

        $usableItems = Backpack::where('user_id', $user->id)
            ->with('item.itemInfo.effects')
            ->get()
            ->filter(fn($b) => $b->item?->itemInfo?->is_slot_usable)
            ->values();

        return view('slots.index', compact('user', 'player', 'group', 'passiveSkills', 'activeSkills', 'hotbarData', 'usableItems'));
    }

    public function updateSlot(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;

        $oldSheet = $this->statService->resolve($player);

        $equippedIds = $request->input('skills', []);
        $player->magicSkills()->update(['is_equipped' => false]);
        if (count($equippedIds)) {
            $player->magicSkills()
                ->whereIn('magic_skill_id', $equippedIds)
                ->update(['is_equipped' => true]);
        }

        $player->refresh();
        $newSheet = $this->statService->resolve($player);
        $this->statService->scaleHp($player, $oldSheet->getHpMax(), $newSheet->getHpMax(), $oldSheet->getMpMax(), $newSheet->getMpMax());

        $message = count($equippedIds) ? 'Сохранено' : 'Сохранено. Не выбрано ни одного скилла';
        return response()->json(['status' => 'success', 'message' => $message]);
    }
}
