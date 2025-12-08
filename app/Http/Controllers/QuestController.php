<?php

namespace App\Http\Controllers;

use App\Models\Npc;
use App\Models\Quest\Quest;
use App\Models\Quest\QuestPlayer;
use App\Models\Quest\QuestPlayerObjective;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuestController extends Controller
{
    public function list(Request $request)
    {
        $player = Auth::user()->player;
        $quests = QuestPlayer::where(['player_id' => $player->id])->orderByDesc('id')->get();
        $questIds = $quests->pluck('id')->toArray();
        $questIds = implode(',', $questIds);

        return view('quest.list', compact('quests', 'questIds'));
    }

    public function quest($id, Request $request)
    {
        $quest = Quest::find($id);
        $npc = Npc::find($request->integer('npc'));

        return view('quest.quest', compact('quest', 'npc'));
    }

    public function take($id, Request $request)
    {
        $user = Auth::user();
        $quest = Quest::find($id);

        $questPlayer = QuestPlayer::where(['player_id' => $user->player->id, 'quest_id' => $quest->id])->first();

        if (!$questPlayer instanceof QuestPlayer) {
            $questPlayer = new QuestPlayer();
            $questPlayer->player_id = $user->player->id;
            $questPlayer->quest_id = $quest->id;
            $questPlayer->save();

            $questPlayerObjective = new QuestPlayerObjective();
            $questPlayerObjective->quest_player_id = $questPlayer->id;
            $questPlayerObjective->quest_objective_id = $questPlayer->quest->objective->id;
            $questPlayerObjective->save();
        }

        return redirect()->route('npc', ['id' => $request->integer('npc')]);
    }
}
