<?php

namespace App\Http\Controllers;

use App\Decorator\Player\BuffDecorator;
use App\Decorator\Player\EquipmentDecorator;
use App\Events\PlayerChangeStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SlotController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $player = $user->player;

        $group = $request->get('group', 'slots');

        [$passiveSkills, $activeSkills] = $player->magicSkills->partition(function ($skill) {
            return $skill->is_passive;
        });

        return view('slots.index', compact('user', 'player', 'group', 'passiveSkills', 'activeSkills'));
    }

    public function updateSlot(Request $request)
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
