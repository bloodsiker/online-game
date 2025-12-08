<?php

namespace App\Http\Controllers;

use App\Decorator\Player\BuffDecorator;
use App\Decorator\Player\EquipmentDecorator;
use App\Events\PlayerChangeStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MagicSkillController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;
//        $playerDecorator = new EquipmentDecorator($player);
//        $playerDecorator = new BuffDecorator($playerDecorator);

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

        $equippedIds = $request->input('skills', []);
        $player->magicSkills()->update(['is_equipped' => false]);
        if (count($equippedIds)) {
            $player->magicSkills()
                ->whereIn('magic_skill_id', $equippedIds)
                ->update(['is_equipped' => true]);

            return response()->json(['status' => 'success', 'message' => 'Сохранено']);
        }

        return response()->json(['status' => 'success', 'message' => 'Сохранено. Не выбрано ни одного скилла']);
    }
}
