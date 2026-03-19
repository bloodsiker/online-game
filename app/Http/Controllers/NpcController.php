<?php

namespace App\Http\Controllers;

use App\Enums\QuestPlayerStatus;
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
        $player = $user->player;

        $npc = Npc::find($id);

        $completedQuestIds = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->pluck('quest_id')
            ->toArray();

        $inProgressQuestIds = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->pluck('quest_id')
            ->toArray();

        // Repeatable quests that are completed and cooldown has passed (ready to take again)
        $repeatableReadyIds = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->whereHas('quest', fn ($q) => $q->where('type', 'repeatable'))
            ->where(fn ($q) => $q->whereNull('reset_at')->orWhere('reset_at', '<=', now()))
            ->pluck('quest_id')
            ->toArray();

        // Repeatable quests still on cooldown — show at NPC but block taking
        $questsOnCooldown = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::COMPLETED)
            ->whereHas('quest', fn ($q) => $q->where('type', 'repeatable')
                ->where('start_npc_id', $npc->id)
                ->isActive()
            )
            ->where('reset_at', '>', now())
            ->with('quest')
            ->get()
            ->map(fn ($qp) => (object) [
                'quest'    => $qp->quest,
                'reset_at' => $qp->reset_at,
                'diff'     => $qp->reset_at->locale('ru')->diffForHumans(now(), true, false, 2),
            ]);

        // Available quests at this NPC (started here)
        $excludeIds = array_diff(
            array_merge($completedQuestIds, $inProgressQuestIds),
            $repeatableReadyIds
        );

        $quests = Quest::whereNotIn('id', $excludeIds)
            ->isActive()
            ->where('start_npc_id', $npc->id)
            ->where(function ($query) use ($completedQuestIds) {
                $query->whereNull('after_quest_id')
                    ->orWhereIn('after_quest_id', $completedQuestIds);
            })
            ->get();

        // In-progress quests completable at this NPC (non-staged OR current stage belongs to this NPC)
        $inProgressQuestPlayers = QuestPlayer::where('player_id', $player->id)
            ->where('status', QuestPlayerStatus::IN_PROGRESS)
            ->whereIn('quest_id', $inProgressQuestIds)
            ->with('objectives.questObjective', 'quest', 'currentStage.objectives')
            ->get();

        $questsInProgress = $inProgressQuestPlayers
            ->filter(function (QuestPlayer $qp) use ($npc) {
                // Staged quest: check if current stage belongs to this NPC
                if ($qp->current_stage_id !== null && $qp->currentStage) {
                    return (int) $qp->currentStage->complete_npc_id === (int) $npc->id;
                }

                // Non-staged quest: use quest's complete_npc_id
                return (int) $qp->quest->complete_npc_id === (int) $npc->id;
            })
            ->map(function (QuestPlayer $qp) {
                $qp->quest->questPlayer  = $qp;
                $qp->quest->canComplete  = $qp->isCurrentStageComplete();
                $qp->quest->currentStage = $qp->currentStage;

                return $qp->quest;
            })
            ->values();

        $message     = session('quest_error') ?? session('quest_success');
        $messageType = session()->has('quest_success') ? 'success' : 'error';

        return view('npc.index', compact('npc', 'quests', 'questsInProgress', 'questsOnCooldown', 'message', 'messageType'));
    }

    public function info($id)
    {
        $monsterLocation = MonsterOnLocation::find($id);
        $monster         = $monsterLocation->monster;

        return view('monster.info', compact('monster'));
    }
}