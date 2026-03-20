<?php

namespace App\Http\Controllers;

use App\Events\PlayerChangeStat;
use App\Services\PlayerStatService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicSkillController extends Controller
{
    public function __construct(private PlayerStatService $statService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;

        $group = $request->get('group', 'magic_skill');
//        dd($player->magicSkills);

        [$passiveSkills, $activeSkills] = $player->magicSkills->partition(function ($skill) {
            return $skill->is_passive;
        });

        return view('magic_skill.index', compact('user', 'player', 'group', 'passiveSkills', 'activeSkills'));
    }

    public function updateSkill(Request $request)
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
