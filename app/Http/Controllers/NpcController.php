<?php

namespace App\Http\Controllers;

use App\Models\Monster\MonsterOnLocation;
use App\Models\Npc;
use App\Models\Quest\Quest;
use App\Models\Quest\QuestPlayer;
use Illuminate\Support\Facades\Auth;

class NpcController extends Controller
{
    public function index($id)
    {
        $user = Auth::user();

        $npc = Npc::find($id);
        $completedQuestIds = QuestPlayer::where(['player_id' => $user->player->id, 'status' => 'completed',])
            ->pluck('quest_id')
            ->toArray();

        $inProgressQuestIds = QuestPlayer::where(['player_id' => $user->player->id, 'status' => 'in_progress',])
            ->pluck('quest_id')
            ->toArray();

        $quests = Quest::whereNotIn('id', array_merge($completedQuestIds, $inProgressQuestIds))
            ->isActive()
            ->where('start_npc_id', $npc->id)
            ->where('complete_npc_id', $npc->id)
            ->where(function ($query) use ($completedQuestIds) {
                $query->whereNull('after_quest_id')
                    ->orWhereIn('after_quest_id', $completedQuestIds);
            })
            ->get();

        $questsInProgress = Quest::whereIn('id', $inProgressQuestIds)
            ->isActive()
            ->where('complete_npc_id', $npc->id)
            ->get();

        return view('npc.index', compact('npc', 'quests', 'questsInProgress'));
    }

    public function info($id)
    {
        $monsterLocation = MonsterOnLocation::find($id);
        $monster = $monsterLocation->monster;

        return view('monster.info', compact('monster'));
    }
}
